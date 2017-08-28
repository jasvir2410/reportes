<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('preguntas', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('name', 250);
            $table->unsignedInteger('tipo_encuesta_id');
            $table->TinyInteger('estado_id',false,true)->default(1)->index('FK_preguntas_estados');
            $table->timestamps();

            /*se crea las relaciones entre tablas y llaves foraneas*/
                $table->foreign('tipo_encuesta_id')
                      ->references('id')
                      ->on('tipo_encuestas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('preguntas');
    }
}
