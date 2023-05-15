<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ListaAulaVirtual extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lista_aula_virtual', function (Blueprint $table) {
            $table->unsignedBigInteger('idClase');
            $table->string('idAlumnos');
            $table->boolean('asistencia');
            $table->primary(['idAlumnos','idClase']);
            $table->timestamps();
            
        });

        Schema::table('lista_aula_virtual', function(Blueprint $table) {
            $table->foreign('idClase')->references('id')->on('agenda_clase_virtual');
            $table->foreign('idAlumnos')->references('id')->on('alumnos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lista_aula_virtual');
    }
}
