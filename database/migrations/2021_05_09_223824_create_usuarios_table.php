<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
    
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
         
            $table->string('id',8)->primary();
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->string('ou');
            $table->string('genero')->nullable();
            $table->string('imagen_perfil')->default('default_picture.png');
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
        Schema::dropIfExists('usuarios');
    }
}
