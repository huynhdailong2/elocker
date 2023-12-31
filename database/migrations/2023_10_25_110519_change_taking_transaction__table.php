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
            $table->text('cluster_name')->nullable();
            $table->text('cabinet_name')->nullable();
            $table->text('bin_name')->nullable();
            $table->integer('request_qty')->nullable();
            $table->integer('cabinet_id')->nullable();
            $table->integer('bin_id')->nullable();
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
            $table->dropColumn('cluster_name');
            $table->dropColumn('cabinet_name');
            $table->dropColumn('bin_name');
            $table->dropColumn('request_qty');
            $table->dropColumn('cabinet_id');
            $table->dropColumn('bin_id');
        });
    }
}
