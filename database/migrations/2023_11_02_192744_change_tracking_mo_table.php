<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTrackingMoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('issue_cards', function (Blueprint $table) {
            $table->integer('taking_transaction_id')->nullable();
        });
        Schema::table('tracking_mo', function (Blueprint $table) {
            $table->integer('taking_transaction_id')->nullable();
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
        Schema::table('issue_cards', function (Blueprint $table) {
            $table->dropColumn('taking_transaction_id');
        });
        Schema::table('tracking_mo', function (Blueprint $table) {
            $table->dropColumn('taking_transaction_id');
        });
    }
}
