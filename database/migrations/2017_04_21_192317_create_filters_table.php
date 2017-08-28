<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filters', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('column_id',false,true);
            $table->unsignedBigInteger('contition_id',false);
            $table->string('value',70);
            $table->TinyInteger('estado_id',false,true);
            $table->string('apply',70);
            $table->unsignedInteger('user_id',false,true);
            /*creando las relaciones entre tablas y llaves primarias*/
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');

            $table->foreign('estado_id')
                  ->references('id')
                  ->on('estados');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('filters');
    }
}
