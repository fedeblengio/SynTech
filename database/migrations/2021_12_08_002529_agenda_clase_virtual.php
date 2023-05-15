<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgendaClaseVirtual extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('agenda_clase_virtual', function (Blueprint $table) {
            $table->id();
            $table->string('idProfesor');
            $table->unsignedBigInteger('idMateria');
            $table->string('idGrupo',10);
            $table->string('fecha_inicio');
            $table->string('fecha_fin');
            $table->timestamps();
        });

        Schema::table('agenda_clase_virtual', function (Blueprint $table) {
            $table->foreign('idGrupo')->references('idGrupo')->on('grupos');
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
        Schema::dropIfExists('agenda_clase_virtual');
    }
}
