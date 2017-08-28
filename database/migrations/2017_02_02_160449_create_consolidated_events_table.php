<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsolidatedEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consolidated_events', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->date('date')->index('search_date');
            $table->string('agente');
            $table->unsignedInteger('login');
            $table->unsignedInteger('acd');
            $table->unsignedInteger('break');
            $table->unsignedInteger('sshh');
            $table->unsignedInteger('refrigerio');
            $table->unsignedInteger('feedback');
            $table->unsignedInteger('capacitacion');
            $table->unsignedInteger('backoffice');
            $table->unsignedInteger('inbound');
            $table->unsignedInteger('outbound');
            $table->unsignedInteger('acw');
            $table->unsignedInteger('desconectado');
            $table->unsignedInteger('logueado');
            $table->unsignedInteger('auxiliar');
            $table->unsignedInteger('talk_time');
            $table->unsignedInteger('saliente_hablado');
            $table->unique(['date','agente']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('consolidated_events');
    }
}
