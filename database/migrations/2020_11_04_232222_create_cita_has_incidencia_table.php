<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitaHasIncidenciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cita_has_incidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('cita_id');
            $table->unsignedBigInteger('incidencia_id');
            $table->foreign('cita_id')->references('id')->on('cita');
            $table->foreign('incidencia_id')->references('id')->on('incidencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cita_has_incidencia');
    }
}
