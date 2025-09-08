<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('logo')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('address')->nullable();
            $table->text('footer_text')->nullable();
            $table->decimal('minimum_order', 24, 2)->default(0);
            $table->decimal('delivery_charge', 24, 2)->default(0);
            $table->decimal('comission', 24, 2)->nullable();
            $table->boolean('schedule_order')->default(0);
            $table->string('opening_time')->default('00:00');
            $table->string('closeing_time')->default('23:59');
            $table->boolean('status')->default(1);
            $table->foreignId('vendor_id');
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
        Schema::dropIfExists('stores');
    }
}
