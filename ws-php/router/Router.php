<?php
namespace Router;

// Excepciones personalizadas
use Config\utils\CustomException as exc;
// Operaciones relacionadas con JWT
use Config\Jwt\Jwt;
// Importamos los controladores
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
            "menu/viewIngredients" => [MenuController::class, "viewIngredients", 1],
            "order/viewOrders"     => [OrderController::class, "viewOrders", 1],
            "order/viewOrder"      => [OrderController::class, "viewOrder", 1],
            "order/lastOrder"      => [OrderController::class, "lastOrder", 1],

            "graphics/totalSales"  => [GraphicsController::class, "totalSales", 1],
            "graphics/bestSeller"  => [GraphicsController::class, "bestSeller", 1],
            "graphics/bestClient"  => [GraphicsController::class, "bestClient", 1],
            "graphics/sales"       => [GraphicsController::class, "sales", 1],
            "graphics/avgTime"     => [GraphicsController::class, "avgTime", 1],
            
            "user/viewUser"        => [UserController::class, "viewUser", 1],
            "user/viewUsers"       => [UserController::class, "viewUsers", 1],
        ],
        "POST" => [
            "auth/signin"       => [AuthController::class, "sign_in", 0],
            "auth/signup"       => [AuthController::class, "sign_up", 0],
            "order/createOrder" => [OrderController::class, "createOrder", 1],
            
            // CORRECCIÓN: 'user/create' debe ser POST porque envía datos nuevos
            "user/create"       => [UserController::class, "createUser", 1],
            // Agregamos update aquí también por si tu front lo manda como POST
            "user/update"       => [UserController::class, "updateUser", 1],
        ],
        "PUT" => [
            "order/updateStatus" => [OrderController::class, 'updateStatus', 1],
            "user/updateUser"    => [UserController::class, 'updateUser', 1]
        ],
        "DELETE" => [
            "user/delete"     => [UserController::class, 'deleteUser', 1],
            "user/deleteUser" => [UserController::class, 'deleteUser', 1] // Alias por compatibilidad
        ]
    ];

    // Maneja una solicitud HTTP entrante.
    public static function handle(String $method, String $uri, array $HEADERS)
    {
        try {
            // Verificar si la ruta existe en el método solicitado
            if (!isset(self::$routes[$method][$uri])) {
                throw new exc("003"); // Método inexistente
            }

            // Obtiene la configuración de la ruta
            $routeConfig = self::$routes[$method][$uri];
            $controllerClass = $routeConfig[0];
            $methodName = $routeConfig[1];
            $type_auth = $routeConfig[2];

            // --- VALIDACIÓN DE SEGURIDAD ---
            if ($type_auth === 0) {
                // Validación Simple (Rutas públicas como Login)
                // Se usa el hash fijo que envía Angular
                if (!isset($HEADERS['simple']) || $HEADERS['simple'] !== 'bb1557a2774351913c8557251ec2cbb4') {
                    throw new exc('006');
                }
            } else {
                // Validación con Token (Rutas privadas)
                if (!isset($HEADERS['authorization']) || !Jwt::Check(@$HEADERS['authorization'])) {
                    throw new exc('006');
                }
            }

            // Verifica si la clase del controlador existe
            if (!class_exists($controllerClass)) throw new exc("002");

            // Crea una instancia del controlador
            $controllerInstance = new $controllerClass();

            // Verifica si el método del controlador existe
            if (!method_exists($controllerInstance, $methodName)) throw new exc("003");

            // Obtiene los datos de la solicitud
            $requestData = self::getRequestData($method);

            // Ejecuta el controlador
            return call_user_func([$controllerInstance, $methodName], $requestData);

        } catch (exc $e) {
            echo json_encode($e->GetOptions());
        } catch (\Throwable $th) {
            echo json_encode(["error" => true, "msg" => $th->getMessage(), "error_code" => $th->getCode()]);
        }
    }

    // Obtiene y decodifica los datos de una solicitud HTTP.
    private static function getRequestData(String $REQUEST_METHOD)
    {
        // Solo GET y DELETE buscan datos en la URL.
        if ($REQUEST_METHOD === 'GET' || $REQUEST_METHOD === 'DELETE') {
            $requestData = $_GET['params'] ?? null; 
        } else {
            // POST y PUT leen del cuerpo (Body)
            $requestData = file_get_contents("php://input"); 
        }
        return json_decode($requestData); 
    }
}
?>