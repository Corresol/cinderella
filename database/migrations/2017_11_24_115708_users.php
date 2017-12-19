<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('api_token', 32);
            $table->date('created');
            $table->string('phone', 15);
            $table->string('pin', 60);
            $table->string('recovery_phrase', 255);
            $table->mediumInteger('last_address', FALSE, TRUE);
            $table->date('premium_news')->nullable()->default(NULL);

            $table->unique('api_token');
            $table->unique('phone');
            $table->unique('recovery_phrase');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::dropIfExists('users');
    }
}
