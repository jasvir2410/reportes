<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
     {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('primer_nombre');
            $table->string('segundo_nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('username')->unique();
            $table->unsignedTinyInteger('estado_id');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->enum('role',['admin','backoffice','supervisor','user']);
            $table->rememberToken();
            $table->timestamps();
            $table->TinyInteger('change_password',false,true)->default(0);
            $table->TinyInteger('change_role',false,true)->default(0);

            /*creando las relaciones entre tablas y llaves primarias*/
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
        Schema::drop('users');
    }
}
