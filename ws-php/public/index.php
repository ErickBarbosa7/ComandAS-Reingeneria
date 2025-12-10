<?php
// 1. SILENCIAR ERRORES VISUALES (CRÍTICO PARA ANGULAR)
// Esto evita que los "Warnings" rompan el JSON que recibe el Frontend
error_reporting(0); 
ini_set('display_errors', 0);

// 2. CARGA DE DEPENDENCIAS
require_once '../vendor/autoload.php'; 
use Router\Router;

// 3. CORS Y HEADERS (Siempre JSON)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, simple");

// 4. MANEJO DE PRE-FLIGHT (OPTIONS)
// Si el navegador pregunta permisos, respondemos OK y cortamos aquí.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// 5. PROCESAMIENTO PRINCIPAL
try {
    $HEADERS = getallheaders();
    
    // Obtenemos la ruta limpia
    $requestUri = rute(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $httpMethod = $_SERVER['REQUEST_METHOD'];

    // Ejecutar Router
    Router::handle($httpMethod, $requestUri, $HEADERS);

} catch (Throwable $e) {
    // Si hay error fatal, devolvemos JSON válido
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "msg" => "Error Fatal: " . $e->getMessage()
    ]);
}

// 6. FUNCIÓN DE LIMPIEZA DE URL
function rute($url) {
    $url = trim($url, '/');
    $parts = explode('/', $url);
    
    // Si la URL empieza con "public" (compatibilidad), lo quitamos
    if (isset($parts[0]) && $parts[0] === 'public') {
        array_shift($parts);
    }
    
    return implode('/', $parts); 
}
?>