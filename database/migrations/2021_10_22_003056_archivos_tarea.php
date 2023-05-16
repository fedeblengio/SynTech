<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchivosTarea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archivos_tarea', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTarea');
            $table->string('nombreArchivo')->nullable();
            $table->timestamps();
        });
        Schema::table('archivos_tarea', function(Blueprint $table) {
            $table->foreign('idTarea')->references('id')->on('tareas');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archivos_tarea');
    }
}
