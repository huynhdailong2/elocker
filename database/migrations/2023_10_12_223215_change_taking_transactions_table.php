<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTakingTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taking_transactions', function (Blueprint $table) {
            $table->text('hardware_port');
            $table->string('name');
            $table->integer('part_number');
            $table->integer('port_id');
            $table->integer('qty');
            $table->integer('pre_qty');
            $table->integer('changed_qty');
            $table->integer('item_id');
            $table->integer('cabinet_id');
            $table->integer('bin_id');
            $table->integer('spare_id');
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taking_transactions', function (Blueprint $table) {
            $table->dropColumn('hardware_port');
        });
    }
}
