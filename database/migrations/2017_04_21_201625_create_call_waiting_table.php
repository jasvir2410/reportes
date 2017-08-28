<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallWaitingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_waiting', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('number_phone',15);
            $table->string('name_number',80);
            $table->string('name_queue',50);
            $table->dateTime('start_call');
            $table->string('number_annexed',15);
            $table->string('name_agent',40);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('call_waiting');
    }
}
