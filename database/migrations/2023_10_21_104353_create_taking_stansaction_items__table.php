<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTakingStansactionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taking_transaction_details', function (Blueprint $table) {
            $table->unsignedBigInteger('taking_transaction_id');
            $table->unsignedBigInteger('spare_id');
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
        Schema::dropIfExists('taking_transaction_details');
    }
}
