<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTakingTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('taking_transactions', function (Blueprint $table) {
            $table->text('request_qty');
            $table->dropColumn('item_id');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taking_transactions', function (Blueprint $table) {
            $table->dropColumn('request_qty');
            $table->interger('item_id');
        });
    }
}
