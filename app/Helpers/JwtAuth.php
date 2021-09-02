<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Iluminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\Usuario;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'esta_es_la_clave_de_Carnet_Virtual';
    }

    public function signup($username,$password,$getToken=null){

    $user = Usuario::where('Username', $username)->first();


    $signup = false;

// Revisar este metodo que no esta validando correctamente
    if (Hash::check($password, $user->Password)){
      $signup = true;
 }

    var_dump(Hash::check($password, $user->Password));


    if($signup){
        $token=array(
            'sub'   => $user->IdUsuario,
            'Username' => $user->Username,
            'iat'=> time(),
            'exp'=> time() + (7*24*60*60)

        );
        $jwt = JWT::encode($token,$this->key,'HS256');
        $decoded = JWT::decode($jwt,$this->key,['HS256']);

        if(is_null($getToken)){
            $data = array('token'=>$jwt);
            //var_dump($data+'true');
            return $data;
        }else{
           $data = array('token'=>$decoded);
          // var_dump($data);
          return $data;
        }
    }else{
        $data=array(
            'status' => 'error',
            'message' => 'Login Incorrecto.'
        );
    }

    return $data;
    }
}


