<?php

namespace App\services;
use Config\database\Methods as db;
use Config\Jwt\Jwt;
use Config\utils\Utils;
use Config\utils\CustomException as excep;

class AuthService
{
    /*public static function sign_in(string $name, string $password){
        $query = (object)[
            "query" => "SELECT * FROM users WHERE name = ? and rol NOT IN (4);",
            "params" => [$name]
        ];
        $res = db::query_one($query);
        if ($res->error) throw new excep("004");
        $msj = $res->msg;
        if(!Utils::verify($password, $msj->password)) throw new excep("005");

        return (object)["error"=>false, "msg"=>$msj];
    }*/
    public static function sign_in(string $name, string $password){
        $query = (object)[
            "query" => "SELECT * FROM users WHERE name = ? AND active = 1;",
            "params" => [$name]
        ];
        $res = db::query_one($query);
        if ($res->error) throw new excep("004");
        $msj = $res->msg;
        
        // Verifica la contraseña (encriptada)
        if(!password_verify($password, $msj->password)) throw new excep("005");

        return (object)["error"=>false, "msg"=>$msj];
    }

    /*public static function sign_up(String $name, String $password, String $id, String $phone)
    {
        //Insertar un usuario
        $query = (object)[
            "query" => "INSERT INTO `users`(`idusers`, `name`, `password`, `phone`, `rol`) VALUES (?,?,?,?,?)",
            "params" => [$id, $name, $password, $phone, "2"]
        ];
        return db::save($query);
    }*/
    public static function sign_up(String $name, String $password, String $phone, String $email)
    {
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $query = (object)[
            "query" => "INSERT INTO `users`(`idusers`, `name`, `password`, `phone`, `email`, `rol`, `active`) 
                        VALUES (UUID(), ?, ?, ?, ?, '3', 1)",
            "params" => [$name, $pass_hash, $phone, $email]
        ];

        // Guardamos el resultado de la DB
        $result = db::save($query);
        $resObj = (object)$result;

        // Verificamos si NO hubo error
        if (isset($resObj->error) && !$resObj->error) {
            
            $webhookUrl = "https://eov4k2nv1bo1nr0.m.pipedream.net"; 
            
            
            Utils::sendWebhook($webhookUrl, [
                "name"  => $name,
                "email" => $email
            ]);
        }

        return $result;
    }

}
