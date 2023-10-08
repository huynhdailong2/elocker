<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWriteOffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('write_offs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('return_spare_id');
            $table->unsignedBigInteger('bin_id');
            $table->unsignedBigInteger('spare_id');
            $table->unsignedBigInteger('user_id')->comment('who writes off');
            $table->unsignedBigInteger('quantity')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('eliminator_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('write_offs');
    }
}
