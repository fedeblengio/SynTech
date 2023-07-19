<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchivosMaterialPublico extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archivos_material_publico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idMaterialPublico');
            $table->string('nombreArchivo')->nullable();
            $table->timestamps();

        });

        Schema::table('archivos_material_publico', function(Blueprint $table) {
            $table->foreign('idMaterialPublico')->references('id')->on('material_publicos');
        }); 
           
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archivos_material_publico');
    }
}
