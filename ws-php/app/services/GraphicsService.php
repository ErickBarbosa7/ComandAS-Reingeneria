<?php

namespace App\services;

use Config\database\Methods as db;
use Config\utils\Utils;

class GraphicsService{
    public static function bestSeller(){
        $query = (object)[
            "query" =>"SELECT p.name AS producto, COUNT(*) AS cantidad, MONTH(o.date) AS mes, c.name AS categoria FROM order_details od INNER JOIN products p ON p.idproducts = od.products_idproducts INNER JOIN `order` o ON o.idorder = od.order_idorder JOIN category c ON p.category_idcategory = c.idcategory WHERE YEAR(o.date) = YEAR(NOW()) GROUP BY p.name, c.name, MONTH(o.date) ORDER BY cantidad DESC;",
            "params" =>[]
        ];
        return db::query($query);
    }
    public static function totalSales(){
        $query = (object)[
            "query" => "SELECT SUM(total) total FROM `order` WHERE status=3",
            "params"=> []
        ];
        return db::query_one($query);
    }
    public static function bestClient(){
        $query = (object)[
            "query" => "SELECT client, COUNT(*) compras, SUM(total) total_compras FROM `order`  GROUP BY client ORDER BY COUNT(*) DESC",
            "params" => []
        ];
        return db::query($query);
    }
    public static function sales(){
        $query = (object)[
            "query"=> "SELECT SUM(o.total) total, MONTH(o.date) mes FROM `order` o  WHERE status=3 GROUP BY mes",
            "params"=> []
        ];
        return db::query($query);
    }
    public static function avgTime(){
        $query = (object) [
            "query"=> "SELECT AVG(TIMESTAMPDIFF(MINUTE, start_order, finish_order)) minutos from `order`",
            "params"=> []
        ];
        return db::query_one($query);
    }
}

?>