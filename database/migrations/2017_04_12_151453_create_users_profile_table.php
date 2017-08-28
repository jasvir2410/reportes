<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_profile', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('user_id',false,true);
            $table->unsignedBigInteger('dni',false);
            $table->string('telefono',15);
            $table->enum('Sexo', ['M', 'F']);
            $table->date('fecha_nacimiento');
            $table->string('avatar',250)->default('default_avatar.png');
            $table->string('ubigeo_id',6);
            /*creando las relaciones entre tablas y llaves primarias*/
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::drop('users_profile');
    }
}
