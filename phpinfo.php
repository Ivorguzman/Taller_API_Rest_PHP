<?php
/**
 * =====================================================================================
 * SCRIPT DE DIAGNÓSTICO DE CONFIGURACIÓN DE PHP
 * =====================================================================================
 *
 * Propósito:
 * ----------
 * Este script utiliza la función `phpinfo()`, una herramienta de diagnóstico invaluable
 * para desarrolladores. Muestra una gran cantidad de información sobre la configuración
 * actual de PHP en el servidor.
 *
 * ¿Qué información muestra?
 * -------------------------
 * - Versión de PHP.
 * - Variables de entorno y de servidor.
 * - Configuración del 'core' de PHP (php.ini).
 * - Cabeceras HTTP.
 * - Extensiones de PHP cargadas (como MySQLi, PDO, GD, Imagick, etc.).
 * - Variables de sesión.
 * - Y mucho más.
 *
 * Uso en Desarrollo:
 * -----------------
 * Es extremadamente útil durante el desarrollo y la depuración para:
 *   - Verificar que una extensión específica está cargada.
 *   - Comprobar el valor de una directiva de configuración (ej. `upload_max_filesize`).
 *   - Entender el entorno en el que se está ejecutando la aplicación.
 *   - Depurar problemas de conexión a bases de datos o de configuración del servidor.
 *
 * ¡ADVERTENCIA DE SEGURIDAD CRÍTICA!:
 * -----------------------------------
 * Este archivo NUNCA debe dejarse en un servidor de producción accesible al público.
 *
 * ¿Por qué es un riesgo de seguridad?
 * ----------------------------------
 * La salida de `phpinfo()` revela información extremadamente sensible sobre el
 * servidor y la configuración de PHP. Un atacante podría usar esta información para:
 *   - Identificar versiones de software con vulnerabilidades conocidas.
 *   - Descubrir la estructura de directorios del servidor (`DOCUMENT_ROOT`).
 *   - Obtener información sobre variables de entorno que podrían contener claves de API
 *     o credenciales de base de datos si no se configuran correctamente.
 *   - Planificar ataques más dirigidos y efectivos contra el sistema.
 *
 * Por lo tanto, utilice este archivo solo para depuración local y asegúrese de que
 * se elimine o se restrinja su acceso antes de desplegar la aplicación.
 */

// Llama a la función phpinfo() para que genere y muestre la página de información.
phpinfo();