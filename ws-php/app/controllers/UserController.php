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

    public function updateUser($data){
    try {
        $id = $data->idusers ?? $data->id ?? '';
        $name = $data->name ?? '';
        $phone = $data->phone ?? '';
        $password = (!empty($data->password)) ? $data->password : "";

        if(empty($id)) {
            echo json_encode(["error" => true, "msg" => "Falta el ID del usuario"]);
            return;
        }

        $resultado = UserModel::updateUser($id, $name, $password, $phone);

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
        echo json_encode(UserModel::deleteUser($id));
    }
}
?>