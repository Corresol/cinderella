<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Transactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('transactions', function (Blueprint $table) {
            $table->string('transaction_id');
            $table->integer('user_id', FALSE, TRUE);
            $table->enum('type', ['sent', 'received']);
            $table->timestamp('time');
            $table->float('amount', 16, 8);
            $table->text('addresses');

            $table->primary('transaction_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::dropIfExists('transactions');
    }
}
