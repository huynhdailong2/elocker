<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWriteOffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('write_offs', function (Blueprint $table) {
            $table->text('cluster_name')->nullable();
            $table->text('cabinet_name')->nullable();
            $table->text('bin_name')->nullable();
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
        Schema::table('write_offs', function (Blueprint $table) {
            $table->dropColumn('cluster_name');
            $table->dropColumn('cabinet_name');
            $table->dropColumn('bin_name');
        });
    }
}
