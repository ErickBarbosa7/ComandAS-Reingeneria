<?php
    namespace App\models;

    use Config\Jwt\Jwt;
    use Config\utils\Utils as util;
    use App\services\AuthService;
    use Config\utils\CustomException as excep;

    class AuthModel{
        public static function sign_in(string $name, string $password){
            $res = AuthService::sign_in($name, $password);
            if($res->error) throw new excep("004");
            $msg = $res->msg;
            //Para quitar datos específicos de un objeto, se usa unset
            unset($msg->password);
            return [
                "error" => false,
                "msg" =>[
                    "idusers"=>$msg->idusers,
                    "name"=>$msg->name,
                    "token"=>Jwt::SignIn($msg),
                    "phone"=>$msg->phone,
                    "rol"=>$msg->rol,
                    "actual_order"=>$msg->actual_order
                ]
            ];
        }
        /*public static function sign_up(string $name, string $password, string $phone){
            //Genera un id
            $id = util::uuid();
            //Hashear contraseña
            $pass_hash = util::hash($password);
            return AuthService::sign_up($name, $pass_hash, $id,$phone);
        }*/
        public static function sign_up(string $name, string $password, string $phone, string $email)
        {
            return AuthService::sign_up($name, $password, $phone, $email);
        }

        

    }

?>