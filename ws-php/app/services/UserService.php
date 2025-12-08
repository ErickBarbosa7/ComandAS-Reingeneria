<?php
namespace App\services;

use Config\database\Methods as db;
// Asegúrate de tener los imports necesarios
// use Config\Jwt\Jwt; ... etc

class UserService {

    // Ver todos
    public static function viewUsers(){
        $query = (object)[
            "query" => "SELECT idusers, name, phone, rol, email FROM users WHERE active = 1",
            "params" => []
        ];
        return db::query($query);
    }

    // Ver uno (incluyendo email)
    public static function viewUser($id){
        $query = (object)[
            "query" => "SELECT idusers, name, phone, rol, email FROM users WHERE idusers = ? AND active = 1",
            "params" => [$id]
        ];
        return db::query_one($query);
    }

    // Actualizar usuario   
    public static function updateUser($id, $name, $password, $phone){
    if ($password !== "") {
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        $query = (object)[
            "query" => "UPDATE users SET name=?, password=?, phone=? WHERE idusers=?",
            "params" => [$name, $pass_hash, $phone, $id]
        ];
    } else {
        $query = (object)[
            "query" => "UPDATE users SET name=?, phone=? WHERE idusers=?",
            "params" => [$name, $phone, $id]
        ];
    }

    $result = db::query($query);

    // ✅ Si no hubo error, devolvemos el usuario actualizado
    if (!$result->error) {
        return self::viewUser($id);
    }

    return $result;
}

    // Eliminar
    public static function deleteUser($id){
        $query = (object)[
            "query" => "UPDATE users SET active = 0 WHERE idusers = ?",
            "params" => [$id]
        ];
        return db::query($query);
    }
}
?>