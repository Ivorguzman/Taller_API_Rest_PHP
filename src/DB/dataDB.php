<?php

/**
 * DECLARACIÓN DE TIPOS ESTRICTOS
 *
 * `declare(strict_types=1);` es una directiva que debe ser la primera instrucción en un archivo PHP.
 * Habilita el modo estricto para la verificación de tipos de datos.
 * Esto ayuda a escribir un código más robusto, predecible y libre de errores de tipo.
 */
declare(strict_types=1);

/**
 * ESPACIO DE NOMBRES (NAMESPACE)
 *
 * `namespace App\DB;` define un "espacio de nombres" para el código en este archivo,
 * evitando colisiones de nombres y organizando el código de manera lógica.
 */

namespace App\DB;

/**
 * IMPORTACIÓN DE CLASES (USE)
 *
 * `use Dotenv\Dotenv;` importa la clase `Dotenv` para cargar variables de entorno
 * desde un archivo `.env`, una práctica recomendada para manejar configuraciones sensibles.
 */
use Dotenv\Dotenv;

// --- CARGA DE VARIABLES DE ENTORNO ---

/**
 * CREACIÓN Y CARGA DE LA INSTANCIA DE DOTENV
 *
 * Se crea una instancia inmutable de Dotenv apuntando al directorio raíz del proyecto
 * (dos niveles por encima del directorio actual `__DIR__`).
 * `load()` lee el archivo `.env` y carga sus variables en el superglobal `$_ENV`.
 */
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

// --- DEFINICIÓN DE CONSTANTES DE CONEXIÓN ---

/**
 * DEFINICIÓN DE CONSTANTES GLOBALES PARA LA CONEXIÓN A LA BASE DE DATOS
 *
 * Estas constantes son utilizadas por la clase `ConnectionDB` para establecer la conexión PDO.
 * Se definen aquí para centralizar toda la configuración de la base de datos en un solo lugar.
 * Los valores se obtienen de las variables de entorno cargadas previamente desde el archivo `.env`.
 *
 * @const string HOST         El servidor de la base de datos (e.g., 'localhost', '127.0.0.1').
 * @const string DB_NAME      El nombre de la base de datos a la que se va a conectar.
 * @const string CHARSET      El juego de caracteres para la conexión (se recomienda 'utf8mb4').
 * @const string USER         El nombre de usuario para acceder a la base de datos.
 * @const string PASSWORD     La contraseña del usuario de la base de datos.
 */
define('HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_DATABASE']);
define('CHARSET', 'utf8mb4');
define('USER', $_ENV['DB_USERNAME']);
define('PASSWORD', $_ENV['DB_PASSWORD']);
