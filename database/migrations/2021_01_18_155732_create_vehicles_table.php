<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('vehicle_num');
            $table->unsignedBigInteger('vehicle_type_id');
            $table->string('variant')->nullable();
            $table->string('unit')->nullable();
            $table->string('unit_other')->nullable();
            $table->string('mileage_start')->nullable();
            $table->string('mileage_end')->nullable();
            $table->unsignedTinyInteger('t_loan')->default(0);
            $table->unsignedTinyInteger('unserviceable')->default(0);
            $table->string('last_point_servicing')->nullable();
            $table->string('schedule_6_months')->nullable();
            $table->string('completion_date_6_months')->nullable();
            $table->string('schedule_12_months')->nullable();
            $table->string('completion_date_12_months')->nullable();
            $table->string('schedule_18_months')->nullable();
            $table->string('completion_date_18_months')->nullable();
            $table->string('schedule_24_months')->nullable();
            $table->string('completion_date_24_months')->nullable();
            $table->string('status');
            $table->unsignedTinyInteger('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
