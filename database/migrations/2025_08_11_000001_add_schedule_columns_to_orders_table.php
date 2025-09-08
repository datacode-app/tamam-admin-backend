<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'schedule_at')) {
                    $table->dateTime('schedule_at')->nullable()->after('order_type');
                }
                if (!Schema::hasColumn('orders', 'scheduled')) {
                    $table->boolean('scheduled')->default(false)->after('schedule_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'scheduled')) {
                    $table->dropColumn('scheduled');
                }
                if (Schema::hasColumn('orders', 'schedule_at')) {
                    $table->dropColumn('schedule_at');
                }
            });
        }
    }
};


