<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('part_no');
            $table->string('material_no');
            $table->string('location')->nullable();
            $table->string('supplier_email')->nullable();
            $table->string('mat_grp')->nullable();
            $table->string('cricode')->nullable();
            $table->string('jom')->nullable();
            $table->string('item_acct')->nullable();
            $table->string('type');
            $table->unsignedTinyInteger('has_batch_no')->nullable();
            $table->unsignedTinyInteger('has_serial_no')->nullable();
            $table->unsignedTinyInteger('has_charge_time')->nullable();
            $table->unsignedTinyInteger('has_calibration_due')->nullable();
            $table->unsignedTinyInteger('has_expiry_date')->nullable();
            $table->unsignedTinyInteger('has_load_hydrostatic_test_due')->nullable();
            $table->string('field1')->nullable();
            $table->string('field2')->nullable();
            $table->text('url')->nullable();
            $table->text('description')->nullable();
            $table->string('auditor')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spares');
    }
}
