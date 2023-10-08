<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTorqueWrenchAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('torque_wrench_areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('area');
            $table->decimal('torque_value', 30, 10);
            $table->timestamps();
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
        Schema::dropIfExists('torque_wrench_areas');
    }
}
