<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIdTakingTransactionDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taking_transaction_details', function (Blueprint $table) {
            //
            $table->integer('request_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taking_transaction_details', function (Blueprint $table) {
            //
            $table->dropColumn('request_qty');
        });
    }
}
