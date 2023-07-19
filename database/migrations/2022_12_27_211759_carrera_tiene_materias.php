<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CarreraTieneMaterias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carrera_tiene_materias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carrera_id');
            $table->unsignedBigInteger('grado_id');
            $table->unsignedBigInteger('materia_id');
            $table->string('cantidad_horas');
            $table->foreign('carrera_id')->references('id')->on('carreras');
            $table->foreign('materia_id')->references('id')->on('materias');
            $table->foreign('grado_id')->references('id')->on('grados');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carrera_tiene_materias');
    }
}
