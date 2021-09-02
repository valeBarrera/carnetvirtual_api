<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMesonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meson', function (Blueprint $table) {
            $table->id();
            $table->integer('run')->unique();
            $table->string('nombres', 255);
            $table->string('apellidos', 255);
            $table->date('fecha_nacimiento');
            $table->string('telefono', 9);
            $table->string('cargo', 50);
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meson');
    }
}
