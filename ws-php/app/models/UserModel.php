<?php
namespace App\models;

use App\services\UserService;

class UserModel {

    public static function viewUsers(){
        return UserService::viewUsers();
    }

    public static function viewUser($id){
        return UserService::viewUser($id);
    }

    public static function updateUser($id, $name, $password, $phone){
        return UserService::updateUser($id, $name, $password, $phone);
    }

    public static function deleteUser($id){
        return UserService::deleteUser($id);
    }
}
?>