<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Store;
use App\Models\DeliveryMan;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Backfill delivery_man_id for existing orders based on business logic
        
        // Get orders that need delivery man assignment
        $orders = Order::with('store')
            ->whereNull('delivery_man_id')
            ->where('order_type', '!=', 'take_away') // Skip pickup orders
            ->whereNotIn('order_status', ['canceled', 'failed'])
            ->get();

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($orders as $order) {
            $store = $order->store;
            
            if (!$store) {
                $skippedCount++;
                continue;
            }

            $deliveryManId = null;

            // If store has self-delivery system
            if ($store->self_delivery_system == 1) {
                // For self-delivery stores, we can simulate that they handled it themselves
                // by leaving delivery_man_id as null (which our logic correctly interprets)
                $skippedCount++;
                continue;
            }

            // For regular stores with delivery enabled, try to assign a Tamam delivery man
            if ($store->delivery == 1) {
                $deliveryMan = DeliveryMan::whereNull('store_id') // Tamam delivery men
                    ->where('status', 1) // Active status
                    ->orderBy('assigned_order_count', 'asc') // Least busy first
                    ->first();

                if ($deliveryMan) {
                    $deliveryManId = $deliveryMan->id;
                }
            }

            if ($deliveryManId) {
                $order->update(['delivery_man_id' => $deliveryManId]);
                
                // Increment assigned order count
                DeliveryMan::where('id', $deliveryManId)->increment('assigned_order_count');
                
                $assignedCount++;
            } else {
                $skippedCount++;
            }
        }

        // Log the results
        if ($assignedCount > 0 || $skippedCount > 0) {
            \Log::info("Delivery Assignment Backfill Complete", [
                'assigned' => $assignedCount,
                'skipped' => $skippedCount,
                'total_processed' => $assignedCount + $skippedCount
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration cannot be reversed as it assigns delivery men
        // based on business logic that may have changed
        \Log::warning('Delivery assignment backfill migration cannot be reversed');
    }
};