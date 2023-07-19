<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReHacerTareasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('re_hacer_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('idTareasNueva');
            $table->unsignedBigInteger('idTareas');
            $table->string('calificacion')->nullable();
            $table->primary(['idTareasNueva', 'idTareas']);
            $table->string('idAlumnos');
            $table->string('mensaje')->nullable();
            $table->string('mensaje_profesor')->nullable();
            $table->timestamps();
            
            

        });
        Schema::table('re_hacer_tareas', function(Blueprint $table) {
            $table->foreign('idTareas')->references('id')->on('tareas');
            $table->foreign('idTareasNueva')->references('id')->on('tareas');
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
        Schema::dropIfExists('re_hacer_tareas');
    }
}
