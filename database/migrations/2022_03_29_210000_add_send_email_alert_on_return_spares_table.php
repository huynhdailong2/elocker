<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddSendEmailAlertOnReturnSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('return_spares', function (Blueprint $table) {
            $table->tinyInteger('send_mail_alert')->nullable(false)->unsigned()->default(0)->after('write_off');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('return_spares', function (Blueprint $table) {
            $table->dropColumn('send_mail_alert');
        });
    }
}
