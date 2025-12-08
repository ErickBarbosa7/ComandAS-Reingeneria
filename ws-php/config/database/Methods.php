<?php

namespace Config\database;

use Config\database\Connection as con;

//Excepciones específicas
use Exception;
//Excepciones en general
use Throwable;
use PDO;

class Methods
{

    //Metodo para ejecutar una consulta de varias columnas
    public static function query(Object $sql)
    {
        try {

            $db = con::conection();
            $stmt = $db->prepare($sql->query);
            $stmt->execute($sql->params);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            while ($row = $stmt->fetchObject()) {
                $results[] = $row;
            }
        } catch (Throwable $th) {
            return (object) ["error" => true, "msg" => "error_query", "error_code" => $th->getCode()];
        }
        //Cerrar conexion con la bd
        $db = null;
        return (object) ["error" => false, "msg" => @$results];
    }

    //Metodo para ejecutar una consulta de un solo resultado    
    public static function query_one(Object $obj)
    {
        try {

            $db = con::conection();
            $stmt = $db->prepare($obj->query);
            $stmt->execute($obj->params);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchObject();
        } catch (Throwable $th) {
            return (object) ["error" => true, "msg" => "error_query_one", "error_code" => $th->getCode()];
        }

        if ($results === false) $results = null;

        $db = null;
        return (object) ["error" => false, "msg" => $results];
    }

    //Metodo para guardar los datos en la base de datos
    public static function save(Object $obj)
    {
        $array = [];
        try {
            $db = con::conection();
            $stmt = $db->prepare($obj->query);
            $stmt->execute($obj->params);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            if ($stmt->fetchColumn()) {
                throw new Exception("query_error");
            }
            $db = null;
            $array = ["error" => false, "msg" => "querys_executed"];
        } catch (Throwable $th) {
            return (object) ["error" => true, "msg" => "error_save", "error_code" => $th->getCode()];
        }
        return $array;
    }

    //Metodo que ejecuta una transacción de consultas en una base de datos utilizando una lista de consultas preparadas
    public static function save_transaction(array $querys)
    {
        try {
            $db = con::conection();
            $db->beginTransaction();
            foreach ($querys as $obj) {
                $stmt = $db->prepare($obj->query);
                $stmt->execute($obj->params);
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $array[] = $stmt->fetchColumn();
                if (in_array(true, $array)) {
                    throw new Exception("error_in_one_of_the_queries");
                }
            }
            $db->commit();
            $db = null;
            $array = ["error" => false, "msg" => "querys_executed"];
        } catch (Throwable $th) {
            $array = (object) ["error" => true, "msg" => "error_save_transaction", "error_code" => $th->getCode()];
        }
        return $array;
    }
}
