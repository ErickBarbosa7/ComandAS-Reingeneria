<?php

//Se define el nombre del espacio
namespace Router;

// Excepciones personalizadas
use Config\utils\CustomException as exc;
// Operaciones relacionadas con JWT
use Config\Jwt\Jwt;
use App\controllers\AuthController;
use App\controllers\GraphicsController;
use App\controllers\MenuController;
use App\controllers\OrderController;
use App\controllers\UserController;

class Router
{
    // Define un array que contiene las rutas y su configuración
    private static $routes = [
        "GET" => [
            "menu/viewIngredients" =>[MenuController::class, "viewIngredients", 1],
            "order/viewOrders" => [OrderController::class, "viewOrders", 1],
            "order/viewOrder" => [OrderController::class, "viewOrder", 1],
            "order/lastOrder" => [OrderController::class, "lastOrder", 1],

            "graphics/totalSales" => [GraphicsController::class, "totalSales", 1],
            "graphics/bestSeller" => [GraphicsController::class, "bestSeller", 1],
            "graphics/bestClient" => [GraphicsController::class, "bestClient", 1],
            "graphics/sales" => [GraphicsController::class, "sales", 1],
            "graphics/avgTime" => [GraphicsController::class, "avgTime", 1],
            
            "user/viewUser" => [UserController::class, "viewUser", 1],
            "user/viewUsers" => [UserController::class, "viewUsers", 1],
        ],
        "POST" => [
            "auth/signin" => [AuthController::class, "sign_in", 0],
            "auth/signup" => [AuthController::class, "sign_up", 0],
            "order/createOrder" => [OrderController::class, "createOrder", 1],
            
        ],
        "PUT" => [
            "order/updateStatus" => [OrderController::class, 'updateStatus', 1],

            "user/updateUser" => [UserController::class, 'updateUser', 1]
        ],
        "DELETE" => [
            "user/deleteUser" => [UserController::class, 'deleteUser', 1]
        ]
    ];

    // Maneja una solicitud HTTP entrante.
    public static function handle(String $method, String $uri, array $HEADERS)
    {
        try {
            // Obtiene el tipo de autenticación requerido para la ruta solicitada
            $type_auth = self::$routes[$method][$uri][2];

            // Verifica si el tipo de autenticación es nulo
            if (is_null($type_auth)) throw new exc("001"); // Lanza una excepción personalizada

            // Realiza verificaciones de autenticación basadas en los encabezados proporcionados
            if (!$type_auth) {
                // Verifica la existencia y validez del encabezado 'Simpleauthb2b'
                if (!isset($HEADERS['simple']) || $HEADERS['simple'] !== md5('Aqui va tu contraseña (Yo la encripte en MD5)')) throw new exc('006');
            } else {
                // Verifica la existencia y validez del encabezado 'Authorization' utilizando Jwt::Check
                if (!isset($HEADERS['authorization']) || !Jwt::Check(@$HEADERS['authorization'])) throw new exc('006');
            }

            // Obtiene el controlador y el método asociado a la URI solicitada
            $callback = self::$routes[$method][$uri];
            $controllerClass = $callback[0];
            $methodName = $callback[1];

            // Verifica si la clase del controlador existe
            if (!class_exists($controllerClass)) throw new exc("002"); // Lanza una excepción personalizada

            // Crea una instancia del controlador
            $controllerInstance = new $controllerClass();

            // Verifica si el método del controlador existe
            if (!method_exists($controllerInstance, $methodName)) throw new exc("003"); // Lanza una excepción personalizada

            // Obtiene los datos de la solicitud
            $requestData = self::getRequestData($method);

            // Llama al método del controlador con los datos de la solicitud y retorna su resultado
            return call_user_func([$controllerInstance, $methodName], $requestData);
        } catch (exc $e) {
            // Captura la excepción personalizada y devuelve sus opciones en formato JSON
            echo json_encode($e->GetOptions());
        } catch (\Throwable $th) {
            // Captura cualquier otra excepción y devuelve un mensaje de error en formato JSON
            echo json_encode(["error" => true, "msg" => $th->getMessage(), "error_code" => $th->getCode()]);
        }
    }

    //Obtiene y decodifica los datos de una solicitud HTTP.
    /*private static function getRequestData(String $REQUEST_METHOD)
    {
        if ($REQUEST_METHOD === 'GET' || $REQUEST_METHOD === 'PUT' || $REQUEST_METHOD === 'DELETE') {
            $requestData = $_GET['params'] ?? null; // Obtiene los datos de la URL
        } else {
            $requestData = file_get_contents("php://input"); // Obtiene los datos de la solicitud para POST
        }
        return json_decode($requestData); // Decodifica los datos de la solicitud como JSON
    }*/
    //Obtiene y decodifica los datos de una solicitud HTTP.
    private static function getRequestData(String $REQUEST_METHOD)
    {
        // Solo GET y DELETE buscan datos en la URL.
        if ($REQUEST_METHOD === 'GET' || $REQUEST_METHOD === 'DELETE') {
            $requestData = $_GET['params'] ?? null; // Obtiene los datos de la URL
        } else {
            // POST y PUT caen aquí y leen del cuerpo (Body)
            $requestData = file_get_contents("php://input"); 
        }
        return json_decode($requestData); // Decodifica los datos de la solicitud como JSON
    }
}
?>