<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_spares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->unsignedBigInteger('bin_id');
            $table->unsignedBigInteger('spare_id');
            $table->string('state');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('handover_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->unsignedBigInteger('quantity_returned_store')->nullable()->comment('only for handover');
            $table->text('noted')->nullable();
            $table->unsignedTinyInteger('write_off')->nullable();
            $table->timestamps();

            $table->index(['type', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_spares');
    }
}
