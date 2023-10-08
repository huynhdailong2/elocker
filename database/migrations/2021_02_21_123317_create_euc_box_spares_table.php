<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEucBoxSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('euc_box_spares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('euc_box_id');
            $table->unsignedBigInteger('spare_id');
            $table->unsignedBigInteger('quantity_oh');
            $table->string('batch_no')->nullable();
            $table->string('calibration_due')->nullable();
            $table->string('charge_time')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('load_hydrostatic_test_due')->nullable();
            $table->string('serial_no')->nullable();
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
        Schema::dropIfExists('euc_box_spares');
    }
}
