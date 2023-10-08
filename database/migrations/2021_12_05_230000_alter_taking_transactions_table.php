<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTakingTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taking_transactions', function (Blueprint $table) {
            $table->bigInteger('job_card_id')->nullable(true)->unsigned()->after('remain_qty');
            $table->longText('remain_bins')->nullable(true)->after('remain_qty');
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
            $table->dropColumn('job_card_id');
            $table->dropColumn('remain_bins');
        });
    }
}
