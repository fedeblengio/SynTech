<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnosPertenecenGruposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('alumnos_pertenecen_grupos', function (Blueprint $table) {
            
           
           $table->id();
            $table->string('idGrupo',10);
            $table->string('idAlumnos');
           
            $table->unique(['idAlumnos', 'idGrupo']);
           
            $table->timestamps();
        });
        Schema::table('alumnos_pertenecen_grupos', function(Blueprint $table) {
            $table->foreign('idGrupo')->references('idGrupo')->on('grupos');
            $table->foreign('idAlumnos')->references('id')->on('alumnos');
        }); 
    }





    /**
     * 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumnos_pertenecen_grupos');
    }
}
