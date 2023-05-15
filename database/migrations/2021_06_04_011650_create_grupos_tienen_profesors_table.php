<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposTienenProfesorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupos_tienen_profesor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idMateria');
            $table->string('idGrupo', 10);
            $table->string('idProfesor');
            $table->unique(['idGrupo','idProfesor','idMateria']);
            $table->timestamps();
        });

        Schema::table('grupos_tienen_profesor', function (Blueprint $table) {
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
        Schema::dropIfExists('grupos_tienen_profesors');
    }
}
