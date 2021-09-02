<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;

class RolController extends Controller
{
    //

    public function all(Request $request){

        $rol= Rol::all();
        $data=[
        'code'=>200,
        'status'=> 'success',
        'rol'=>$rol];
    return response()->json($data);


}
    public function pruebas(){
        $prueba=Rol::all();



       return response()->json($prueba);
    }
}
