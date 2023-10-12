<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBinSpareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bin_spare', function (Blueprint $table) {
            $table->unsignedBigInteger('bin_id');
            $table->unsignedBigInteger('spare_id');
            // Các cột thông tin bổ sung khác
            $table->timestamps();

            $table->foreign('bin_id')->references('id')->on('bins')->onDelete('cascade');
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
        Schema::dropIfExists('bin_spare');
    }
}
