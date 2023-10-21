<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpareIdToBinConfiguresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bin_configures', function (Blueprint $table) {
            $table->bigInteger('spare_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bin_configures', function (Blueprint $table) {
            $table->dropColumn('spare_id');
        });
    }
}
