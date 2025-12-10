<?php
    namespace App\models;

    use Config\Jwt\Jwt;
    use Config\utils\Utils as util;
    use App\services\AuthService;
    use Config\utils\CustomException as excep;

    class AuthModel{
        // Metodo para iniciar sesion con nombre y password
        public static function sign_in(string $name, string $password){
            // Llamamos al servicio encargado de validar las credenciales
            $res = AuthService::sign_in($name, $password);

            // Si hay un error en el proceso, lanzamos una excepcion personalizada
            if($res->error) throw new excep("004");

            // Guardamos la informacion del usuario retornada por el servicio
            $msg = $res->msg;

            // Se elimina la contraseña para evitar devolverla en la respuesta
            unset($msg->password);

            // Se retorna la informacion del usuario junto con el token
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

        // Metodo para registrar un usuario usando los nuevos parametros permitidos
        public static function sign_up(string $name, string $password, string $phone, string $email)
        {
            // Simplemente enviamos los datos al servicio de registro
            return AuthService::sign_up($name, $password, $phone, $email);
        }

        

    }

?>