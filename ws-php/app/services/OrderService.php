<?php

namespace App\services;

use Config\database\Methods as db;
use Config\utils\Utils;

class OrderService{

    // crea una orden completa con sus detalles e ingredientes removidos
    public static function createOrder(int $total, $origin, $comments, string $client, string $users_idusers, $order_details)
    {
        // id principal de la orden
        $idOrder = Utils::uuid();
        $query = [];

        // registro base de la orden
        $query[] = (object)[
            "query"  => "INSERT INTO `order`(`idorder`, `total`, `origin`, `comments`, `client`, `users_idusers`) 
                         VALUES (?,?,?,?,?,?)",
            "params" => [
                $idOrder,
                $total,
                $origin,
                $comments,
                $client,
                $users_idusers
            ]
        ];

        // recorrido de cada producto enviado en la orden
        foreach ($order_details as $value) {

            // id para el detalle de orden
            $idOrderDetails = Utils::uuid();

            // guardado del detalle
            $query[] = (object)[
                "query"  => "INSERT INTO `order_details`(`idorderdetail`, `unit_price`, `order_type`, `comments`, `order_idorder`, `products_idproducts`) 
                             VALUES (?,?,?,?,?,?)",
                "params" => [
                    $idOrderDetails,
                    $value->unit_price,
                    $value->order_type,
                    $value->comments,
                    $idOrder,
                    $value->products_idproducts
                ]
            ];

            // si el cliente pidio quitar ingredientes se registran aqui
            foreach ($value->not_ingredient as $ingredient) {
                $query[] = (object)[
                    "query"  => "INSERT INTO `not_ingredient`(`ingredients_idingredients`, `order_details_idorderdetail`, `type`) 
                                 VALUES (?,?,?)",
                    "params" => [
                        $ingredient->ingredients_idingredients,
                        $idOrderDetails,
                        $ingredient->type,
                    ]
                ];
            }
        }

        // ejecucion de todo el paquete de consultas
        $res = db::save_transaction($query);

        // si todo salio bien se obtiene la orden completa y se manda por socket
        if ($res['error'] == false) {

            $qOrder = (object)[
                "query" => "SELECT o.idorder, o.client, o.total, o.status, o.comments, o.date, o.users_idusers
                            FROM `order` o
                            WHERE o.idorder = ?",
                "params" => [$idOrder]
            ];

            $orderData = db::query_one($qOrder);

            // envio por socket solo si la orden se pudo recuperar bien
            if (!$orderData->error && !empty($orderData->msg)) {
                Utils::postSocket('comanda', $orderData->msg);
            }

            // se devuelve el id generado
            return [
                "error" => false,
                "msg"   => $idOrder
            ];
        }

        // si algo fallo se regresa el error tal cual
        return $res;
    }

    // obtiene ordenes activas, opcionalmente filtradas por usuario
    public static function viewOrders($id_user = null){
        
        // base de consulta para traer ordenes activas
        $sql = "SELECT o.idorder, o.client, o.total, o.status, o.comments, o.date, o.users_idusers 
                FROM `order` AS O 
                WHERE active='1'";
        
        $params = [];

        // si hay id de usuario se filtra
        if ($id_user != null) {
            $sql .= " AND o.users_idusers = ?";
            $params[] = $id_user;
        }

        // mas nuevas primero
        $sql .= " ORDER BY o.date DESC";

        $query = (object)[
            "query" => $sql,
            "params" => $params
        ];
        
        return db::query($query);
    }

    // trae una orden con sus productos e ingredientes removidos
    public static function viewOrder(string $idOrder){
        $query = (object)[
            "query" => "SELECT o.idorder, o.client, o.total, o.status, o.comments, od.idorderdetail, 
                        od.order_type, od.comments as comments_product, p.name product, c.name category 
                        FROM `order` AS O 
                        JOIN order_details od ON od.order_idorder = o.idorder 
                        JOIN products p ON p.idproducts=od.products_idproducts 
                        JOIN category c ON c.idcategory =p.category_idcategory 
                        WHERE idorder=?",
            "params" => [$idOrder]
        ];

        $res = db::query($query);
        $msg = $res->msg;

        // por cada producto se traen sus ingredientes removidos
        foreach ($msg as $key => $value) {
            $query = (object)[
                "query" => "SELECT ni.ingredients_idingredients, ni.type, i.name 
                            FROM not_ingredient ni 
                            JOIN ingredients i ON ni.ingredients_idingredients = i.idingredients 
                            WHERE order_details_idorderdetail=?",
                "params" => [$value->idorderdetail]
            ];
            $res2 = db::query($query);
            $msg[$key]->ingredients=$res2->msg;
        }

        return (object)["error"=>false, "msg"=>$msg];
    }

    // actualiza el estado de una orden segun el flujo de cocina
    public static function updateStatus(int $status, string $idOrder, string $users_idusers){
        $query = []; 

        // bloque segun estado de orden
        switch ($status) {
            case 1: // en cocina
                $query[] = (object)[ "query" => "UPDATE `order` SET status=?, start_order = CURRENT_TIMESTAMP() WHERE idorder=?", "params" => [$status, $idOrder] ];
                $query[] = (object)[ "query" => "UPDATE `users` SET actual_order=? WHERE idusers=?", "params" => [$idOrder, $users_idusers] ];
                break;

            case 2: // lista
                $query[] = (object)[ "query" => "UPDATE `order` SET status=?, finish_order = CURRENT_TIMESTAMP() WHERE idorder=?", "params" => [$status, $idOrder] ];
                $query[] = (object)[ "query" => "UPDATE `users` SET actual_order=null WHERE idusers=?", "params" => [$users_idusers] ];
                break;

            case 3: // completada
            case 4: // cancelada
                $query[] = (object)[ "query" => "UPDATE `order` SET status=? WHERE idorder=?", "params" => [$status, $idOrder] ];
                break;
        }
        
        $res = db::save_transaction($query);

        // si todo funciono se manda update por socket y correo si aplica
        if (!$res['error']) {
            
            // obtener orden con datos de usuario dueño
            $qFullOrder = (object)[
                "query" => "SELECT o.idorder, o.client, o.total, o.status, o.comments, o.date, 
                                   o.users_idusers, u.email, u.name 
                            FROM `order` o 
                            JOIN users u ON o.users_idusers = u.idusers 
                            WHERE o.idorder = ?",
                "params" => [$idOrder]
            ];
            $orderData = db::query_one($qFullOrder);
            
            if (!$orderData->error && !empty($orderData->msg)) {
                $theOrder = $orderData->msg;

                // update por socket
                Utils::postSocket('comanda', $theOrder);

                // si la orden esta lista se dispara webhook
                if ($status == 2 && !empty($theOrder->email)) {
                    $webhookUrl = "https://eojiw3aqyohllb7.m.pipedream.net";
                    
                    Utils::sendWebhook($webhookUrl, [
                        "name"     => $theOrder->name,
                        "email"    => $theOrder->email,
                        "id_order" => $idOrder
                    ]);
                }
            }
        }

        return $res;
    }

    // obtiene la ultima orden que un usuario tiene en proceso
    public static function lastOrder(string $iduser){
        $query = (object)[
            "query" => "SELECT u.actual_order, o.status 
                        FROM users u 
                        JOIN `order` o ON o.idorder= u.actual_order 
                        WHERE u.idusers = ?",
            "params" => [$iduser]
        ];

        $res = db::query_one($query);

        // si no hay orden asociada se regresa nulo
        if(!isset($res->msg) || empty($res->msg) || !isset($res->msg->status)){
             return ['error'=>false, 'msg'=>null];
        }

        // si esta en cocina se regresa completa
        if($res->msg->status == 1){
            return self::viewOrder($res->msg->actual_order);
        }

        return ['error'=>false,'msg'=>null];
    }

    // obtiene todas las ordenes de un usuario en especifico
    public static function viewOrdersByUser($idUser){
        $query = (object)[
            "query" => "SELECT o.idorder, o.client, o.total, o.status, o.comments, o.date 
                        FROM `order` AS O 
                        WHERE users_idusers = ? 
                        ORDER BY o.date DESC",
            "params" => [$idUser]
        ];
        return db::query($query);
    }
}
