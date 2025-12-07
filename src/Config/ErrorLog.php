<?php

/**
 * @file ErrorLog.php
 * @brief Archivo que define la clase ErrorLog para centralizar la configuración del manejo de errores.
 *
 * Este archivo es crucial para establecer un sistema de registro de errores robusto
 * y seguro para la aplicación.
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
 * Clase ErrorLog
 * Clase de utilidad para configurar el manejo de errores de PHP.
 * Su objetivo es asegurar que los errores se registren en un archivo
 * en lugar de mostrarse al usuario final, lo cual es una práctica
 * de seguridad esencial en entornos de producción.
 */
class ErrorLog
{
    /**
     * Activa y configura el registro de errores de la aplicación.
     * Este método se debe llamar al inicio de la aplicación para asegurar
     * que todos los errores sean manejados correctamente.
     */
    public static function activateErrorLog(): void
    {
        // --- 1. CONFIGURAR EL NIVEL DE REPORTE DE ERRORES ---
        // `error_reporting(E_ALL)` le dice a PHP que reporte todos los tipos de errores
        // posibles (errores fatales, advertencias, avisos, etc.).
        // Esto es muy útil en desarrollo para no pasar por alto ningún problema.
        error_reporting(E_ALL);

        // --- 2. EVITAR ERRORES REPETIDOS ---
        // `ini_set('ignore_repeat_errors', 'true')` evita que el mismo error,
        // originado en el mismo archivo y línea, se escriba una y otra vez en el log.
        // Esto mantiene el archivo de log mucho más limpio y legible.
        ini_set('ignore_repeat_errors', 'true');

        // --- 3. OCULTAR ERRORES AL USUARIO (¡MUY IMPORTANTE!) ---
        // `ini_set('display_errors', 'false')` es una configuración de seguridad CRÍTICA.
        // Desactiva la visualización de errores en la página. Si algo falla, el usuario
        // no verá un mensaje de error técnico que podría revelar información sensible
        // sobre la estructura de nuestro código o base de datos.
        ini_set('display_errors', 'false');

        // --- 4. HABILITAR EL REGISTRO DE ERRORES EN ARCHIVO ---
        // `ini_set('log_errors', 'true')` le dice a PHP que, aunque no muestre los
        // errores, sí debe escribirlos en un archivo de log.
        ini_set('log_errors', 'true');

        // --- 5. ESPECIFICAR LA RUTA DEL ARCHIVO DE LOG ---
        // `ini_set('error_log', ...)` define dónde se guardará el archivo de log.
        // `dirname(__DIR__, 2)` es una forma segura de obtener la ruta raíz del proyecto.
        // - `__DIR__` es el directorio de este archivo (`.../api/src/Config`).
        // - `dirname(__DIR__, 2)` sube dos niveles, llegando a `.../api`.
        // La ruta final será algo como: `C:/xampp/htdocs/api/logs/php_error.log`.
        ini_set('error_log', dirname(__DIR__, 2) . '/logs/php_error.log');

        // --- 6. CONVERTIR ERRORES EN EXCEPCIONES ---
        // `set_error_handler()` nos permite tomar el control total sobre cómo se manejan
        // los errores de PHP.
        // Aquí, definimos una función que se ejecutará cada vez que ocurra un error.
        // Esta función toma el error y lo convierte en una `ErrorException`.
        // `set_error_handler()` ahora llama a nuestro método con nombre `handleErrorAsException`.
        // La sintaxis `[self::class, '...']` es la forma moderna y recomendada de
        // referenciar un método estático de la clase actual. Esto mejora la legibilidad
        // y permite reutilizar la lógica si fuera necesario.
        set_error_handler([self::class, 'handleErrorAsException']);
    }

    /**
     * Manejador de errores personalizado que convierte errores de PHP en ErrorException.
     *
     * Este método es el "callback" que se registra con `set_error_handler`. Su única
     * responsabilidad es tomar un error estándar de PHP y relanzarlo como una
     * `ErrorException`, permitiendo que sea capturado por un bloque `try...catch`.
     *
     * @param int    $severity El nivel del error (ej. E_WARNING).
     * @param string $message  El mensaje de error.
     * @param string $file     El archivo donde ocurrió el error.
     * @param int    $line     La línea donde ocurrió el error.
     * @param array|null $context  Un array que apunta a la tabla de símbolos activa en el punto donde ocurrió el error.
     * @throws \ErrorException Siempre lanza una ErrorException si el error no está suprimido.
     */
    private static function handleErrorAsException(int $severity, string $message, string $file, int $line, ?array $context = null): void
    {
        // Esta comprobación respeta el operador `@` para suprimir errores.
        if (!(error_reporting() & $severity)) {
            return;
        }

        $fullMessage = $message;
        // Si el contexto existe y no está vacío, lo formateamos y lo añadimos al mensaje
        // de la excepción. Esto es extremadamente útil para la depuración, ya que nos
        // permite ver el valor de todas las variables locales en el momento del error.
        if (!empty($context)) {
            // `var_export` crea una representación de string que es clara y legible.
            $contextString = var_export($context, true);
            $fullMessage .= "\n\n--- CONTEXTO DEL ERROR (VARIABLES LOCALES) ---\n" . $contextString;
        }

        // Lanzamos la excepción con el mensaje original enriquecido con el contexto.
        // Ahora, cuando captures esta excepción, su método getMessage() devolverá toda esta información.
        throw new \ErrorException($fullMessage, 0, $severity, $file, $line);
    }
}
