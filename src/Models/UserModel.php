<?php

/**
 * ==================================================================================
 * DECLARACIÓN DE TIPOS ESTRICTOS (strict_types)
 * ==================================================================================
 *
 * Habilita el modo estricto de tipos para asegurar que el código sea más robusto
 * y predecible, evitando conversiones de tipo automáticas e inesperadas.
 */
declare(strict_types=1);

/**
 * ==================================================================================
 * NAMESPACE (Espacio de Nombres)
 * ==================================================================================
 *
 * Define el espacio de nombres para esta clase, siguiendo el estándar PSR-4.
 * Organiza la clase UserModel dentro de la estructura lógica de la aplicación.
 */

namespace App\Models;

/**
 * ==================================================================================
 * IMPORTACIÓN DE CLASES (Use)
 * ==================================================================================
 *
 * Importa las clases necesarias para el funcionamiento del modelo.
 * - `App\DB\ConnectionDB`: Para obtener la conexión a la base de datos (Singleton).
 * - `PDO`: La clase de PHP para interactuar con la base de datos.
 * - `PDOException`: Para capturar errores específicos de la base de datos.
 */
use App\DB\ConnectionDB;
use PDO;
use PDOException;

/**
 * ==================================================================================
 * MODELO DE USUARIO (UserModel)
 * ==================================================================================
 *
 * @author Ivor
 * @date 2025-12-07
 *
 * ARQUITECTURA Y RESPONSABILIDADES:
 * --------------------------------
 * Esta clase implementa el patrón de diseño "Modelo". Su única y exclusiva
 * responsabilidad es interactuar con la tabla `usuario` en la base de datos.
 *
 * Las responsabilidades clave son:
 * 1.  **Abstracción de la Base de Datos**: Proporciona métodos con nombres claros
 *     (ej. `getAll`, `find`, `create`) que ocultan la complejidad de las consultas SQL.
 * 2.  **Lógica de Datos**: Contiene todo el código SQL para consultar y manipular
 *     los datos de los usuarios.
 * 3.  **Seguridad**: Utiliza **sentencias preparadas** (`prepared statements`) en
 *     todas sus consultas para prevenir ataques de inyección SQL.
 *
 * ¿QUÉ NO HACE ESTA CLASE?
 * -----------------------
 * - No maneja la lógica de la petición HTTP (eso es trabajo del Controlador).
 * - No genera respuestas JSON (eso también es trabajo del Controlador).
 * - No contiene HTML ni ninguna otra lógica de presentación.
 *
 * Este diseño sigue el principio de "Separación de Responsabilidades", haciendo
 * que el código sea más limpio, seguro, reutilizable y fácil de mantener.
 */
class UserModel
{
    /**
     * @var PDO La instancia de la conexión a la base de datos.
     */
    private PDO $connection;

    /**
     * @var string El nombre de la tabla de la base de datos asociada a este modelo.
     */
    private string $table = 'usuario';

    /**
     * Constructor del modelo.
     * Obtiene la instancia Singleton de la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->connection = ConnectionDB::getConnection();
    }

    /**
     * Obtiene todos los registros de usuarios de la base de datos.
     *
     * @return array Un array de usuarios. Si no hay usuarios, devuelve un array vacío.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->connection->query("SELECT ID_USUARIO, nombre, correo, fecha FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            // En una aplicación real, aquí se registraría el error.
            // error_log("Error en UserModel::getAll(): " . $e->getMessage());
            return []; // Devolver un array vacío en caso de error.
        }
    }

    /**
     * Busca un usuario por su ID.
     *
     * @param int $id El ID del usuario a buscar.
     * @return array|null Los datos del usuario como un array asociativo, o null si no se encuentra.
     */
    public function find(int $id): ?array
    {
        try {
            // 1. Preparar la consulta SQL con un marcador de posición (:id).
            $stmt = $this->connection->prepare("SELECT ID_USUARIO, nombre, correo, rol, fecha FROM {$this->table} WHERE ID_USUARIO = :id");

            // 2. Vincular el valor del parámetro al marcador de posición.
            // Esto neutraliza cualquier código malicioso (inyección SQL).
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // 3. Ejecutar la consulta.
            $stmt->execute();

            // 4. Obtener el resultado.
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (PDOException $e) {
            // error_log("Error en UserModel::find(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param array $data Un array asociativo con los datos del usuario.
     * @return int|null El ID del usuario recién creado, o null si la creación falla.
     */
    public function create(array $data): ?int
    {
        try {
            // 1. Preparar la consulta SQL con marcadores de posición.
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (nombre, dni, correo, rol, password) VALUES (:nombre, :dni, :correo, :rol, :password)"
            );

            // 2. Vincular los valores a los marcadores de posición.
            $stmt->bindValue(':nombre', $data['nombre']);
            $stmt->bindValue(':dni', $data['dni']);
            $stmt->bindValue(':correo', $data['correo']);
            $stmt->bindValue(':rol', $data['rol'], PDO::PARAM_INT);
            $stmt->bindValue(':password', $data['password']); // La contraseña ya debe estar hasheada.

            // 3. Ejecutar la inserción.
            $stmt->execute();

            // 4. Devolver el ID del nuevo registro.
            return (int)$this->connection->lastInsertId();
        } catch (PDOException $e) {
            // error_log("Error en UserModel::create(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza un usuario existente en la base de datos.
     *
     * @param int $id El ID del usuario a actualizar.
     * @param array $data Un array asociativo con los datos a actualizar.
     * @return bool true si la actualización fue exitosa, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        try {
            // 1. Construir la parte de la consulta de los campos a actualizar dinámicamente.
            $fields = [];
            $allowedFields = ['nombre', 'dni', 'correo', 'rol', 'password'];
            foreach (array_keys($data) as $field) {
                if (in_array($field, $allowedFields, true)) {
                    $fields[] = "{$field} = :{$field}";
                }
            }
            if (empty($fields)) {
                return false; // No hay campos válidos para actualizar.
            }
            $query_fields = implode(', ', $fields);

            // 2. Preparar la consulta SQL completa.
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET {$query_fields} WHERE ID_USUARIO = :id"
            );

            // 3. Vincular los datos del cuerpo de la petición.
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields, true)) {
                    $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $stmt->bindValue(":{$key}", $value, $paramType);
                }
            }
            // Vincular el ID de la URL.
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // 4. Ejecutar la consulta.
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Error en UserModel::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param int $id El ID del usuario a eliminar.
     * @return bool true si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        try {
            // 1. Preparar la consulta SQL.
            $stmt = $this->connection->prepare("DELETE FROM {$this->table} WHERE ID_USUARIO = :id");

            // 2. Vincular el ID.
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // 3. Ejecutar y devolver el resultado.
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Error en UserModel::delete(): " . $e->getMessage());
            return false;
        }
    }
}
