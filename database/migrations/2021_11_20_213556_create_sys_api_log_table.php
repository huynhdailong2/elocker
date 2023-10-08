<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysApiLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_api_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('method');
            $table->text('url');
            $table->longText('request')->nullable();
            $table->longText('response')->nullable();
            $table->string('host_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->datetime('request_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_api_logs');
    }
}
