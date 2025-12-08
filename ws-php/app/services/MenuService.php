<?php

namespace App\services;
use Config\database\Methods as db;
use Config\utils\Utils;

class MenuService{
    public static function viewIngredients(){
        $query = (object)[
            "query" => "SELECT p.idproducts, p.name, p.price, p.description, p.category_idcategory, c.name as name_category FROM products as p JOIN category c ON c.idcategory = p.category_idcategory WHERE p.active = 1 ORDER BY p.category_idcategory, p.name",
            "params" => []
        ];
        $res = db::query($query);
        $msg = $res->msg;
        foreach ($msg as $key => $value) {
            $query = (object)[
                "query" => "SELECT i.idingredients, i.name, i.extra, i.cost, i.stock, i.required FROM products_ingredients pi JOIN ingredients i ON i.idingredients = pi.ingredients_idingredients WHERE pi.products_idProducts = ? ORDER BY i.name",
                "params" => [$value->idproducts]
            ];
            $res2 = db::query($query);
            $msg[$key]->ingredients=$res2->msg;
        }
        return (object)["error"=>false, "msg"=>$msg];

    }
    /* public static function sql(){
        $query = (object)[
            "query"=> "INSERT INTO `products_ingredients`(`id`, `products_idProducts`, `ingredients_idingredients`) 
            VALUES 
            (?,'8b4b5018-5e5c-457a-ab87-7b3b6064b86a','aae9dd0c-0e48-493d-ab3c-956946a5927a'),
            (?,'8b4b5018-5e5c-457a-ab87-7b3b6064b86a','175b29fd-1a2a-4d03-b7fd-7d71b16278bd'),
            (?,'8b4b5018-5e5c-457a-ab87-7b3b6064b86a','ae372207-d080-471a-be7d-042e360ff707');",
            "params" => [
                Utils::uuid(),
                Utils::uuid(),
                Utils::uuid()
            ]
            ];
            return db::save($query);
    } */
}

?>