<?php

namespace App\controllers;

use App\models\GraphicsModel;
use Config\utils\CustomException as exc;

class GraphicsController
{
    public function totalSales($params = null)
    {
        try {
            $result = GraphicsModel::totalSales();
            echo json_encode($result);
        } catch (exc $e) {
            echo json_encode($e->GetOptions());
        } catch (\Exception $e) {
            echo json_encode([
                'error' => true,
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function bestSeller($params = null)
    {
        try {
            // Validación directa
            if (!$params || !isset($params->mes) || $params->mes === '' || $params->mes === null) {
                throw new exc("007");
            }
            
            $mes = $params->mes;
            
            $result = GraphicsModel::bestSeller($mes);
            echo json_encode($result);
            
        } catch (exc $e) {
            echo json_encode($e->GetOptions());
        } catch (\Exception $e) {
            echo json_encode([
                'error' => true,
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function bestClient($params = null)
    {
        try {
            $result = GraphicsModel::bestClient();
            echo json_encode($result);
        } catch (exc $e) {
            echo json_encode($e->GetOptions());
        } catch (\Exception $e) {
            echo json_encode([
                'error' => true,
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function sales($params = null)
    {
        try {
            $result = GraphicsModel::sales();
            echo json_encode($result);
        } catch (exc $e) {
            echo json_encode($e->GetOptions());
        } catch (\Exception $e) {
            echo json_encode([
                'error' => true,
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function avgTime($params = null)
    {
        try {
            $result = GraphicsModel::avgTime();
            echo json_encode($result);
        } catch (exc $e) {
            echo json_encode($e->GetOptions());
        } catch (\Exception $e) {
            echo json_encode([
                'error' => true,
                'msg' => $e->getMessage()
            ]);
        }
    }
}

?>