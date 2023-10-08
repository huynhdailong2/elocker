<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bins', function (Blueprint $table) {
            $table->bigInteger('process_by')->nullable(true)->unsigned()->after('is_locked');
            $table->timestamp('process_time')->nullable(true)->after('is_locked');
            $table->tinyInteger('is_processing')->nullable(false)->default(0)->index()->after('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bins', function (Blueprint $table) {
            $table->dropColumn('process_by');
            $table->dropColumn('process_time');
            $table->dropColumn('is_processing');
        });
    }
}
