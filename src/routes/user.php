<?php

/**
 * @file user.php
 * @brief Archivo de ruta para el recurso "user".
 *
 * --- ¿QUÉ HACE ESTE ARCHIVO? ---
 * Este archivo actúa como el punto de entrada específico para todas las peticiones
 * dirigidas al recurso "/user". Es incluido por el enrutador principal (`index.php`)
 * después de que este haya validado la petición.
 *
 * --- ¿CÓMO FUNCIONA? ---
 * 1.  **Recepción de Variables**: Las variables `$requestMethod`, `$url`, `$controller`
 *     etc., ya existen y están disponibles porque `index.php` las creó antes de
 *     incluir este archivo.
 * 2.  **Obtención del Cuerpo (Body)**: Lee el cuerpo de la petición (para `POST` y `PUT`)
 *     y lo decodifica si es JSON.
 * 3.  **Instanciación del Controlador**: Crea un nuevo objeto `UserController`,
 *     "inyectándole" todo el contexto de la petición (método, ruta, parámetros,
 *     cuerpo y cabeceras).
 *
 * Al instanciar `UserController`, su constructor se ejecuta y pone en marcha
 * toda la lógica para manejar la petición y generar una respuesta.
 */

declare(strict_types=1);

// Importa la clase UserController para poder instanciarla.
use App\Controllers\UserController;

// Obtiene el cuerpo de la petición y lo decodifica si es JSON.
// file_get_contents('php://input') lee el stream de datos crudos del body.
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true); // `true` lo convierte en un array asociativo.

// Obtiene todas las cabeceras de la petición.
$headers = getallheaders();

// Crea una nueva instancia de UserController.
// Le pasamos todas las variables globales que el enrutador principal (`index.php`)
// ya ha preparado para nosotros.
// Esto activa el constructor de UserController, que a su vez llama al método `dispatch()`
// para manejar la petición GET, POST, PUT o DELETE.
new UserController(
    method: $requestMethod, // El verbo HTTP ('GET', 'POST', etc.).
    route: $controller,     // La ruta base ('user').
    params: $url,           // Los segmentos de la URL (['user', '123']).
    data: $data,            // El cuerpo de la petición decodificado.
    headers: $headers       // Las cabeceras de la petición.
);
