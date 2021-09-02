<?php

use App\Models\Farmacia;
use App\Models\Medico;
use App\Models\Meson;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $rol = new Rol();
        $rol->nombre = 'Paciente';
        $rol->descripcion = 'Rol Paciente';
        $rol->save();

        $rol = new Rol();
        $rol->nombre = 'Mesón';
        $rol->descripcion = 'Rol Mesón';
        $rol->save();

        $rol = new Rol();
        $rol->nombre = 'Farmacia';
        $rol->descripcion = 'Rol Farmacia';
        $rol->save();

        $rol = new Rol();
        $rol->nombre = 'Médico';
        $rol->descripcion = 'Rol Médico';
        $rol->save();


        $usuario = new Usuario();
        $usuario->email = "paciente1@mail.com";
        $usuario->username = "paciente1";
        $usuario->change_password = False;
        $usuario->password = Hash::make("paciente");
        $usuario->rol_id = Paciente::ID_PACIENTE;
        $usuario->save();

        $paciente = new Paciente();
        $paciente->run = "11222333";
        $paciente->nombres = "Paciente";
        $paciente->apellidos = "Uno";
        $paciente->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", "01-01-2000");
        $paciente->telefono = "922223333";
        $paciente->usuario_id = $usuario->id;
        $paciente->save();

        $usuario = new Usuario();
        $usuario->email = "meson1@mail.com";
        $usuario->username = "meson1";
        $usuario->change_password = False;
        $usuario->password = Hash::make("meson123");
        $usuario->rol_id = Meson::ID_MESON;
        $usuario->save();

        $paciente = new Meson();
        $paciente->run = "11444333";
        $paciente->nombres = "Mesón";
        $paciente->apellidos = "Uno";
        $paciente->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", "01-01-1999");
        $paciente->cargo = "Administrador";
        $paciente->telefono = "999993333";
        $paciente->usuario_id = $usuario->id;
        $paciente->save();

        $usuario = new Usuario();
        $usuario->email = "medico1@mail.com";
        $usuario->username = "medico1";
        $usuario->change_password = False;
        $usuario->password = Hash::make("medico1");
        $usuario->rol_id = Medico::ID_MEDICO;
        $usuario->save();

        $paciente = new Medico();
        $paciente->run = "11666333";
        $paciente->nombres = "Médico";
        $paciente->apellidos = "Uno";
        $paciente->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", "01-01-1999");
        $paciente->especialidad = "CIRUGIA GENERAL";
        $paciente->telefono = "999993333";
        $paciente->usuario_id = $usuario->id;
        $paciente->save();

        $usuario = new Usuario();
        $usuario->email = "farmacia1@mail.com";
        $usuario->username = "farmacia1";
        $usuario->change_password = False;
        $usuario->password = Hash::make("farmacia1");
        $usuario->rol_id = Farmacia::ID_FARMACIA;
        $usuario->save();

        $paciente = new Farmacia();
        $paciente->run = "11555333";
        $paciente->nombres = "Farmacia";
        $paciente->apellidos = "Uno";
        $paciente->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", "01-01-1999");
        $paciente->cargo = "Químico Farmacéutico";
        $paciente->telefono = "999993333";
        $paciente->usuario_id = $usuario->id;
        $paciente->save();
    }
}
