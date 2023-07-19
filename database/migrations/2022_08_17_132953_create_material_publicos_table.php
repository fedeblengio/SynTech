<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialPublicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_publicos', function (Blueprint $table) {
            $table->id();
            $table->string('idUsuario');
            $table->string('titulo');
            $table->binary('mensaje');
            $table->binary('imgEncabezado');
            $table->timestamps();
        });

        Schema::table('material_publicos', function(Blueprint $table) {
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
        Schema::dropIfExists('material_publicos');
    }
}
