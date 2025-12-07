<?php

/**
 * ==================================================================================
 * TEST DE CONEXIÓN A LA BASE DE DATOS (Test.php)
 * ==================================================================================
 *
 * @author Ivor
 * @date 2025-11-15
 *
 *
 * ¿QUÉ HACE ESTE ARCHIVO?
 * ----------------------
 * Este script tiene un único propósito: verificar si la aplicación puede conectarse
 * exitosamente a la base de datos. Es una herramienta de diagnóstico fundamental
 * para asegurar que la configuración de la base de datos (credenciales, host, etc.)
 * es correcta y que el servidor de base de datos está operativo.
 *
 *
 * ¿CÓMO LO HACE?
 * ----------------
 * 1.  **Configuración Inicial**: Establece que la respuesta será en formato JSON y
 *     activa un manejador de errores personalizado para no exponer información sensible.
 * 2.  **Carga de Dependencias**: Incluye el autoloader de Composer y las clases
 *     necesarias para la conexión (ConnectionDB), el manejo de errores (ErrorLog)
 *     y las respuestas HTTP (ResponseHttp).
 * 3.  **Intento de Conexión**: Utiliza un bloque `try-catch` para intentar obtener
 *     una conexión a la base de datos a través de la clase `ConnectionDB`.
 * 4.  **Verificación**: Si la conexión es exitosa, ejecuta una consulta simple (`SELECT 1`)
 *     que no tiene otro fin que confirmar que el enlace con la BD es funcional.
 * 5.  **Respuesta al Cliente**:
 *     - Si todo va bien, envía una respuesta JSON con estado HTTP 200 (OK).
 *     - Si algo falla (ej. credenciales incorrectas, servidor de BD caído), captura
 *       la excepción `PDOException`.
 * 6.  **Manejo de Errores**: En caso de fallo, registra el error técnico detallado
 *     en un archivo de log (privado) y envía al cliente una respuesta JSON con
 *     estado HTTP 500 (Error Interno del Servidor) y un mensaje genérico.
 *
 *
 * ¿POR QUÉ SE HACE ASÍ?
 * ----------------------
 * - **Seguridad**: Al usar `ErrorLog::activateErrorLog()` y el bloque `try-catch`,
 *   se evita que los mensajes de error de PHP o PDO se muestren directamente al
 *   usuario final. Exponer estos detalles podría revelar vulnerabilidades, como
 *   nombres de usuario, contraseñas o rutas de archivos.
 * - **Abstracción**: La lógica de la conexión está encapsulada en la clase `ConnectionDB`.
 *   Este script no necesita saber los detalles (host, usuario, contraseña); solo
 *   pide la conexión. Esto facilita el mantenimiento, ya que si las credenciales
 *an, solo se modifican en un lugar (`dataDB.php`).
 * - **Respuestas Estandarizadas**: El uso de `ResponseHttp` asegura que todas las
 *   respuestas de la API (tanto de éxito como de error) sigan una estructura
 *   consistente (ej. `{"status": "...", "message": "..."}`), lo que facilita
 *   el trabajo para los desarrolladores del frontend.
 * - **Diagnóstico Rápido**: Permite a los desarrolladores verificar rápidamente la
 *   salud de la conexión a la base de datos sin necesidad de probar un endpoint
 *   complejo de la API.
 */

// Directiva para forzar el tipado estricto en todo el script.
// ¿POR QUÉ? Ayuda a prevenir errores sutiles relacionados con tipos de datos
// (ej. pasar un string donde se espera un número). Es una buena práctica en PHP moderno.
declare(strict_types=1);

// --- 1. CONFIGURACIÓN DE LA RESPUESTA HTTP ---
// ¿QUÉ? Informa al cliente (navegador, Postman, etc.) que la respuesta de este script
// será en formato JSON.
// ¿CÓMO? A través de la función `header()` de PHP.
// ¿POR QUÉ? Es el estándar para las APIs REST. Permite que los clientes interpreten
// correctamente los datos recibidos.
header("Content-Type: application/json");

// --- 2. CARGA DE ARCHIVOS Y DEPENDENCIAS ---
// ¿QUÉ? Carga el autoloader de Composer y el archivo de configuración de la base de datos.
// ¿CÓMO? `require_once` detiene la ejecución si los archivos no se encuentran, lo cual es
// deseable porque la aplicación no puede funcionar sin ellos. `dirname(__DIR__)` construye
// una ruta absoluta desde la ubicación actual del archivo, haciéndolo más robusto.
// ¿POR QUÉ?
// - `vendor/autoload.php`: Es el mecanismo estándar de Composer para cargar automáticamente
//   las clases (`use App\DB\ConnectionDB`, etc.) sin necesidad de `require` manuales.
// - `src/DB/dataDB.php`: Centraliza las credenciales y constantes de la base de datos.
require_once dirname(__DIR__) . "/vendor/autoload.php";
require_once dirname(__DIR__) . "/src/DB/dataDB.php";

// ¿QUÉ? Importa las clases que se utilizarán en este script.
// ¿POR QUÉ? Permite usar nombres de clase más cortos (ej. `ConnectionDB` en lugar de
// `App\DB\ConnectionDB`) y mejora la legibilidad del código.
use App\DB\ConnectionDB;
use App\Config\ErrorLog;
use App\Config\ResponseHttp;

// --- 3. ACTIVACIÓN DEL MANEJADOR DE ERRORES PERSONALIZADO ---
// ¿QUÉ? Redirige todos los errores y advertencias de PHP para que sean gestionados
// por nuestra clase `ErrorLog`.
// ¿CÓMO? La función `activateErrorLog` probablemente usa `set_error_handler()` de PHP.
// ¿POR QUÉ? Es una medida de seguridad CRÍTICA. A partir de este punto, los errores
// técnicos no se mostrarán al usuario, sino que se guardarán en un archivo de log
// (`Logs/php_error.log`), evitando la exposición de información sensible.
ErrorLog::activateErrorLog();

// --- 4. LÓGICA PRINCIPAL: VERIFICACIÓN DE CONEXIÓN ---
// ¿QUÉ? Intenta establecer una conexión con la base de datos y ejecutar una consulta simple.
// ¿CÓMO? Se encapsula la lógica en un bloque `try-catch` para manejar posibles fallos.
// ¿POR QUÉ? La conexión a recursos externos (como una base de datos) es una operación
// que puede fallar por múltiples razones (red, credenciales, etc.). El `try-catch`
// es el mecanismo estándar para gestionar estos errores de forma controlada.
try {
    // 4.1. Obtener la instancia de la conexión PDO.
    // ¿QUÉ? Solicita el objeto de conexión a la base de datos.
    // ¿CÓMO? Llama al método estático `getConnection()` de nuestra clase `ConnectionDB`.
    // Esta clase es responsable de gestionar la creación del objeto PDO.
    $pdo = ConnectionDB::getConnection();

    // 4.2. Realizar una consulta de prueba.
    // ¿QUÉ? Ejecuta una consulta SQL que no hace nada más que devolver '1'.
    // ¿CÓMO? Usando el método `query()` del objeto PDO.
    // ¿POR QUÉ? No basta con conectarse. Esta consulta confirma que la conexión está
    // realmente viva y es capaz de procesar comandos SQL. Es una verificación completa.
    $pdo->query("SELECT 1");

    // 4.3. Enviar respuesta de éxito.
    // ¿QUÉ? Si las dos líneas anteriores se ejecutaron sin errores, se envía una
    // respuesta de éxito al cliente.
    // ¿CÓMO? Usando nuestra clase `ResponseHttp` para generar un objeto de respuesta
    // estándar con código 200, que luego se convierte a formato JSON.
    echo json_encode(ResponseHttp::status200("Conexión a la base de datos exitosa."));

} catch (\PDOException $th) {
    // --- 5. MANEJO DE ERRORES DE CONEXIÓN ---
    // Este bloque solo se ejecuta si el `try` falla (ej. la conexión a la BD es rechazada).

    // 5.1. Preparar el mensaje detallado para el log.
    // ¿QUÉ? Se crea un mensaje de error específico que incluye el texto de la excepción.
    // ¿POR QUÉ? Este mensaje es para los desarrolladores. Contiene la información
    // técnica necesaria para diagnosticar el problema (ej. "Access denied for user...").
    $logMessage = "Error de conexión en Test.php: " . $th->getMessage();

    // 5.2. Registrar el error en el archivo de logs.
    // ¿QUÉ? Guarda el mensaje de error detallado en el archivo de log del servidor.
    // ¿CÓMO? Usa la función nativa `error_log()` de PHP. Nuestra configuración en
    // `ErrorLog::activateErrorLog()` asegura que este mensaje se escriba en el
    // archivo `Logs/php_error.log`.
    error_log($logMessage);

    // 5.3. Preparar el mensaje público y genérico para el usuario.
    // ¿QUÉ? Se define un mensaje de error que no revela ningún detalle técnico.
    // ¿CÓMO? Se utiliza la constante `CE_500` (si está definida en `dataDB.php`) o un
    // texto predeterminado.
    // ¿POR QUÉ? Por seguridad. El usuario final no debe ver "Error de SQLSTATE[HY000] [1045]".
    // Solo necesita saber que algo salió mal internamente.
    $publicMessage = defined('CE_500') ? CE_500 : "Error interno del servidor.";

    // 5.4. Enviar una única respuesta de error al cliente.
    // ¿QUÉ? Envía una respuesta HTTP con código 500 (Internal Server Error).
    // ¿CÓMO? Usa `ResponseHttp` para crear la estructura JSON estándar y `json_encode`
    // para la conversión final antes de enviarla con `echo`.
    echo json_encode(ResponseHttp::status500($publicMessage));
}