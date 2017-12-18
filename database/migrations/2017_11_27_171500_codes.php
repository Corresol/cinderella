<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Codes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 15);
            $table->string('code', 6);
            $table->dateTime('created_at');

            $table->unique('phone');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::dropIfExists('codes');
    }
}
