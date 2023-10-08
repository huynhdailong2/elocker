<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shelf_id');
            $table->unsignedBigInteger('row');
            $table->unsignedBigInteger('bin');
            $table->string('status');
            $table->unsignedBigInteger('spare_id')->nullable();
            $table->unsignedBigInteger('quantity')->nullable();
            $table->unsignedBigInteger('quantity_oh')->nullable();
            $table->unsignedBigInteger('min')->nullable();
            $table->unsignedBigInteger('max')->nullable();
            $table->unsignedBigInteger('critical')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('bins');
    }
}
