<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfesoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profesores', function (Blueprint $table) {

            $table->string('id');
            $table->string('Cedula_Profesor');
            $table->primary(['id', 'Cedula_Profesor']);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('profesores', function(Blueprint $table) {
            $table->foreign('Cedula_Profesor')->references('id')->on('usuarios');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profesores');
    }
}
