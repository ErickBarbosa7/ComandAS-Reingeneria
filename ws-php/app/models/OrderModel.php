<?php

namespace App\models;

use App\services\OrderService;
use Config\utils\CustomException as excep;

class OrderModel
{
    public static function createOrder(int $total, $origin, $comments, string $client, string $users_idusers, $order_details){
        $res = OrderService::createOrder($total, $origin, $comments, $client, $users_idusers, $order_details);
        if($res['error']) throw new excep("008");
        $msg = $res['msg'];
        return ['error'=>false, 'msg'=>['idorder'=>$msg, 'client'=>$client,'total'=>$total,'status'=>0,'comment'=>$comments]];
    }
    public static function viewOrders($id_user = null){
        return OrderService::viewOrders($id_user);
    }
    public static function viewOrder($idorder){
        return OrderService::viewOrder($idorder);
    }
    // Ver ordenes por usuario (Para el cliente)
    public static function viewOrdersByUser($idUser){
        return OrderService::viewOrdersByUser($idUser);
    }
    public static function updateStatus($status, $idorder, $users_idusers){
        return OrderService::updateStatus($status, $idorder, $users_idusers);
    }
    public static function lastOrder($iduser){
        return OrderService::lastOrder($iduser);
    }
}
