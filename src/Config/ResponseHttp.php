<?php

/**
 * @file ResponseHttp.php
 * @brief Archivo que define la clase ResponseHttp para estandarizar las respuestas de la API.
 *
 * Este archivo contiene la clase ResponseHttp, que proporciona un conjunto de métodos
 * estáticos para generar respuestas HTTP consistentes y predecibles.
 */

// Activa el modo estricto de tipos en PHP.
// Esto asegura que los tipos de datos de los argumentos y los valores de retorno
// de las funciones coincidan exactamente con los declarados, previniendo errores.
declare(strict_types=1);

// Define un "espacio de nombres" para esta clase. Los espacios de nombres son como
// carpetas para tu código: organizan las clases y evitan que dos clases con el mismo
// nombre choquen entre sí.

namespace App\Config;

/**
 * Clase ResponseHttp
 * Agrupa un conjunto de métodos estáticos para generar respuestas HTTP.
 * Centraliza la lógica de las respuestas para mantener el código limpio y reutilizable.
 */
class ResponseHttp
{
    /**
     * @var array|null $data Contenedor para los datos de la respuesta.
     *
     * - `private`: Solo accesible desde dentro de esta clase.
     * - `static`: Pertenece a la clase, no a una instancia. Su valor se comparte
     *   entre todas las llamadas a los métodos de esta clase.
     * - `?array`: El tipo puede ser `array` o `null`.
     *
     * Guarda los datos que se quieren enviar en el cuerpo de la respuesta.
     */
    private static ?array $data = null;

    /**
     * Método privado que construye y prepara la respuesta HTTP.
     * Es el método central que utilizan todos los demás métodos públicos de la clase.
     *
     * @param int    $code      El código de estado HTTP (ej. 200, 404, 500).
     * @param string $status    Un texto descriptivo del estado (ej. "Ok", "Not Found").
     * @param string $message   El mensaje específico para el cliente.
     * @param bool   $isError   Indica si la respuesta es un error para registrarla en el log.
     * @return array            Un array asociativo listo para ser convertido a JSON.
     */
    private static function send(int $code, string $status, string $message, bool $isError = false): array
    {
        // --- 1. REGISTRO DE ERRORES ---
        // Si el método fue llamado con `$isError = true`, se registra el mensaje
        // en el log de errores del servidor. Esto es crucial para la depuración.
        if ($isError) {
            error_log('API Response Error -> Status: ' . $status . ' | Message: ' . $message);
        }

        // --- 2. ESTABLECER CÓDIGO DE ESTADO HTTP ---
        // `http_response_code()` es una función de PHP que envía el código de estado
        // HTTP al cliente (navegador, app, etc.).
        http_response_code($code);

        // --- 3. CONSTRUCCIÓN DEL CUERPO DE LA RESPUESTA ---
        // Se crea un array asociativo que será la base de la respuesta JSON.
        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        // --- 4. AÑADIR DATOS ADICIONALES ---
        // Si se han proporcionado datos a través del método `data()`, se añaden
        // al array de la respuesta.
        if (self::$data !== null) {
            $response['data'] = self::$data;
        }

        // --- 5. LIMPIEZA Y RETORNO ---
        // Se reinicia la variable estática `$data` a `null` para evitar que
        // los datos de una petición se filtren en la siguiente.
        self::$data = null;

        // Se devuelve el array completo. El controlador se encargará de convertirlo a JSON.
        return $response;
    }

    /**
     * Adjunta un payload de datos a la próxima respuesta.
     *
     * @param array $data Los datos a incluir en la respuesta.
     * @return self Devuelve la propia clase para permitir encadenamiento de métodos.
     */
    public static function data(array $data): self
    {
        self::$data = $data;
        return new self();
    }

    /**
     * Respuesta 200 (OK).
     * Para peticiones correctas y exitosas.
     */
    public static function status200(string $message): array
    {
        return self::send(200, 'Ok', $message);
    }

    /**
     * Respuesta 201 (Created).
     * Para peticiones que han creado un nuevo recurso en el servidor.
     */
    public static function status201(string $message): array
    {
        return self::send(201, 'Created', $message);
    }

    /**
     * Respuesta 400 (Bad Request).
     * La petición del cliente está mal formada o es inválida.
     */
    public static function status400(string $message): array
    {
        return self::send(400, 'Bad Request', $message, true);
    }

    /**
     * Respuesta 401 (Unauthorized).
     * El cliente necesita autenticarse para acceder al recurso.
     */
    public static function status401(string $message): array
    {
        return self::send(401, 'Unauthorized', $message, true);
    }

    /**
     * Respuesta 403 (Forbidden).
     * El cliente está autenticado, pero no tiene permisos para esta acción.
     */
    public static function status403(string $message): array
    {
        return self::send(403, 'Forbidden', $message, true);
    }

    /**
     * Respuesta 404 (Not Found).
     * El recurso o endpoint solicitado no existe.
     */
    public static function status404(string $message): array
    {
        return self::send(404, 'Not Found', $message, true);
    }

    /**
     * Respuesta 405 (Method Not Allowed).
     * Se usó un verbo HTTP (GET, POST) no permitido para este endpoint.
     */
    public static function status405(string $message): array
    {
        return self::send(405, 'Method Not Allowed', $message, true);
    }

    /**
     * Respuesta 422 (Unprocessable Entity).
     * La petición está bien formada, pero contiene errores semánticos (ej. errores de validación).
     */
    public static function status422(string $message): array
    {
        return self::send(422, 'Unprocessable Entity', $message, true);
    }

    /**
     * Respuesta 500 (Internal Server Error).
     * Error genérico del servidor. Algo falló de nuestro lado.
     */
    public static function status500(string $message): array
    {
        return self::send(500, 'Internal Server Error', $message, true);
    }
}
