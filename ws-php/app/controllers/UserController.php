<?php
namespace App\controllers;

use App\models\UserModel;

class UserController {

    public function viewUsers(){
        echo json_encode(UserModel::viewUsers());
    }

    public function viewUser($data){
        $id = $data->idusers ?? $data->id ?? ''; 
        echo json_encode(UserModel::viewUser($id));
    }

    // --- CREAR USUARIO ---
    public function createUser($data) {
        $name = $data->name ?? '';
        $phone = $data->phone ?? '';
        $password = $data->password ?? '';
        $rol = $data->rol ?? ''; 
        $email = $data->email ?? ''; 

        if (empty($name) || empty($password) || empty($rol)) {
            echo json_encode(["error" => true, "msg" => "Faltan datos obligatorios"]);
            return;
        }

        if ($rol == 3) {
            echo json_encode(["error" => true, "msg" => "No se pueden crear clientes desde el panel administrativo."]);
            return;
        }

        // Pasamos el email al modelo
        echo json_encode(UserModel::createUser($name, $password, $phone, $rol, $email));
    }

    // --- ACTUALIZAR USUARIO ---
    public function updateUser($data){
        try {
            $id = $data->idusers ?? $data->id ?? '';
            $name = $data->name ?? '';
            $phone = $data->phone ?? '';
            $password = (!empty($data->password)) ? $data->password : null;
            $rol = $data->rol ?? null; 
            $email = $data->email ?? '';

            if(empty($id)) {
                echo json_encode(["error" => true, "msg" => "Falta el ID del usuario"]);
                return;
            }

            // Pasamos el email al modelo
            $resultado = UserModel::updateUser($id, $name, $password, $phone, $rol, $email);

            echo json_encode($resultado);

        } catch (\Throwable $th) {
            echo json_encode([
                "error" => true, 
                "msg" => "Error PHP: " . $th->getMessage()
            ]);
        }
    }

    public function deleteUser($data){
        $id = $data->idusers ?? $data->id ?? '';
        if(empty($id)) {
            echo json_encode(["error" => true, "msg" => "Falta ID"]);
            return;
        }
        echo json_encode(UserModel::deleteUser($id));
    }
}
?>