<?php
namespace App\Helpers;

class GeneratePassword{

    public function __construct(){}

    public function generate($longitud = 8){
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $password = "";
        for ($i = 0; $i < $longitud; $i++) {
            $password .= substr($str, rand(0, 36), 1);
        }
        return $password;
    }
}
