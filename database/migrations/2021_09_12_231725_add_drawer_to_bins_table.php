<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDrawerToBinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bins', function (Blueprint $table) {
            if (!Schema::hasColumn('bins', 'drawer_name')) {
                $table->unsignedBigInteger('drawer_name')->after('bin')->nullable();
            }
            if (!Schema::hasColumn('bins', 'is_drawer')) {
                $table->unsignedTinyInteger('is_drawer')->after('description')->default(0);
            }
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
            $table->dropColumn('drawer_name');
            $table->dropColumn('is_drawer');
        });
    }
}
