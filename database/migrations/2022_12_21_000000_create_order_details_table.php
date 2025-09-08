<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('item_id');
            $table->foreignId('item_campaign_id')->nullable();
            $table->decimal('price', 24, 2);
            $table->text('item_details');
            $table->string('variation')->nullable();
            $table->integer('quantity');
            $table->decimal('tax_amount', 24, 2);
            $table->decimal('discount_on_item', 24, 2)->nullable();
            $table->string('discount_type')->default('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
