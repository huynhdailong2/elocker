<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReplenishQtyColumnEucBoxSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('euc_box_spares', function (Blueprint $table) {
            $table->bigInteger('quantity_replenish')->after('quantity_oh')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('euc_box_spares', function (Blueprint $table) {
            $table->datetime('quantity_replenish')->nullable();
        });
    }
}
