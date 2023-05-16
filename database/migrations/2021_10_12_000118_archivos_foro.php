<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchivosForo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archivos_foro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idForo');
            $table->unsignedBigInteger('idDato');
            $table->string('nombreArchivo')->nullable();
            $table->timestamps();

        });
        Schema::table('archivos_foro', function(Blueprint $table) {
            $table->foreign('idForo')->references('id')->on('foros');
            $table->foreign('idDato')->references('id')->on('datosForo');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archivos_foro');
    }
}
