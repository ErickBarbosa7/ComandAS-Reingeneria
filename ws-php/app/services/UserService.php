<?php
namespace App\services;

use Config\database\Methods as db;

class UserService {

    // Ver todos
    public static function viewUsers(){
        $query = (object)[
            "query" => "SELECT idusers, name, phone, rol, email FROM users ORDER BY name ASC",
            "params" => []
        ];
        return db::query($query);
    }

    // Ver uno
    public static function viewUser($id){
        $query = (object)[
            "query" => "SELECT idusers, name, phone, rol, email FROM users WHERE idusers = ?",
            "params" => [$id]
        ];
        return db::query_one($query);
    }

    // Crear usuario 
    public static function createUser($id, $name, $password, $phone, $rol, $email){
        $query = (object)[
            "query" => "INSERT INTO users (idusers, name, password, phone, rol, email) 
                        VALUES (?, ?, ?, ?, ?, ?)",
            "params" => [$id, $name, $password, $phone, $rol, $email]
        ];
        return db::save($query);
    }

    // Actualizar usuario
    public static function updateUser($id, $name, $password, $phone, $rol, $email){
        
        if ($password !== null && $password !== "") {
            $query = (object)[
                "query" => "UPDATE users 
                            SET name=?, password=?, phone=?, rol=?, email=? 
                            WHERE idusers=?",
                "params" => [$name, $password, $phone, $rol, $email, $id]
            ];
        } else {
            $query = (object)[
                "query" => "UPDATE users 
                            SET name=?, phone=?, rol=?, email=? 
                            WHERE idusers=?",
                "params" => [$name, $phone, $rol, $email, $id]
            ];
        }

        return db::save($query);
    }

    // Eliminar usuario
    public static function deleteUser($id){
        $query = (object)[
            "query" => "DELETE FROM users WHERE idusers = ?",
            "params" => [$id]
        ];
        
        $res = db::save($query);

        if(isset($res['error']) && $res['error']) {
            if (strpos($res['msg'], 'Constraint') !== false || strpos($res['msg'], 'foreign key') !== false) {
                return [
                    "error" => true,
                    "msg" => "No se puede eliminar: El usuario tiene historial de ventas y es necesario para los reportes."
                ];
            }
        }
        return $res;
    }
}
?>
