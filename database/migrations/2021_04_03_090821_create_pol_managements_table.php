<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pol_managements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('card_number')->nullable();
            $table->string('material_number')->nullable();
            $table->text('description')->nullable();
            $table->text('purpose_use')->nullable();
            $table->string('type')->nullable();
            $table->datetime('request_date')->nullable();
            $table->bigInteger('request_quantity')->nullable();
            $table->datetime('received_date')->nullable();
            $table->bigInteger('received_quantity')->nullable();
            $table->datetime('issued_date')->nullable();
            $table->bigInteger('issued_quantity')->nullable();
            $table->datetime('expiry_date')->nullable();
            $table->string('status');
            $table->string('request_by')->nullable();
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
        Schema::dropIfExists('pol_managements');
    }
}
