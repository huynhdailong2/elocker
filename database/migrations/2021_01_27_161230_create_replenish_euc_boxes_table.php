<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplenishEucBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replenish_euc_boxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('euc_box_id');
            $table->unsignedBigInteger('spare_id');
            $table->unsignedTinyInteger('is_confirmed');
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('receiver_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('euc_box_id');
            $table->index(['euc_box_id', 'spare_id'], 'euc_box_id_spare_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replenish_euc_boxes');
    }
}
