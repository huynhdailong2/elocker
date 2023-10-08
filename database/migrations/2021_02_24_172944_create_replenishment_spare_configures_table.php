<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplenishmentSpareConfiguresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replenishment_spare_configures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('replenishment_spare_id');
            $table->unsignedBigInteger('order');
            $table->string('batch_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->unsignedTinyInteger('has_charge_time')->nullable();
            $table->string('charge_time')->nullable();
            $table->unsignedTinyInteger('has_calibration_due')->nullable();
            $table->string('calibration_due')->nullable();
            $table->unsignedTinyInteger('has_expiry_date')->nullable();
            $table->string('expiry_date')->nullable();
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
        Schema::dropIfExists('replenishment_spare_configures');
    }
}
