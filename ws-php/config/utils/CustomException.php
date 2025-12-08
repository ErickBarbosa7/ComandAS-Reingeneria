<?php
    namespace Config\utils;
    
    use Exception;

    //Hereda lo que tiene la liberia de php
    class CustomException extends Exception
    {

    //Opcion predeterminada para mensaje de error.
    private $_options = "unknown_error";

    //Opcion predeterminada para mensaje de error.
    private $_error_code = "000";

    //Constructor de la clase $errorCode Código(s) de error para buscar en el catálogo de mensajes.
    public function __construct(...$errorCode)
    {
        if (!isset($errorCode[0])) return;
        if (!array_key_exists($errorCode[0], self::MESSAGE_CATALOGUE)) return;
        $this->_options = self::MESSAGE_CATALOGUE[$errorCode[0]]; 
        $this->_error_code = $errorCode[0]; 
    }

    // Obtener opciones de mensaje de error. Un array con información sobre el error, incluyendo indicador de error y mensaje
    public function GetOptions() { return ["error"=> true, "msg" => $this->_options, "error_code" => $this->_error_code]; }
    //ultimo error insertado 009
    //Catálogo de mensajes de error.
    const MESSAGE_CATALOGUE = [
        "001" => "incorrect_request_method",
        "002" => "incorrect_class",
        "003" => "method_not_exist",
        "006" => "not_token",
        "007" => "empty_params",
        //Creat Order
        "008" => "incorrect_insert",
        "009" => "incorrect_update",
        // Login
        "004" => "no_user",
        "005" => "invalid_credentials"
    ];
    }
?>