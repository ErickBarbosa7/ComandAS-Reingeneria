<?php

namespace App\models;

use App\services\GraphicsService;
use Config\utils\CustomException;

class GraphicsModel
{
    public static function totalSales()
    {
        return GraphicsService::totalSales();
    }
    public static function bestSeller($mes)
    {
        $res = GraphicsService::bestSeller();
        
        $count = 0;
        $labels = ["", "", ""];
        $data = ["", "", ""];
        
        // Si hay error, retornar el error
        if ($res->error) {
            return $res;
        }
        
        $msj = $res->msg;
        
        // Verificar si el mensaje es un string (error)
        if (is_string($msj)) {
            throw new CustomException('004');
        }
        
        // Iterar sobre los resultados
        foreach ($msj as $element) {
            if ($element->mes == $mes) {
                if ($count <= 2) {
                    if ($count == 0) {
                        $labels[1] = "#1 " . $element->categoria . ":" . $element->producto;
                        $data[1] = $element->cantidad;
                    } else if ($count == 1) {
                        $labels[0] = "#2 " . $element->categoria . ":" . $element->producto;
                        $data[0] = $element->cantidad;
                    } else {
                        $labels[2] = "#3 " . $element->categoria . ":" . $element->producto;
                        $data[2] = $element->cantidad;
                    }
                    $count++;
                }
            }
        }
        
        return [
            'error' => false, 
            'msg' => [
                "labels" => $labels, 
                "data" => $data
            ]
        ];
    }

    public static function bestClient()
    {
        $res = GraphicsService::bestClient();
        
        $count = 0;
        $labels = ["", "", ""];
        $data = ["", "", ""];
        
        // Si hay error, retornar el error
        if ($res->error) {
            return $res;
        }
        
        $msj = $res->msg;
        
        // Verificar si el mensaje es un string (error)
        if (is_string($msj)) {
            throw new CustomException('004');
        }
        
        // Iterar sobre los resultados
        foreach ($msj as $element) {
            if ($count <= 2) {
                if ($count == 0) {
                    $labels[1] = "#1 " . $element->client;
                    $data[1] = $element->compras;
                } else if ($count == 1) {
                    $labels[0] = "#2 " . $element->client;
                    $data[0] = $element->compras;
                } else {
                    $labels[2] = "#3 " . $element->client;
                    $data[2] = $element->compras;
                }
                $count++;
            }
        }
        
        return [
            'error' => false, 
            'msg' => [
                "labels" => $labels, 
                "data" => $data
            ]
        ];
    }

    public static function sales()
    {
        $labels = [
            'Enero' => 1, 
            'Febrero' => 2, 
            'Marzo' => 3, 
            'Abril' => 4, 
            'Mayo' => 5, 
            'Junio' => 6, 
            'Julio' => 7, 
            'Agosto' => 8, 
            'Septiembre' => 9, 
            'Octubre' => 10, 
            'Noviembre' => 11, 
            'Diciembre' => 12
        ];
        
        $labelsAux = [];
        $data = [];
        
        $res = GraphicsService::sales();
        
        // Si hay error, retornar el error
        if ($res->error) {
            return $res;
        }
        
        $msj = $res->msg;
        
        // Verificar si el mensaje es un string (error)
        if (is_string($msj)) {
            throw new CustomException('004');
        }
        
        // Iterar sobre cada mes
        foreach ($labels as $mes => $num) {
            $valor = 0;
            
            // Buscar el valor correspondiente al mes
            foreach ($msj as $element) {
                if ($element->mes == $num) {
                    $valor = (float)$element->total;
                    break;
                }
            }
            
            $labelsAux[] = $mes;
            $data[] = $valor;
        }
        
        return [
            'error' => false, 
            'msg' => [
                "labels" => $labelsAux, 
                "data" => $data
            ]
        ];
    }

    public static function avgTime()
    {
        return GraphicsService::avgTime();
    }
}

?>