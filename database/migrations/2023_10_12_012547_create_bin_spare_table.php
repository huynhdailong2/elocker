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
            $table->bigInteger('quantity')->default('0');
            $table->bigInteger('quantity_oh')->default('0');
            $table->integer('critical')->default('0');
            $table->integer('min')->default('0');
            $table->bigInteger('max')->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

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
