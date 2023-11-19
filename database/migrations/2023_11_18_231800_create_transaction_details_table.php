<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id');
            $table->integer('shelf_id')->nullable();
            $table->text('row');
            $table->integer('spare_id');
            $table->integer('bin_id');
            $table->integer('job_card_id')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('conditions');
            $table->softDeletes();
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
        Schema::dropIfExists('transaction_details');
    }
}
