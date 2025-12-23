<?php

/**
 * @file Index.php
 * @brief Punto de Entrada Único (Front Controller) para toda la API.
 *
 * --- ¿QUÉ HACE ESTE ARCHIVO? ---
 * Este archivo es el corazón y el portero de nuestra API. Todas, absolutamente todas las
 * peticiones de los clientes (navegadores, apps móviles, etc.) llegan aquí primero.
 *
 * --- ¿CÓMO FUNCIONA? (El Patrón Front Controller) ---
 * 1. Carga lo esencial: Prepara las herramientas básicas como el autoloading de Composer.
 * 2. Analiza la Petición: Mira la URL y el método HTTP (GET, POST, etc.) para entender
 *    qué quiere hacer el cliente.
 * 3. Valida la Petición: Actúa como un guardia de seguridad. Revisa si el recurso
 *    solicitado existe y si el método está permitido.
 * 4. Delega el Trabajo: Si la petición es válida, carga el archivo d   e ruta correspondiente
 *    (ej. `src/routes/user.php`) para que maneje la lógica específica.
 * 5. Maneja Errores: Si algo sale mal en cualquier punto, captura el error y devuelve
 *    una respuesta JSON limpia y segura, evitando que la aplicación se rompa.
 */

// Nota:  === Secuencia de arranque ===
// 1.  Se carga el autoloader.
// 2.  Se establece la conexión con la base de datos.
// 3.  Se procesan las rutas.

// --- CONFIGURACIÓN INICIAL ---

// ¿QUÉ?: Activa el modo estricto de tipos en PHP.
// ¿POR QUÉ?: Obliga a PHP a ser riguroso con los tipos de datos (ej. un `int` debe ser un `int`).
// Esto previene errores sutiles y hace el código más predecible y robusto.
declare(strict_types=1);

// ¿QUÉ?: Establece la cabecera `Content-Type` para todas las respuestas.
// ¿POR QUÉ?: Le decimos al cliente (navegador, Postman) que SIEMPRE le vamos a responder
// con formato JSON. Esto es fundamental para que el cliente sepa cómo interpretar la respuesta.
header("Content-Type: application/json");

// --- IMPORTACIÓN DE CLASES (El "Kit de Herramientas") ---

// ¿QUÉ?: Importa las clases que vamos a necesitar en este archivo.
// ¿POR QUÉ?: Para poder usarlas con su nombre corto (ej. `ResponseHttp` en lugar de `\App\Config\ResponseHttp`).
use App\Config\ErrorLog;     // Nuestra clase para manejar y registrar errores.
use App\Config\ResponseHttp; // Nuestra clase para crear respuestas HTTP estandarizadas.

// --- CARGA DE ARCHIVOS ESENCIALES ---

// ¿QUÉ?: Carga el autoloader de Composer.
// ¿CÓMO?: `dirname(__DIR__)` sube un nivel desde `public` a la raíz del proyecto.
// ¿POR QUÉ?: Es como darle a PHP un mapa de todo nuestro proyecto. Gracias a esto, podemos
// usar cualquier clase (nuestra o de terceros) sin necesidad de hacer `require` manuales.
require_once dirname(__DIR__) . "/vendor/autoload.php";

// ¿QUÉ?: Carga la configuración de la base de datos y establece la conexión.
// ¿POR QUÉ?: Para que la conexión a la base de datos esté disponible para todas las rutas.
require_once dirname(__DIR__) . "/src/DB/dataDB.php";

// ¿QUÉ?: Carga nuestras constantes personalizadas para los mensajes de estado.
// ¿POR QUÉ?: Para tener mensajes consistentes en toda la aplicación (ej. `CE_404`).
require_once dirname(__DIR__) . "/public/codigosEstado.php";

// --- INICIALIZACIÓN DEL MANEJO DE ERRORES ---

// ¿QUÉ?: Activa nuestro manejador de errores personalizado.
// ¿POR QUÉ?: A partir de esta línea, PHP ya no mostrará los errores directamente al usuario.
// En su lugar, los registrará en un archivo de log (`Logs/php_error.log`), lo cual es
// una práctica de seguridad esencial para no exponer detalles internos de la aplicación.
ErrorLog::activateErrorLog();

// --- EL CORAZÓN DEL ENRUTADOR (EL "SALVAVIDAS" GLOBAL) ---

// ¿QUÉ?: Envuelve toda la lógica principal en un bloque `try...catch`.
// ¿POR QUÉ?: Actúa como una reds de seguridad. Si cualquier cosa falla inesperadamente
// dentro del `try` (un error de base de datos, una excepción no capturada), la ejecución
// salta inmediatamente al bloque `catch`. Esto evita que la aplicación se rompa y nos
// permite enviar una respuesta de error 500 controlada y amigable.
try {
    // --- 1. EL "MENÚ" DE LA API (TABLA DE ENRUTAMIENTO) ---
    // ¿QUÉ?: Define qué "endpoints" o recursos  están disponibles en nuestra API.
    // ¿CÓMO?: Es un array donde la clave es el nombre del recurso (lo que va en la URL)
    // y el valor es una lista de los métodos HTTP (verbos) que ese recurso acepta.
    // Analogía: Es como el directorio de un centro comercial: "Tienda de Zapatos (user) -> Acepta: Comprar (POST), Ver (GET), Devolver (DELETE)",  Colocar Nueva Mercanciaa (PUT).
    $routes = [
        'user' => ['GET', 'POST', 'PUT', 'DELETE'],
        'auth' => ['POST'],
    ];

    // --- 2. ANÁLISIS DE LA PETICIÓN DEL CLIENTE ---
    // ¿QUÉ?: Extrae las piezas clave de la petición entrante.
    // a) El método HTTP: ¿El cliente quiere LEER (GET), CREAR (POST), ACTUALIZAR (PUT) o BORRAR (DELETE)?
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // b) La URL solicitada: ¿Qué recurso específico quiere el cliente?
    // ¿CÓMO?:
    // - `$_GET['route']`: Obtenemos la parte de la URL que define la ruta.
    // - `filter_var(..., FILTER_SANITIZE_URL)`: Limpia la URL de caracteres extraños o peligrosos.
    // - `rtrim(..., '/')`: Quita la barra final si existe (ej. 'user/' -> 'user').
    // - `explode('/', ...)`: Divide la ruta en segmentos (ej. 'user/123' -> ['user', '123']).
    /* $url = isset($_GET['route']) ? explode('/', filter_var(rtrim($_GET['route'], '/'), FILTER_SANITIZE_URL)) : []; */
    $url = isset($_GET['route']) ? explode('/', filter_var(rtrim($_GET['route'], '/'), FILTER_SANITIZE_URL)) : print_r("Ruta no encontrada");



    //& c) El controlador: Es el primer segmento de la URL, que corresponde a nuestro recurso.
    // El operador `?? null` es una forma segura de asignar `null` si la URL viene vacía.
    $controller = $url[0] ?? null;

    // --- 3. VALIDACIONES (EL "GUARDIA DE SEGURIDAD") ---
    //& Antes de hacer cualquier trabajo, validamos si la petición tiene sentido.

    // Validación A: ¿Se especificó un recurso?
    if ($controller === null) {
        $message = defined('CE_400') ? CE_400 : "Petición incorrecta. No se especificó la ruta.";
        echo json_encode(ResponseHttp::status400($message));
        exit; // Detiene la ejecución.
    }

    // Validación B: ¿El recurso solicitado está en nuestro "array controller y el array routes"?
    if (!array_key_exists($controller, $routes)) {
        $message = defined('CE_404') ? CE_404 : "El recurso solicitado no existe.";
        echo json_encode(ResponseHttp::status404($message));
        exit; // Detiene la ejecución.
    }

    // Validación C: ¿El método HTTP está permitido para este recurso?
    if (!in_array($requestMethod, $routes[$controller], true)) {
        $message = defined('CE_405') ? CE_405 : "Método no permitido para este recurso.";
        echo json_encode(ResponseHttp::status405($message));
        exit; // Detiene la ejecución.
    }

    // --- 4. DELEGACIÓN DE LA TAREA ---
    // Si todas las validaciones pasan, significa que la petición es legítima.

    // ¿QUÉ?: Construye la ruta al archivo PHP que contiene la lógica para ese controlador.
    $routeFile = dirname(__DIR__) . '/src/routes/' . $controller . '.php';

    // ¿QUÉ?: Comprueba si el archivo de la ruta existe y es legible.
    // ¿POR QUÉ?: Es una última comprobación de seguridad y de integridad. Si definimos una
    // ruta en el "menú" `$routes`, el archivo físico DEBE existir.
    if (is_readable($routeFile)) {
        // ¿QUÉ?: Carga y ejecuta el código del archivo de la ruta correspondiente.
        // A partir de aquí, este archivo (Index.php) le pasa el control al archivo de la ruta.
        require $routeFile;
    } else {
        // Si el archivo no existe, es un error de configuración en nuestro servidor, no del cliente.
        $message = defined('CE_500') ? CE_500 : "Error interno del servidor: archivo de ruta no encontrado.";
        echo json_encode(ResponseHttp::status500($message));
        exit;
    }
} catch (\Throwable $th) {
    // --- CAPTURA FINAL DE ERRORES ---
    // ¿QUÉ?: Este bloque se ejecuta si ocurre cualquier tipo de error (`Throwable`) no manejado en el `try`.
    // ¿POR QUÉ?: Es nuestra última línea de defensa.
    // 1. Registra el error real en el log para que el desarrollador pueda investigarlo.
    $errorMessage = $th->getMessage();
    $logMessage = defined('CE_500') ? CE_500 . " Detalles: " . $errorMessage : "Error interno del servidor.";
    error_log($logMessage);
    // (Aquí se podría llamar a ErrorLog para guardar $logMessage)

    // 2. Envía una respuesta genérica y segura al cliente, sin revelar detalles técnicos.
    $publicMessage = defined('CE_500') ? CE_500 : "Error : Comuniquese con el Administrado";
    echo json_encode(ResponseHttp::status500($publicMessage));
}
