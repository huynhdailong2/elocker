<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClusterIdShelfIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('taking_transaction_details', function (Blueprint $table) {
            $table->integer('cabinet_id')->nullable();
            $table->integer('bin_id')->nullable();
            $table->text('location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('taking_transaction_details', function (Blueprint $table) {
            //
            $table->dropColumn('cabinet_id');
            $table->dropColumn('bin_id');
            $table->dropColumn('location');
        });
    }
}
