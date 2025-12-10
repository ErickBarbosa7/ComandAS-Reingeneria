<?php
namespace App\models;

use App\services\UserService;
use Config\utils\Utils;

class UserModel {

    public static function viewUsers(){
        return UserService::viewUsers();
    }

    public static function viewUser($id){
        return UserService::viewUser($id);
    }

    public static function createUser($name, $password, $phone, $rol, $email){
        $id = Utils::uuid();
        $pass_hash = Utils::hash($password);
        
        return UserService::createUser($id, $name, $pass_hash, $phone, $rol, $email);
    }

    public static function updateUser($id, $name, $password, $phone, $rol = null, $email){
        
        $pass_hash = (!empty($password)) ? Utils::hash($password) : null;
        
        return UserService::updateUser($id, $name, $pass_hash, $phone, $rol, $email);
    }

    public static function deleteUser($id){
        return UserService::deleteUser($id);
    }
}
?>
