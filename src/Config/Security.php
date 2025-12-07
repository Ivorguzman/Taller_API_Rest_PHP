<?php

/**
 * ==================================================================================
 * CLASE DE SEGURIDAD (Security.php)
 * ==================================================================================
 *
 * @author Ivor
 * @date 2025-11-15
 *
 *
 * ¿QUÉ HACE ESTA CLASE?
 * ----------------------
 * Esta clase es el núcleo de la seguridad de la aplicación. Agrupa un conjunto de
 * herramientas (métodos) estáticas y reutilizables para manejar tareas críticas
 * relacionadas con la seguridad. Su propósito es centralizar la lógica para que
 * sea consistente, fácil de mantener y auditar.
 *
 * Las responsabilidades principales incluyen:
 * 1.  **Gestión de Secretos**: Cargar de forma segura la clave secreta de la aplicación.
 * 2.  **Manejo de Contraseñas**: Crear hashes seguros de contraseñas y verificarlos.
 * 3.  **Autenticación por Token**: Crear y validar JSON Web Tokens (JWT) para
 *     proteger los endpoints de la API.
 *
 *
 * ¿POR QUÉ SE ESTRUCTURA ASÍ?
 * ---------------------------
 * - **Clase de Utilidad (Utility Class)**: Se diseña como una clase con métodos
 *   `static`. Esto significa que no necesitas crear un objeto `new Security()` para
 *   usar sus funciones. Puedes llamarlas directamente: `Security::crearPassword(...)`.
 *   Esto es ideal para funciones que no dependen de un estado interno (son "puras").
 * - **Centralización**: Tener toda la lógica de seguridad en un solo lugar previene
 *   la duplicación de código y asegura que las mismas reglas y algoritmos se apliquen
 *   en toda la aplicación. Si se necesita cambiar el algoritmo de hashing, solo se
 *   hace en un único sitio.
 * - **Abstracción**: Oculta la complejidad de las librerías subyacentes (como
 *   `php-jwt` y `phpdotenv`). Otros desarrolladores no necesitan saber cómo funcionan
 *   estas librerías; solo necesitan usar los métodos simples que esta clase ofrece.
 * - **Seguridad por Diseño**: Métodos como `secretKey()` implementan patrones como
 *   el "fail-fast" (fallar rápido) y el caching para ser robustos y eficientes.
 */

// Directiva para forzar el tipado estricto.
// ¿POR QUÉ? Es una buena práctica en PHP moderno que previene errores de tipo,
// forzando a que las variables y los parámetros de función sean del tipo exacto
// que se declaró. Ayuda a escribir código más predecible y menos propenso a bugs.
declare(strict_types=1);

// Define el "espacio de nombres" para organizar la clase.
// ¿POR QUÉ? Evita colisiones de nombres con otras clases y estructura el proyecto
// de una manera lógica y estándar, siguiendo las recomendaciones de PSR-4.
namespace App\Config;

// Importa las clases de las librerías de terceros que se van a utilizar.
// ¿POR QUÉ? Permite usar nombres más cortos y legibles (`Dotenv` en vez de
// `\Dotenv\Dotenv`) y declara explícitamente las dependencias de esta clase.
use Dotenv\Dotenv; // Para cargar variables de entorno desde el archivo .env.
use Firebase\JWT\JWT; // Para codificar y decodificar tokens JWT.
use Firebase\JWT\Key; // Para encapsular la clave secreta al validar un JWT.

  //&& Clase Security: Contenedor de herramientas de seguridad.
class Security
{
    /**
     * @var string|null Caché para la clave secreta.
     *
     * ¿QUÉ HACE?
     * Almacena la clave secreta en memoria después de leerla por primera vez.
     *
     * ¿CÓMO LO HACE?
     * Es una propiedad `private static`.
     * - `private`: Solo se puede acceder desde dentro de esta misma clase.
     * - `static`: Su valor es compartido por todas las llamadas a la clase, no
     *   pertenece a una instancia. Persiste durante toda la ejecución de la petición.
     *
     * ¿POR QUÉ SE HACE ASÍ?
     * Para mejorar el rendimiento. Leer y procesar el archivo `.env` en cada
     * llamada a una función de seguridad sería muy ineficiente. Con la caché,
     * este proceso solo ocurre una vez por petición.
     */
    private static ?string $cachedSecretKey = null;

    /**
     * Obtiene la clave secreta de la aplicación de forma segura y eficiente.
     *
     * ¿QUÉ HACE?
     * Proporciona la `SECRET_KEY` definida en el archivo `.env`.
     *
     * ¿CÓMO LO HACE?
     * 1. Revisa si la clave ya está en la caché (`$cachedSecretKey`). Si es así, la devuelve.
     * 2. Si no, carga las variables del archivo `.env` usando la librería Dotenv.
     * 3. Obtiene el valor de `SECRET_KEY`.
     * 4. Valida que la clave no esté vacía (es un error crítico si falta).
     * 5. Guarda la clave en la caché y la devuelve.
     *
     * ¿POR QUÉ SE HACE ASÍ?
     * - **Seguridad**: Centraliza el acceso a la clave. Ninguna otra parte del código
     *   necesita saber de dónde viene la clave.
     * - **Eficiencia**: El uso de la caché (patrón Singleton implícito para el valor)
     *   evita la sobrecarga de leer el sistema de archivos repetidamente.
     * - **Robustez**: Lanza una excepción (`RuntimeException`) si la clave no está
     *   configurada, deteniendo la aplicación de forma segura ("fail-fast") en lugar
     *   de continuar en un estado inseguro.
     *
     * @return string La clave secreta para firmar tokens.
     * @throws \RuntimeException Si `SECRET_KEY` no está definida en `.env`.
     */
    final public static function secretKey(): string
    {
        // Paso 1: Comprobar la caché para un retorno inmediato.
        if (self::$cachedSecretKey !== null) {
            return self::$cachedSecretKey;
        }

        // Paso 2: Cargar el archivo .env si la clave no está en caché.
        // `dirname(__DIR__, 2)` navega dos niveles hacia arriba desde el directorio actual
        // para llegar a la raíz del proyecto de forma fiable.
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load(); // Carga las variables en el superglobal `$_ENV`.

        // Paso 3: Extraer la clave del entorno.
        // El operador `??` (null coalescing) es un atajo seguro para obtener el valor
        // o `null` si no existe, evitando warnings de PHP.
        $secretKey = $_ENV['SECRET_KEY'] ?? null;

        // Paso 4: Validar la existencia de la clave.
        if ($secretKey === null) {
            // Es más seguro detener la ejecución que permitir que la aplicación
            // continúe sin una clave secreta (ej. creando tokens no seguros).
            throw new \RuntimeException('La variable de entorno SECRET_KEY no está definida en el archivo .env');
        }

        // Paso 5: Guardar en caché y devolver.
        // La asignación y el retorno se hacen en una sola línea.
        return self::$cachedSecretKey = $secretKey;
    }

    /**
     * Crea un hash seguro de una contraseña para su almacenamiento.
     *
     * ¿QUÉ HACE?
     * Convierte una contraseña de texto plano (ej. "123456") en un hash criptográfico
     * irreversible (ej. "$2y$10$n...").
     *
     * ¿CÓMO LO HACE?
     * Utiliza la función nativa de PHP `password_hash()`, que es el estándar de oro.
     * - `PASSWORD_DEFAULT`: Le indica a PHP que use el algoritmo más seguro disponible
     *   (actualmente `bcrypt`). Esto hace que el código sea resistente al futuro, ya
     *   que se adaptará automáticamente si PHP introduce un algoritmo mejor.
     * - La función genera automáticamente una "sal" (salt) aleatoria para cada hash,
     *   lo cual es fundamental para protegerse contra ataques de tablas arcoíris.
     *
     * ¿POR QUÉ SE HACE ASÍ?
     * ¡NUNCA se debe guardar una contraseña en texto plano! Este método asegura que
     * incluso si la base de datos es comprometida, las contraseñas no puedan ser
     * leídas directamente.
     *
     * @param string $plainTextPassword La contraseña introducida por el usuario.
     * @return string El hash resultante, listo para ser guardado en la base de datos.
     * @throws \RuntimeException Si el proceso de hashing falla por alguna razón.
     */
    final public static function crearPassword(string $plainTextPassword): string
    {
        // Genera el hash usando el algoritmo recomendado por PHP.
        $passwordHash = password_hash($plainTextPassword, PASSWORD_DEFAULT);

        // Comprobación de seguridad: aunque es raro, `password_hash` puede fallar.
        if ($passwordHash === false) {
            throw new \RuntimeException('No se pudo crear el hash de la contraseña.');
        }

        // Devuelve el hash, que incluye el algoritmo, el coste, la sal y el hash final.
        return $passwordHash;
    }

    /**
     * Verifica si una contraseña de texto plano coincide con un hash almacenado.
     *
     * ¿QUÉ HACE?
     * Compara de forma segura la contraseña que un usuario introduce durante el login
     * con el hash que está guardado en la base de datos.
     *
     * ¿CÓMO LO HACE?
     * Usa la función `password_verify()`. Esta función extrae la sal y el algoritmo
     * del hash almacenado, aplica el mismo proceso a la contraseña de texto plano
     * y compara los resultados de una manera que previene ataques de temporización.
     *
     * ¿POR QUÉ SE HACE ASÍ?
     * Es la única forma correcta de verificar un hash creado con `password_hash()`.
     * Intentar comparar los hashes manualmente es inseguro y propenso a errores.
     *
     * @param string $password La contraseña en texto plano a verificar.
     * @param string $hash El hash recuperado de la base de datos.
     * @return bool `true` si la contraseña es correcta, `false` si no lo es.
     */
    final public static function verificarPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Crea un JSON Web Token (JWT) firmado.
     *
     * ¿QUÉ HACE?
     * Genera un token que puede ser entregado a un usuario después de un login exitoso.
     * Este token sirve como una "credencial" para futuras peticiones a la API.
     *
     * ¿CÓMO LO HACE?
     * 1. Construye el "payload" (cuerpo) del token con datos estándar y personalizados.
     *    - `iat` (Issued At): Marca de tiempo de cuándo se creó el token.
     *    - `exp` (Expiration Time): Marca de tiempo de cuándo el token dejará de ser válido.
     *    - `data`: Un contenedor para datos de la aplicación (ej. `['user_id' => 1]`).
     * 2. Llama a `JWT::encode()` para codificar el payload y firmarlo digitalmente
     *    con la clave secreta y el algoritmo `HS256`.
     *
     * ¿POR QUÉ SE HACE ASÍ?
     * La firma digital garantiza la integridad del token (no puede ser modificado) y
     * su autenticidad (solo puede ser creado por alguien con la clave secreta).
     * La fecha de expiración es una medida de seguridad crucial para limitar la vida
     * útil de las sesiones.
     *
     * @param string $key La clave secreta para firmar el token.
     * @param array $data Los datos a incluir en el payload del token.
     * @param int $expSegundos Duración del token en segundos (por defecto 1 hora).
     * @return string El token JWT como una cadena de texto.
     */
    final public static function createTokenJwt(string $key, array $data, int $expSegundos = 3600): string
    {
        // Validaciones de entrada para evitar errores en tiempo de ejecución.
        if (empty($key)) {
            throw new \InvalidArgumentException('La clave secreta no puede estar vacía.');
        }
        if (empty($data)) {
            throw new \InvalidArgumentException('Los datos del token no pueden estar vacíos.');
        }

        // Momento actual como timestamp UNIX.
        $ahora = time();

        // Construcción del payload del token.
        $payload = [
            'iat'  => $ahora,
            'exp'  => $ahora + $expSegundos,
            'data' => $data,
        ];

        // Codifica y firma el token.
        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * Valida un token JWT de una cabecera de autorización.
     *
     * ¿QUÉ HACE?
     * Inspecciona una petición HTTP entrante, extrae el token JWT de la cabecera
     * `Authorization` y verifica si es válido.
     *
     * ¿CÓMO LO HACE?
     * 1. Comprueba que la cabecera `Authorization` exista.
     * 2. Extrae el token, esperando el formato "Bearer <token>".
     * 3. Usa un bloque `try-catch` para intentar decodificar el token con `JWT::decode()`.
     *    - `JWT::decode` se encarga de todo: verifica la firma, la fecha de expiración
     *      y el formato del token.
     * 4. Si la decodificación es exitosa, devuelve el payload.
     * 5. Si falla (ej. token expirado, firma inválida), captura la excepción y devuelve `false`.
     *
     * ¿POR QUÉ SE HACE ASÍ?
     * Este método encapsula toda la lógica de validación, proporcionando una forma
     * simple y segura de proteger los endpoints de la API. El manejo de excepciones
     * permite diferenciar entre distintos tipos de fallos de validación.
     *
     * @param array $headers El array de cabeceras de la petición (ej. `getallheaders()`).
     * @param string $key La clave secreta para validar la firma.
     * @return object|false El payload decodificado si el token es válido, o `false` si no lo es.
     */
    final public static function validarTokenJwt(array $headers, string $key)
    {
        // Paso 1: Verificar la existencia de la cabecera de autorización.
        if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
            error_log("Error de seguridad: Encabezado 'Authorization' no encontrado.");
            http_response_code(401); // 401 Unauthorized
            exit(); // Detiene la ejecución inmediatamente.
        }
        $authHeader = $headers['Authorization'];

        // Paso 2: Validar el formato "Bearer <token>".
        $partes = explode(' ', trim($authHeader));
        if (count($partes) !== 2 || !hash_equals('Bearer', $partes[0]) || empty($partes[1])) {
            return false; // Formato incorrecto.
        }
        $jwt = $partes[1]; // Extrae el token.

        // Paso 3: Intentar decodificar y validar el token.
        try {
            // `JWT::decode` hace todo el trabajo pesado de validación.
            // La librería requiere que la clave se pase dentro de un objeto `Key`.
            $data = JWT::decode($jwt, new Key($key, 'HS256'));
            return $data; // Éxito: devuelve el payload.

        } catch (\Firebase\JWT\ExpiredException $e) {
            // El token es válido, pero ha caducado.
            error_log("Token expirado: " . $e->getMessage());
            return false;
        } catch (\Throwable $th) {
            // Cualquier otro error (firma inválida, token malformado, etc.).
            error_log("Error de validación de token: " . $th->getMessage());
            return false;
        }
    }
}
