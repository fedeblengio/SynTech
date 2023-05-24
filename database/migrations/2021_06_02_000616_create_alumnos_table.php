<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
   
        Schema::create('alumnos', function (Blueprint $table) {
           
            $table->string('id');
            $table->string('Cedula_Alumno');
            $table->primary(['id', 'Cedula_Alumno']);
            $table->timestamps();
            $table->softDeletes();
        });
         
        Schema::table('alumnos', function(Blueprint $table) {
            $table->foreign('Cedula_Alumno')->references('id')->on('usuarios');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumnos');
    }
}
