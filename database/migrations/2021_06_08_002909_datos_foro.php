<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatosForo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datosForo', function (Blueprint $table) {
            $table->unsignedBigInteger('idForo');
            $table->id();
            $table->string('idUsuario');
            $table->binary('mensaje');
           
            $table->timestamps();


            
        });

        Schema::table('datosForo', function(Blueprint $table) {
            $table->foreign('idForo')->references('id')->on('foros');
            $table->foreign('idUsuario')->references('id')->on('usuarios');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('datosForo');
    }
}
