<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBinSpareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('bin_spare', function (Blueprint $table) {
            $table->bigInteger('is_processing')->default('0');
            $table->timestamp('process_time')->nullable();
            $table->integer('process_by')->nullable();
            $table->bigIncrements('id');
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
        Schema::table('bin_spare', function (Blueprint $table) {
            $table->dropColumn('is_processing');
            $table->dropColumn('process_time');
            $table->dropColumn('process_by');
            $table->dropColumn('id');
        });
    }
}
