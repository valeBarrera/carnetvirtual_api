<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/usuario/login','UsuarioController@login');
Route::post('/usuario/login_web', 'UsuarioController@login_web');



Route::middleware(['auth:sanctum'])->group(function () {

    /* Usuarios */
    Route::post('/usuario/logout','UsuarioController@logout');
    Route::post('/usuario/all', 'UsuarioController@all');
    Route::post('/usuario/refresh', 'UsuarioController@refresh');
    Route::post('/usuario/get', 'UsuarioController@getUser');
    Route::post('/usuario/reset_password', 'UsuarioController@resetPassword');
    Route::post('/usuario/change_password', 'UsuarioController@changePassword');

    Route::post('/paciente/registrar', 'PacienteController@registrar');
    Route::post('/farmacia/registrar', 'FarmaciaController@registrar');
    Route::post('/medico/registrar', 'MedicoController@registrar');
    Route::post('/meson/registrar', 'MesonController@registrar');
    Route::post('/paciente/modificar', 'PacienteController@modificar');
    Route::post('/farmacia/modificar', 'FarmaciaController@modificar');
    Route::post('/medico/modificar', 'MedicoController@modificar');
    Route::post('/meson/modificar', 'MesonController@modificar');

    /*Receta*/
    Route::post('/receta/obtener-id', 'RecetaController@obternerRecetasById');
    Route::post('/receta/obtener-run', 'RecetaController@obternerRecetasByRun');
    Route::post('/receta/registrar', 'RecetaController@registrarRecetas');
    Route::post('/receta/modificar', 'RecetaController@modificarRecetas');
    Route::post('/receta/surtir', 'RecetaController@surtirRecetas');
    Route::post('/receta/paciente', 'RecetaController@obtenerRecetasPaciente');

    /*Citas*/
    Route::post('/cita/obtener-id', 'CitaController@obternerCitasById');
    Route::post('/cita/obtener-run', 'CitaController@obternerCitasByRun');
    Route::post('/cita/registrar', 'CitaController@registrarCitas');
    Route::post('/cita/medicos', 'CitaController@obtenerMedicos');
    Route::post('/cita/cancelar', 'CitaController@cancelarCita');
    Route::post('/cita/notificar', 'CitaController@notificarNuevamente');

    /*Incidencias*/
    Route::post('/incidencia/obtener', 'IncidenciaController@obtenerIncidencias');
    Route::post('/incidencia/medicos', 'IncidenciaController@obtenerMedicos');
    Route::post('/incidencia/registrar', 'IncidenciaController@registrarIncidencias');

    /*Paciente*/
    Route::post('/paciente/citas', 'PacienteController@misCitas');
    Route::post('/paciente/recetas', 'PacienteController@misMedicamentos');

});

