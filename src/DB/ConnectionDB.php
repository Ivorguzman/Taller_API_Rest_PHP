<?php

/**
 * ==================================================================================
 * DECLARACIÓN DE TIPOS ESTRICTOS (strict_types)
 * ==================================================================================
 *
 * Habilita el modo estricto de tipos en todo el archivo. Esto asegura que PHP
 * no realice conversiones de tipo implícitas, lo que conduce a un código más
 * predecible y robusto.
 */
declare(strict_types=1);

/**
 * ==================================================================================
 * NAMESPACE (Espacio de Nombres)
 * ==================================================================================
 *
 * Organiza esta clase dentro del espacio de nombres `App\DB`, siguiendo el estándar
 * PSR-4 para la autocarga de clases y una estructura de proyecto limpia.
 */

namespace App\DB;

// Importa la clase PDO del espacio de nombres global para poder referenciarla
// directamente. PDO (PHP Data Objects) es la interfaz estándar en PHP para
// interactuar con bases de datos.
use PDO;
use PDOException; // Importa la clase de excepción de PDO para el type-hinting.

/**
 * ==================================================================================
 * GESTOR DE CONEXIÓN A LA BASE DE DATOS (ConnectionDB)
 * ==================================================================================
 *
 * @author Ivor
 * @date 2025-11-16
 *
 * ARQUITECTURA Y PATRÓN DE DISEÑO: SINGLETON
 * -------------------------------------------
 * Esta clase implementa el **Patrón de Diseño Singleton**. El objetivo de este patrón
 * es garantizar que solo exista **una y solo una instancia** de esta clase en toda
 * la aplicación. Para una conexión a base de datos, esto es crucial por varias
 * razones:
 *
 * 1.  **Eficiencia**: Abrir una conexión a la base de datos es una operación costosa
 *     en términos de recursos y tiempo. Reutilizar una única conexión para todas
 *     las consultas durante una petición HTTP es mucho más eficiente que abrir y
 *     cerrar conexiones constantemente.
 * 2.  **Consistencia**: Asegura que todas las partes de la aplicación trabajen con
 *     la misma conexión, lo cual es importante para la gestión de transacciones.
 * 3.  **Control Centralizado**: Proporciona un único punto de acceso global a la
 *     conexión de la base de datos, facilitando su gestión y configuración.
 *
 * ¿CÓMO SE IMPLEMENTA EL SINGLETON AQUÍ?
 * -------------------------------------
 * - **Propiedad Estática Privada (`$instance`)**: Almacena la única instancia de PDO.
 *   Al ser `static`, su valor persiste a lo largo de toda la petición.
 * - **Constructor Privado (`__construct`)**: Impide que se puedan crear instancias
 *   de `ConnectionDB` desde fuera de la clase con `new ConnectionDB()`.
 * - **Método Estático Público (`getConnection`)**: Es el único punto de acceso
 *   público. Se encarga de crear la instancia la primera vez que se llama y de
 *   devolverla en todas las llamadas posteriores.
 */
class ConnectionDB
{
    /**
     * @var PDO|null La única instancia de la conexión PDO.
     * Es `null` hasta que se establece la primera conexión.
     */
    private static ?PDO $instance = null;

    /**
     * El constructor es privado para prevenir la creación de nuevas instancias
     * desde fuera de la clase, forzando el uso del método `getConnection()`.
     */
    private function __construct()
    {
        // El constructor se deja vacío intencionadamente.
    }

    /**
     * Previene la clonación de la instancia, reforzando el patrón Singleton.
     */
    private function __clone()
    {
        // Vacío para evitar la clonación.
    }

    /**
     * Previene la deserialización de la instancia.
     */
    public function __wakeup(): void
    {
        throw new \Exception("No se puede deserializar una instancia de ConnectionDB.");
    }

    /**
     * Método de acceso global a la instancia de la conexión PDO.
     *
     * Este es el corazón del patrón Singleton. La primera vez que se llama, crea
     * la conexión a la base de datos. En las llamadas subsecuentes, simplemente
     * devuelve la conexión ya existente.
     *
     * @return PDO La instancia única del objeto PDO.
     * @throws PDOException Si la conexión a la base de datos falla.
     */
    final public static function getConnection(): PDO
    {
        // Si la instancia aún no ha sido creada...
        if (self::$instance === null) {
            // ...se crea una nueva conexión.

            // --- Construcción del DSN (Data Source Name) ---
            // El DSN es una cadena de texto que le dice a PDO a qué base de datos
            // conectarse, en qué host se encuentra y con qué juego de caracteres.
            // Las constantes (HOST, DB_NAME, CHARSET) se cargan desde `src/DB/dataDB.php`.
            $dsn = 'mysql:host=' . HOST . ';dbname=' . DB_NAME . ';charset=' . CHARSET;

            // --- Opciones de Configuración de PDO ---
            // Este array configura el comportamiento de la conexión PDO.
            $options = [
                // `ATTR_ERRMODE => ERRMODE_EXCEPTION`: Es la configuración más importante.
                // Le dice a PDO que, en lugar de emitir Warnings silenciosos, lance
                // una `PDOException` cuando ocurra un error. Esto permite capturar
                // los errores de base de datos con bloques `try-catch`, lo cual es
                // un manejo de errores mucho más limpio y robusto.
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                // `ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC`: Establece el modo de obtención
                // de resultados por defecto. `FETCH_ASSOC` devuelve las filas como un
                // array asociativo (ej. `['id' => 1, 'name' => 'Juan']`), que es muy
                // conveniente para convertir a JSON.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // `ATTR_EMULATE_PREPARES => false`: Desactiva la emulación de sentencias
                // preparadas. Esto le indica a PDO que use sentencias preparadas nativas
                // del motor de la base de datos, lo cual es más seguro y previene
                // ataques de inyección SQL.
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // --- Creación de la Instancia de PDO ---
            // Se intenta crear el objeto PDO. Si las credenciales son incorrectas,
            // el host no es accesible, o la base de datos no existe, el constructor
            // de PDO lanzará una `PDOException` aquí mismo.
            // La responsabilidad de capturar esta excepción recae en el código que
            // llama a `getConnection()`, no en esta clase.
            self::$instance = new PDO($dsn, USER, PASSWORD, $options);
        }

        // Devuelve la instancia de PDO (ya sea la recién creada o la existente).
        return self::$instance;
    }
}
