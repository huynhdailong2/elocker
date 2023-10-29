<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TakingTransactionSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('taking_transaction_details', function (Blueprint $table) {
            $table->unsignedBigInteger('taking_transaction_id');
            $table->unsignedBigInteger('spare_id');
            $table->integer('job_card_id')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->text('job_name')->nullable();
            $table->text('vehicle_num')->nullable();
            $table->integer('area_id')->nullable();
            $table->text('area_name')->nullable();
            $table->text('platform')->nullable();
            $table->text('listWO');
            $table->timestamps();

            $table->foreign('taking_transaction_id')->references('id')->on('taking_transactions')->onDelete('cascade');
            $table->foreign('spare_id')->references('id')->on('spares')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('taking_transaction_details');
    }
}
