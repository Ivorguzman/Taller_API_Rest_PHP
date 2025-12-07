<?php

/**
 * @file
 * Este archivo, auth.php, se encarga de la autenticación y la generación de JSON Web Tokens (JWT).
 * Utiliza la clase Security para crear un token JWT basado en una clave secreta y un ID de usuario.
 *
 * El flujo principal de este archivo es:
 * 1. Habilitar el modo estricto de tipos para mayor seguridad en el código.
 * 2. Importar la clase Security que contiene los métodos para la autenticación.
 * 3. Generar un token JWT utilizando un ID de usuario de ejemplo (en este caso, 1).
 * 4. Imprimir el token JWT generado en la respuesta HTTP.
 *
 * Además, este archivo contiene una sección de código comentado que sirve como un banco de pruebas
 * para verificar la funcionalidad de la clase Security, como la creación y verificación de contraseñas
 * y la carga de variables de entorno para obtener la clave secreta.
 */

// La declaración `declare(strict_types=1);` es una directiva de PHP que debe ser la primera
// instrucción en un archivo.
declare(strict_types=1);

// La palabra clave `use` se utiliza para importar clases de otros `namespaces`.
use App\Config\Security;

// Importar la clase de conexión a la base de datos.
use App\DB\ConnectionDB;

// Establecer la conexión a la base de datos.
ConnectionDB::getConnection();

// La instrucción `echo` se utiliza en PHP para imprimir una o más cadenas de texto en la salida.
echo(

    json_encode(Security::secretKey()) . "\n" .
    // Llamamos al método estático `createTokenJwt` para crear un JSON Web Token.
    // El primer argumento es la clave secreta para firmar el token.
    // El segundo argumento es el "payload" del token, con los datos del usuario.
    Security::createTokenJwt(
        Security::secretKey(),
        ['user_id' => 1]
    )
);
