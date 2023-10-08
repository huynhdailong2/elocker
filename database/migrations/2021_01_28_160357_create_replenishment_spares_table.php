<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplenishmentSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replenishment_spares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('replenishment_id');
            $table->unsignedBigInteger('bin_id');
            $table->unsignedBigInteger('spare_id');
            $table->unsignedBigInteger('quantity');
            $table->timestamps();

            $table->index('replenishment_id');
            $table->index(['replenishment_id', 'spare_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replenishment_spares');
    }
}
