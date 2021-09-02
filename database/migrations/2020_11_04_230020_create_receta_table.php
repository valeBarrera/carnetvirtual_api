<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receta', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_emision');
            $table->date('ultima_fecha_surtido')->nullable();
            $table->date('proximo_retiro')->nullable();
            $table->text('prescripcion');
            $table->text('indicaciones');
            $table->string('estado', 255);
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('farmacia_id')->nullable();
            $table->unsignedBigInteger('medico_id');
            $table->timestamps();
            $table->foreign('paciente_id')->references('id')->on('paciente');
            $table->foreign('farmacia_id')->references('id')->on('farmacia');
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
        Schema::dropIfExists('receta');
    }
}
