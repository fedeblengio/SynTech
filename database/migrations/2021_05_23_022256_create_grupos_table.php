<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('idGrupo',10);
            $table->unsignedBigInteger('grado_id');
            $table->foreign('grado_id')->references('id')->on('grados');
            $table->unique(['idGrupo', 'grado_id']);
            $table->string('nombreCompleto')->nullable();
            $table->string('anioElectivo');
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
        Schema::dropIfExists('grupos');
    }
}
