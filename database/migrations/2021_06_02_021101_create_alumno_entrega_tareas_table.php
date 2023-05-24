<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoEntregaTareasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('alumno_entrega_tareas', function (Blueprint $table) {
           
           
            $table->unsignedBigInteger('idTareas');
            $table->string('idAlumnos');
            $table->string('calificacion')->nullable();
            
            $table->string('mensaje_profesor')->nullable();
            $table->boolean('re_hacer');
            $table->string('mensaje')->nullable();
            $table->primary(['idAlumnos','idTareas']);
            $table->timestamps();
            
        });
        Schema::table('alumno_entrega_tareas', function(Blueprint $table) {
           
            $table->foreign('idAlumnos')->references('id')->on('alumnos');
            $table->foreign('idTareas')->references('idTareas')->on('profesor_crea_tareas');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_entrega_tareas');
    }
}