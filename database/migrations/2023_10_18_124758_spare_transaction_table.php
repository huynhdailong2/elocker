<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SpareTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('takting_transaction_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('taking_transaction_id');
            $table->unsignedBigInteger('spare_id');
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
        Schema::dropIfExists('spare_taking_transaction');
    }
}
