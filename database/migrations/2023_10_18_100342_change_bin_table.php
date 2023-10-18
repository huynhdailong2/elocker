<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bins', function (Blueprint $table) {
            $table->integer('cu_id');
            $table->integer('lock_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bins', function (Blueprint $table) {
            $table->dropColumn('cu_id');
            $table->dropColumn('lock_id');
        });
    }
}
