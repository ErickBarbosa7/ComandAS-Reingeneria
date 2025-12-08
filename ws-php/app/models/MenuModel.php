<?php

namespace App\models;

use App\services\MenuService;

class MenuModel{
    public static function viewIngredients(){
        return MenuService::viewIngredients();
    }
}

?>