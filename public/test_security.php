<?php

/**
 * =====================================================================================
 * SCRIPT DE PRUEBA PARA LA CLASE `Security` Y LA VALIDACIÓN DE JWT
 * =====================================================================================
 *
 * Propósito:
 * ----------
 * Este archivo demuestra cómo utilizar y probar el método `Security::validarTokenJwt()`.
 * Sirve como un ejemplo práctico de cómo proteger un endpoint o una ruta, asegurando
 * que solo las peticiones con un JSON Web Token (JWT) válido puedan acceder.
 *
 * Escenario de Prueba:
 * --------------------
 * 1. Intenta validar un token JWT extraído de las cabeceras de la petición HTTP.
 * 2. Si el token es válido y no ha expirado, devuelve un mensaje de éxito con los
 *    datos del usuario (payload) contenidos en el token.
 * 3. Si el token ha expirado, captura la excepción específica `ExpiredException` y
 *    devuelve un error 401 claro.
 * 4. Si el token falta, tiene un formato incorrecto, o la firma es inválida, captura
 *    la excepción genérica `\Throwable` y devuelve un error 401 con el mensaje
 *    correspondiente.
 *
 * Cómo Probarlo:
 * --------------
 * Para que este script funcione, debes enviar una petición a este archivo desde un
 * cliente HTTP (como Postman) e incluir la cabecera 'Authorization' con un Bearer Token.
 *
 *   - Cabecera: `Authorization`
 *   - Valor: `Bearer <tu_token_jwt_aqui>`
 *
 * Puedes probar con un token válido, uno caducado y sin token para ver los diferentes
 * flujos de ejecución.
 */

// --- 1. CONFIGURACIÓN INICIAL DEL ENTORNO DE PRUEBA ---

// Activa el modo estricto de tipos.
declare(strict_types=1);

// Establece la cabecera para indicar que la salida siempre será JSON en formato UTF-8.
header("Content-Type: application/json; charset=utf-8");

// --- 2. CARGA DE ARCHIVOS Y CLASES ESENCIALES ---

// Carga el autoloader de Composer para acceder a las clases del proyecto y de vendors.
require_once dirname(__DIR__) . "/vendor/autoload.php";
// Carga nuestras constantes de mensajes de estado.
require_once dirname(__DIR__) . "/public/codigosEstado.php";

// Importa las clases necesarias para este script.
use App\Config\Security;     // La clase que contiene la lógica de seguridad.
use App\Config\ResponseHttp; // La clase para generar respuestas HTTP estandarizadas.
use Firebase\JWT\ExpiredException; // La excepción específica para tokens expirados.

// --- 3. EJECUCIÓN DE LA LÓGICA DE VALIDACIÓN ---

// El bloque `try...catch` es el corazón de este patrón de validación.
// Nos permite manejar los diferentes resultados de la validación (éxito, expirado, inválido)
// de una manera limpia y estructurada.
try {
    // --- PASO A: INTENTO DE VALIDACIÓN DEL TOKEN ---

    // `getallheaders()`: Obtiene todas las cabeceras de la petición HTTP actual.
    // `Security::validarTokenJwt()`: A esta función se le pasa la responsabilidad de:
    //   1. Buscar la cabecera 'Authorization'.
    //   2. Extraer el token (el que viene después de "Bearer ").
    //   3. Decodificar y verificar la firma y la fecha de expiración del token.
    //
    // Si todo es correcto, devuelve el "payload" del token (los datos del usuario).
    // Si algo falla, lanza una excepción y la ejecución salta a un bloque `catch`.
    $datosUsuario = Security::validarTokenJwt(getallheaders());

    // --- PASO B: SI LA VALIDACIÓN FUE EXITOSA ---

    // Si el script llega a este punto, la excepción no fue lanzada, por lo tanto, el token es válido.
    // Preparamos una respuesta de éxito (200 OK) para confirmar.
    ResponseHttp::data([
        'message'      => '¡Token validado con éxito!',
        'user_payload' => $datosUsuario, // Incluimos los datos del usuario extraídos del token.
    ]);

    // Enviamos la respuesta de éxito al cliente.
    echo json_encode(ResponseHttp::status200(CE_200));

} catch (ExpiredException $e) {
    // --- CAPTURA DE ERROR ESPECÍFICO: TOKEN EXPIRADO ---

    // Este bloque se ejecuta SOLAMENTE si `validarTokenJwt` lanza `ExpiredException`.
    // Esto nos permite dar al cliente un mensaje más útil que un simple "inválido".
    $response = ResponseHttp::status401("El token ha expirado. Por favor, inicie sesión de nuevo.");
    http_response_code(401); // Nos aseguramos de que el código de estado HTTP sea 401.
    echo json_encode($response);

} catch (\Throwable $e) {
    // --- CAPTURA DE TODOS LOS DEMÁS ERRORES DE VALIDACIÓN ---

    // Este bloque `catch` atrapa cualquier otra excepción (`\Throwable` es la base para todas las excepciones y errores en PHP 7+).
    // Los posibles errores que captura son:
    //  - Falta la cabecera 'Authorization'.
    //  - El token no tiene el formato "Bearer <token>".
    //  - El token está malformado.
    //  - La firma del token es inválida.
    //
    // Usamos el mensaje de la propia excepción (`$e->getMessage()`) porque nuestra clase `Security`
    // ya genera mensajes claros para cada uno de estos casos.
    $response = ResponseHttp::status401($e->getMessage());
    http_response_code(401); // Nos aseguramos de que el código de estado HTTP sea 401.
    echo json_encode($response);
}
