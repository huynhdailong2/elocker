<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->integer('previous_qty')->nullable();
            $table->integer('current_qty')->nullable();
            $table->integer('changed_qty')->nullable();
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
        Schema::table('transaction_details', function (Blueprint $table) {
            //
            $table->dropColumn('previous_qty');
            $table->dropColumn('current_qty');
            $table->dropColumn('changed_qty');
        });
    }
}
