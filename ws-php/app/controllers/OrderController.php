<?php

namespace App\controllers;

use App\models\OrderModel;

class OrderController
{
    public function createOrder($data){
    echo json_encode(OrderModel::createOrder(
        $data->total, 
        $data->origin, 
        $data->comments, 
        $data->client, 
        $data->users_idusers, 
        $data->order_details
    ));
}

    public function viewOrders($data){
        $id_user = $data->id_user ?? null;
        
        // Pasarlo al modelo
        echo json_encode(OrderModel::viewOrders($id_user));
    }
    public function viewOrder($data){
        echo json_encode(OrderModel::viewOrder($data->idorder));
    }
    public function updateStatus($data){
        echo json_encode(OrderModel::updateStatus($data->status, $data->idorder, $data->users_idusers));
    }
    public function lastOrder($data){
        echo json_encode(OrderModel::lastOrder($data->iduser));
    }
    // Ver ordenes por usuario (Para el cliente)
    public function viewOrdersByUser($data){
        echo json_encode(OrderModel::viewOrdersByUser($data->iduser));
    }
}
