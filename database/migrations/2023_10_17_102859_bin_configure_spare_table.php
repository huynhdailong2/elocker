<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BinConfigureSpareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bin_configure_spare', function (Blueprint $table) {
            $table->unsignedBigInteger('bin_configure_id');
            $table->unsignedBigInteger('spare_id');
            $table->timestamps();

            $table->foreign('bin_configure_id')->references('id')->on('bin_configures')->onDelete('cascade');
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
        Schema::dropIfExists('bin_configure_spare');
    }
}
