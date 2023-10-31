<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditColumnJobCardIdIssueCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('issue_cards', function (Blueprint $table) {
            $table->integer('job_card_id')->nullable()->change();
        });
        Schema::table('tracking_mo', function (Blueprint $table) {
            $table->integer('job_card_id')->nullable()->change();
        });
        Schema::table('return_spares', function (Blueprint $table) {
            $table->integer('bin_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
