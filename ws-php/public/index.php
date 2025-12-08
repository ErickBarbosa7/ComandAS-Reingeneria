<?php
// Incluye el archivo de carga automática de clases desde la carpeta 'vendor'
require_once '../vendor/autoload.php';

// Importa la clase Router desde el namespaces 'Router'
use Router\router;

// Establece los Headers
header("Access-Control-Allow-Origin: *");
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// Obtiene todas los headers de la solicitud actual
$HEADERS = getallheaders();

// Obtiene la URI de la solicitud y el método HTTP utilizado
$requestUri = rute(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$httpMethod = $_SERVER['REQUEST_METHOD'];

// Llama al método estático 'handle' de la clase 'Router' para manejar la solicitud
Router::handle($httpMethod, $requestUri, $HEADERS);

//Función para analizar la URL y extraer parte de ella.
function rute (String $url) {
    // Divide la URL en segmentos usando '/' como separador
    $parts = explode('/', $url);
    
    // Busca el índice del segmento 'public' en la URL
    $publicIndex = array_search('public', $parts);
    
    // Verifica si se encuentra 'public' y si hay un segmento siguiente después de 'public'
    if ($publicIndex !== false && isset($parts[$publicIndex + 1])) {
        // Devuelve el segmento siguiente después de 'public'
        //return $parts[$publicIndex + 1];
        $routeParts = array_slice($parts, $publicIndex + 1); // Obtiene ["auth", "signin"]
        return implode('/', $routeParts);
    } else {
        // Si 'public' no está presente o no hay un segmento siguiente, devuelve una cadena vacía
        return "";
    }
}

?>