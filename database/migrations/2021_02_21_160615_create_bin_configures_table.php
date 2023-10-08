<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBinConfiguresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bin_configures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bin_id');
            $table->unsignedBigInteger('order');
            $table->string('batch_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->unsignedTinyInteger('has_charge_time')->nullable();
            $table->string('charge_time')->nullable();
            $table->unsignedTinyInteger('has_calibration_due')->nullable();
            $table->string('calibration_due')->nullable();
            $table->unsignedTinyInteger('has_expiry_date')->nullable();
            $table->string('expiry_date')->nullable();
            $table->unsignedTinyInteger('has_load_hydrostatic_test_due')->nullable();
            $table->string('load_hydrostatic_test_due')->nullable();
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
        Schema::dropIfExists('bin_configures');
    }
}
