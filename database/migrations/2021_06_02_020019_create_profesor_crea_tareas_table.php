<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfesorCreaTareasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('profesor_crea_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('idMateria');
            $table->unsignedBigInteger('idTareas');
            $table->string('idGrupo',10);
            $table->primary(['idGrupo', 'idTareas']);
            $table->string('idProfesor');
        
            $table->timestamps();
        });
        Schema::table('profesor_crea_tareas', function(Blueprint $table) {
            $table->foreign('idGrupo')->references('idGrupo')->on('grupos');
            $table->foreign('idTareas')->references('id')->on('tareas');
            $table->foreign('idMateria')->references('id')->on('materias');
            $table->foreign('idProfesor')->references('id')->on('profesores');
        }); 
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profesor_crea_tareas');
    }
}
