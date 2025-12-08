<?php
namespace App\controllers;

use App\models\MenuModel;

class MenuController{
    public function viewIngredients(){
        echo json_encode(MenuModel::viewIngredients());
    }
}

?>