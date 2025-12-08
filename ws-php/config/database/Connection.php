<?php

    namespace Config\database;
    //Protocolo para la conexion a la bd que nos ayuda a que no nos hagan inyecciones
    use PDO;
    use \PDOException;
    class Connection
    {
        private static $host = "localhost";
        private static $db_name = "asburger_db";
        private static $user_name = "root";
        private static $password = "root";

        public static function conection()
        {
            try
            {
                return new PDO('mysql:host='.self::$host.';dbname='.self::$db_name, self::$user_name, self::$password);
            }
            catch(PDOException $e)
            {
                return null;
            }
        }
    }
?>