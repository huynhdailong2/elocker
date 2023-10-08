<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssueCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issue_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('job_card_id');
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->unsignedBigInteger('euc_box_id')->nullable();
            $table->unsignedBigInteger('spare_id');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('torque_wrench_area_id')->nullable();
            $table->unsignedBigInteger('issuer_id');
            $table->unsignedBigInteger('taker_id');
            $table->string('returned')->nullable();
            $table->unsignedBigInteger('returned_quantity')->nullable();
            $table->timestamps();

            $table->index('spare_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issue_cards');
    }
}
