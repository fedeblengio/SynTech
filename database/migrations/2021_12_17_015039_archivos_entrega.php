<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchivosEntrega extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archivos_entrega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTareas');
            $table->string('idAlumnos');
            $table->string('nombreArchivo')->nullable();
            $table->timestamps();
        });
        Schema::table('archivos_entrega', function(Blueprint $table) {
            $table->foreign('idTareas')->references('id')->on('tareas');
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
        Schema::dropIfExists('archivos_entrega');
    }
}
