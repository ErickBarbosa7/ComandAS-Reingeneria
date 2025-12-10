<?php

namespace App\services;
use Config\database\Methods as db;
use Config\Jwt\Jwt;
use Config\utils\Utils;
use Config\utils\CustomException as excep;

class AuthService
{
    /*
    public static function sign_in(string $name, string $password){
        $query = (object)[
            "query" => "SELECT * FROM users WHERE name = ? and rol NOT IN (4);",
            "params" => [$name]
        ];
        $res = db::query_one($query);
        if ($res->error) throw new excep("004");
        $msj = $res->msg;
        if(!Utils::verify($password, $msj->password)) throw new excep("005");

        return (object)["error"=>false, "msg"=>$msj];
    }
    */

    // Metodo actual de inicio de sesion
    public static function sign_in(string $name, string $password){
        // Consulta para obtener al usuario que coincida con el nombre y este activo
        $query = (object)[
            "query" => "SELECT * FROM users WHERE name = ? AND active = 1;",
            "params" => [$name]
        ];

        // Ejecutamos la consulta usando el metodo query_one
        $res = db::query_one($query);

        // Si hubo error en la DB se lanza la excepcion personalizada 004
        if ($res->error) throw new excep("004");

        // Obtenemos los datos del usuario
        $msj = $res->msg;
        
        // Verificamos la contraseña (comparada contra el hash guardado)
        if(!password_verify($password, $msj->password)) throw new excep("005");

        // Retornamos el usuario si todo es correcto
        return (object)["error"=>false, "msg"=>$msj];
    }

    /*
    public static function sign_up(String $name, String $password, String $id, String $phone)
    {
        //Insertar un usuario
        $query = (object)[
            "query" => "INSERT INTO `users`(`idusers`, `name`, `password`, `phone`, `rol`) VALUES (?,?,?,?,?)",
            "params" => [$id, $name, $password, $phone, "2"]
        ];
        return db::save($query);
    }
    */

    // Metodo actual para registrar usuario
    public static function sign_up(String $name, String $password, String $phone, String $email)
    {
        // Hasheamos la contraseña antes de insertarla
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Consulta para insertar un nuevo usuario con UUID(), rol 3 y active = 1
        $query = (object)[
            "query" => "INSERT INTO `users`(`idusers`, `name`, `password`, `phone`, `email`, `rol`, `active`) 
                        VALUES (UUID(), ?, ?, ?, ?, '3', 1)",
            "params" => [$name, $pass_hash, $phone, $email]
        ];

        // Ejecutamos la operacion en DB
        $result = db::save($query);
        $resObj = (object)$result;

        // Si la insercion fue exitosa mandamos un webhook (notificacion)
        if (isset($resObj->error) && !$resObj->error) {
            
            // URL donde se mandara la notificacion
            $webhookUrl = "https://eov4k2nv1bo1nr0.m.pipedream.net"; 
            
            // Enviamos los datos a traves del webhook
            Utils::sendWebhook($webhookUrl, [
                "name"  => $name,
                "email" => $email
            ]);
        }

        // Retornamos el resultado final
        return $result;
    }

}

