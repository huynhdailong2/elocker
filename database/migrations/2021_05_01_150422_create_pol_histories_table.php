<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pol_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pol_id');
            $table->string('type')->comment('issue or replenish');
            $table->bigInteger('quantity')->nullable();
            $table->unsignedBigInteger('issuer_id')->nullable()->comment('issue by');
            $table->unsignedBigInteger('receiver_id')->nullable()->comment('issue to');
            $table->unsignedBigInteger('receiver_requested_id')->nullable()->comment('receiver requested');
            $table->unsignedBigInteger('requester_id')->nullable()->comment('requester');
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
        Schema::dropIfExists('pol_histories');
    }
}
