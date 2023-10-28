<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTakingTransactionItemsTable extends Migration
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
            $table->integer('job_card_id')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->text('job_name')->nullable();
            $table->text('vehicle_num')->nullable();
            $table->integer('area_id')->nullable();
            $table->text('area_name')->nullable();
            $table->text('platform')->nullable();
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
            $table->dropColumn('job_card_id');
            $table->dropColumn('vehicle_id');
            $table->dropColumn('platform');
            $table->dropColumn('job_name');
            $table->dropColumn('vehicle_num');
            $table->dropColumn('area_id');
            $table->dropColumn('area_name');
        });
    }
}
