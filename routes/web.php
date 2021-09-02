<?php

use App\Helpers\GeneratePassword;
use App\Models\Cita;
use App\Models\Usuario;
use App\Notifications\ChangePassword;
use App\Notifications\NewCita;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/notification', function () {
    $cita = Cita::find(10);

    return (new NewCita($cita))
        ->toMail($cita->paciente->usuario);
});
