<?php

    namespace App\controllers;
    
    use App\models\AuthModel;
    
    class AuthController
    {
        public function sign_in($data){
            echo json_encode(AuthModel::sign_in($data->name, $data->password));
        }
        public function sign_up($data){
            echo json_encode(AuthModel::sign_up($data->name,$data->password, $data->phone, $data->email));
        }

    }
?>