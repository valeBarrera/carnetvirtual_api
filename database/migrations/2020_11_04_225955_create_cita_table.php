<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cita', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora');
            $table->string('estado', 255);
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('meson_id')->nullable();
            $table->unsignedBigInteger('medico_id');
            $table->timestamps();
            $table->foreign('paciente_id')->references('id')->on('paciente');
            $table->foreign('meson_id')->references('id')->on('meson');
            $table->foreign('medico_id')->references('id')->on('medico');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cita');
    }
}
