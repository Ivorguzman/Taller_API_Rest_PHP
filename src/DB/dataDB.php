<?php

/**
 * DECLARACIÓN DE TIPOS ESTRICTOS
 * 
 * `declare(strict_types=1);` es una directiva que debe ser la primera instrucción en un archivo PHP.
 * Habilita el modo estricto para la verificación de tipos de datos.
 * En modo estricto, PHP exigirá que los tipos de datos de los valores coincidan exactamente
 * con los tipos declarados en los parámetros de las funciones y en los valores de retorno.
 * Por ejemplo, si una función espera un `int`, pasarle un string como "5" causará un error.
 * Esto ayuda a escribir un código más robusto, predecible y libre de errores de tipo.
 */
declare(strict_types=1);

/**
 * ESPACIO DE NOMBRES (NAMESPACE)
 * 
 * `namespace App\DB;` define un "espacio de nombres" para el código en este archivo.
 * Los espacios de nombres son un sistema para organizar el código en grupos lógicos y jerárquicos,
 * similar a cómo se organizan los archivos en carpetas.
 * Su principal objetivo es evitar colisiones de nombres, permitiendo que existan múltiples clases, funciones o constantes con el mismo nombre en diferentes espacios de nombres sin entrar en conflicto.
 * Aquí, estamos diciendo que todo el código de este archivo pertenece al sub-espacio `DB` dentro del espacio `App`.
 */
namespace App\DB;

/**
 * IMPORTACIÓN DE CLASES (USE)
 * 
 * `use Dotenv\Dotenv;` importa la clase `Dotenv` del paquete `vlucas/phpdotenv`.
 * La palabra clave `use` nos permite crear un alias o atajo para una clase, de modo que podamos
 * referirnos a ella simplemente como `Dotenv` en lugar de su nombre completamente cualificado (`\Dotenv\Dotenv`).
 * Esta clase es una herramienta fundamental para cargar variables de entorno desde un archivo `.env`.
 * Las variables de entorno se usan para almacenar configuración sensible (como credenciales de base de datos,
 * claves de API, etc.) fuera del código fuente, lo cual es una práctica de seguridad recomendada.
 */
use Dotenv\Dotenv;


use App\DB\ConnectionDB;

// --- CARGA DE VARIABLES DE ENTORNO ---

/**
 * CREACIÓN DE LA INSTANCIA DE DOTENV
 * 
 * `Dotenv::createImmutable(dirname(__DIR__, 2))` crea una instancia de la clase Dotenv.
 * - `dirname(__DIR__, 2)`: Esta es una forma de navegar por el árbol de directorios.
 *   - `__DIR__` es una constante mágica de PHP que devuelve la ruta del directorio del archivo actual (ej: `C:\xampp\htdocs\api\src\DB`).
 *   - `dirname(__DIR__)` subiría un nivel (a `C:\xampp\htdocs\api\src`).
 *   - El segundo argumento `2` en `dirname` le dice que suba dos niveles desde `__DIR__`.
 *     Por lo tanto, apunta al directorio raíz del proyecto (`C:\xampp\htdocs\api`), que es donde se espera que esté el archivo `.env`.
 * - `createImmutable()`: Crea una instancia "inmutable" de Dotenv. Esto significa que las variables de entorno
 *   cargadas no pueden ser sobrescritas por otras variables ya existentes en el sistema (como las del servidor web).
 *   Es una forma más segura y predecible de manejar la configuración.
 */
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));

/**
 * CARGA DEL ARCHIVO .ENV
 * 
 * `$dotenv->load();` lee el archivo `.env` del directorio especificado anteriormente,
 * procesa las variables definidas en él y las carga en el superglobal `$_ENV` de PHP.
 * Después de esta línea, podemos acceder a las variables como `$_ENV['NOMBRE_DE_LA_VARIABLE']`.
 */
$dotenv->load();

/**
 * RECOPILACIÓN DE DATOS DE CONEXIÓN
 * 
 * Se crea un array asociativo `$data` para almacenar las credenciales de la base de datos.
 * Cada valor se obtiene del superglobal `$_ENV`, que fue poblado por Dotenv.
 * Esto centraliza la configuración de la base de datos en un solo lugar dentro del script.
 */
$data = [
    'DB_USERNAME' => $_ENV['DB_USERNAME'], // Nombre de usuario para la base de datos.
    'DB_PASSWORD' => $_ENV['DB_PASSWORD'], // Contraseña para la base de datos.
    'DB_DATABASE' => $_ENV['DB_DATABASE'], // Nombre de la base de datos a la que conectar.
    'DB_HOST'     => $_ENV['DB_HOST'],     // Dirección del servidor de la base de datos (ej: 'localhost' o una IP).
    'DB_PORT'     => $_ENV['DB_PORT'],     // Puerto en el que el servidor de la base de datos está escuchando (ej: 3306 para MySQL).
];

/**
 * CONSTRUCCIÓN DEL DSN (Data Source Name)
 * 
 * El DSN es una cadena de texto con un formato específico que le dice al driver de la base de datos
 * (en este caso, PDO para MySQL) cómo conectarse.
 * - `mysql:host=...`: Especifica el driver (mysql) y el host.
 * - `dbname=...`: El nombre de la base de datos.
 * - `port=...`: El puerto de conexión.
 * - `charset=utf8mb4`: Es muy importante para asegurar que la conexión maneje correctamente
 *   un amplio rango de caracteres, incluyendo emojis y símbolos especiales.
 */
$host = 'mysql:host=' . $data['DB_HOST'] . ';dbname=' . $data['DB_DATABASE'] . ';port=' . $data['DB_PORT'] . ';charset=utf8mb4';

/**
 * INTENTO DE CONEXIÓN A LA BASE DE DATOS
 * 
 * `ConnectionDB::from(...)` parece ser una llamada a un método estático `from` en una clase `ConnectionDB`.
 * Este es un patrón de diseño común (Factory Method) donde en lugar de usar `new ConnectionDB()`,
 * se llama a un método estático que se encarga de crear y configurar la instancia de la conexión.
 * 
 * IMPORTANTE: Este script asume que la clase `ConnectionDB` ya ha sido cargada, probablemente
 * a través del autoloader de Composer. Si no, esta línea producirá un error fatal.
 * 
 * @param string $host El DSN construido anteriormente.
 * @param string $data['DB_USERNAME'] El nombre de usuario.
 * @param string $data['DB_PASSWORD'] La contraseña.
 */
ConnectionDB::from($host, $data['DB_USERNAME'], $data['DB_PASSWORD']);


/**
 * SALIDA DE DEPURACIÓN (DEBUG)
 * 
 * `echo $host;` imprime la cadena DSN en la pantalla.
 * Esto es útil durante el desarrollo para verificar que la cadena de conexión se está
 * construyendo correctamente con los datos del archivo `.env`.
 * En un entorno de producción, esta línea debería ser eliminada para no enviar
 * información de configuración sensible al cliente.
 */
echo " Contenido de \$host = $host || "  ;
