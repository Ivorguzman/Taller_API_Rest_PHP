<?php

/**
 * ==================================================================================
 * DECLARACIÓN DE TIPOS ESTRICTOS (strict_types)
 * ==================================================================================
 *
 * `declare(strict_types=1);` es una directiva de PHP que debe ser la primera
 * instrucción en un archivo. Habilita el modo estricto para la verificación de tipos.
 *
 * ¿POR QUÉ ES IMPORTANTE?
 * En modo estricto, PHP exige que los tipos de datos de los valores coincidan
 * exactamente con los tipos declarados en los parámetros de las funciones y en

 * los valores de retorno. Por ejemplo, si una función espera un `int`, pasarle un
 * string como "5" causará un `TypeError`. Sin el modo estricto, PHP intentaría
 * convertir el string a un número, lo que puede ocultar bugs.
 *
 * BENEFICIOS:
 * - **Robustez**: Reduce errores inesperados de tipo.
 * - **Claridad**: Hace que el código sea más predecible y fácil de entender.
 * - **Mantenimiento**: Facilita la depuración y el refactoring.
 */
declare(strict_types=1);

/**
 * ==================================================================================
 * NAMESPACE (Espacio de Nombres)
 * ==================================================================================
 *
 * `namespace App\Controllers;` organiza las clases en una estructura lógica y jerárquica.
 * Es como poner la clase `UserController` dentro de una carpeta virtual `App/Controllers`.
 *
 * BENEFICIOS:
 * - **Evita Colisiones**: Permite tener clases con el mismo nombre en diferentes
 *   namespaces sin que entren en conflicto.
 * - **Organización**: Estructura el proyecto de manera ordenada.
 * - **Autocarga (Autoloading)**: Es fundamental para el estándar PSR-4, que permite
 *   a Composer cargar automáticamente las clases sin necesidad de `require` manuales.
 */

namespace App\Controllers;

// Importa las clases que este controlador necesitará.
use App\Config\ResponseHttp;
use App\Config\Security;
use App\Models\UserModel;

/**
 * ==================================================================================
 * CONTROLADOR DE USUARIOS (UserController)
 * ==================================================================================
 *
 * @author Ivor
 * @date 2025-11-16
 *
 * ARQUITECTURA Y RESPONSABILIDADES:
 * --------------------------------
 * Esta clase implementa el patrón de diseño "Controlador" en un contexto de API REST.
 * Su única responsabilidad es gestionar las peticiones HTTP entrantes relacionadas
 * con el recurso "user".
 *
 * El flujo de trabajo es el siguiente:
 * 1.  **Recepción**: El constructor recibe toda la información de la petición (método,
 *     ruta, parámetros, cuerpo, cabeceras) desde el enrutador (`index.php`).
 * 2.  **Despacho (Dispatching)**: El método `dispatch()` actúa como un conmutador.
 *     Basándose en el método HTTP (`GET`, `POST`, `PUT`, `DELETE`), decide qué
 *     acción específica (método privado) debe ejecutarse.
 * 3.  **Ejecución de la Acción**: Cada método privado (ej. `get()`, `post()`) contiene
 *     la lógica para manejar un tipo de petición:
 *     - Valida los datos de entrada (parámetros de la URL, cuerpo de la petición).
 *     - Llama al "Modelo" (`UserModel`) para interactuar con la base de datos
 *       (leer, crear, actualizar o borrar usuarios).
 *     - NO contiene lógica de negocio compleja ni consultas SQL directas.
 * 4.  **Generación de Respuesta**: Una vez que el Modelo devuelve los datos (o un
 *     estado de éxito/fallo), el controlador utiliza la clase `ResponseHttp` para
 *     construir una respuesta JSON estandarizada y la envía de vuelta al cliente.
 *
 * Este diseño promueve la "Separación de Responsabilidades" (SoC), un principio
 * clave de la programación orientada a objetos, donde cada clase tiene un único
 * y bien definido propósito.
 */
class UserController
{
    // --------------------------------------------------------------------------
    // PROPIEDADES DE LA CLASE
    // --------------------------------------------------------------------------

    /**
     * @var string El método HTTP de la petición (ej: 'GET', 'POST'). Se guarda en minúsculas.
     */
    private string $method;

    /**
     * @var string La ruta completa solicitada por el cliente (ej: '/user/1').
     */
    private string $route;

    /**
     * @var array Los parámetros extraídos de la ruta (ej: para '/user/1', sería ['user', '1']).
     */
    private array $params;

    /**
     * @var array|null El cuerpo (body) decodificado de la petición, generalmente para POST y PUT.
     */
    private ?array $data;

    /**
     * @var array Las cabeceras (headers) de la petición HTTP.
     */
    private array $headers;

    /**
     * @var UserModel La instancia del modelo de usuario para interactuar con la base de datos.
     */
    private UserModel $userModel;

    // --------------------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------------------

    /**
     * Inicializa el controlador con los detalles de la petición actual.
     * Este método es llamado por el enrutador (`index.php`) para "inyectar"
     * el contexto de la petición en el controlador.
     *
     * @param string     $method  El método HTTP de la petición.
     * @param string     $route   La ruta completa solicitada.
     * @param array      $params  Segmentos de la ruta como un array.
     * @param array|null $data    El cuerpo de la petición (ya decodificado si es JSON).
     * @param array      $headers Las cabeceras HTTP de la petición.
     */
    public function __construct(string $method, string $route, array $params, ?array $data, array $headers)
    {
        $this->method = strtolower($method); // Normaliza el método a minúsculas para comparaciones fiables.
        $this->route = $route;
        $this->params = $params;
        $this->data = $data;
        $this->headers = $headers;
        $this->userModel = new UserModel(); // Instancia el modelo de usuario.

        // Inicia el proceso de despacho para manejar la petición.
        $this->dispatch();
    }

    // --------------------------------------------------------------------------
    // MÉTODO DE DESPACHO (El "Conmutador")
    // --------------------------------------------------------------------------

    /**
     * Dirige la petición al método privado correspondiente según el verbo HTTP.
     * Este es el punto de entrada principal del controlador después de su inicialización.
     */
    private function dispatch(): void
    {
        // Utiliza una estructura `match` (disponible en PHP 8+) para una sintaxis
        // más limpia y segura que un `switch`.
        match ($this->method) {
            'get'    => $this->get(),
            'post'   => $this->post(),
            'put'    => $this->put(),
            'delete' => $this->delete(),
            // Si se recibe un método no soportado, se devuelve un error 405.
            default  => $this->sendMethodNotAllowedResponse(),
        };
    }

    // --------------------------------------------------------------------------
    // MÉTODOS DE ACCIÓN (Lógica para cada verbo HTTP)
    // --------------------------------------------------------------------------

    /**
     * Maneja las peticiones GET (Leer usuarios).
     *
     * Lógica:
     * - Si la URL es `/user`, devuelve una lista de todos los usuarios.
     * - Si la URL es `/user/{id}`, devuelve un único usuario por su ID.
     */
    private function get(): void
    {
        if (isset($this->params[1])) {
            $userId = (int)$this->params[1];
            $user = $this->userModel->find($userId);
            if ($user) {
                ResponseHttp::data($user);
                $response = ResponseHttp::status200("Usuario encontrado.");
            } else {
                $response = ResponseHttp::status404("Usuario no encontrado.");
            }
        } else {
            $users = $this->userModel->getAll();
            ResponseHttp::data($users);
            $response = ResponseHttp::status200("Lista de usuarios obtenida.");
        }
        echo json_encode($response);
    }

    /**
     * Maneja las peticiones POST (Crear un nuevo usuario).
     *
     * Lógica:
     * - Valida que los datos necesarios (`name`, `email`, `password`) existan en el body.
     * - Hashea la contraseña.
     * - Llama al `UserModel` para crear el nuevo usuario en la base de datos.
     * - Devuelve una respuesta 201 (Created) con los datos del usuario creado.
     */
    private function post(): void
    {
        if (empty($this->data) || !isset($this->data['nombre'], $this->data['dni'], $this->data['correo'], $this->data['rol'], $this->data['password'])) {
            $response = ResponseHttp::status422("Datos incompletos. Se requiere 'nombre', 'dni', 'correo', 'rol' y 'password'.");
        } else {
            // Hashear la contraseña antes de guardarla.
            $this->data['password'] = Security::crearPassword($this->data['password']);

            $newUserId = $this->userModel->create($this->data);

            if ($newUserId) {
                $newUser = $this->userModel->find($newUserId);
                ResponseHttp::data($newUser);
                $response = ResponseHttp::status201("Usuario creado exitosamente.");
            } else {
                $response = ResponseHttp::status500("Error al crear el usuario.");
            }
        }
        echo json_encode($response);
    }

    /**
     * Maneja las peticiones PUT (Actualizar un usuario existente).
     *
     * Lógica:
     * - Extrae el ID del usuario de la URL.
     * - Valida que el ID exista.
     * - Valida que el cuerpo de la petición contenga datos para actualizar.
     * - Llama al `UserModel` para actualizar el usuario.
     */
    private function put(): void
    {
        if (!isset($this->params[1]) || empty($this->data)) {
            $response = ResponseHttp::status400("Se requiere el ID del usuario y datos para actualizar.");
        } else {
            $userId = (int)$this->params[1];

            // Si se envía una nueva contraseña, hashearla.
            if (isset($this->data['password'])) {
                $this->data['password'] = Security::crearPassword($this->data['password']);
            }

            if ($this->userModel->update($userId, $this->data)) {
                $response = ResponseHttp::status200("Usuario actualizado exitosamente.");
            } else {
                $response = ResponseHttp::status500("Error al actualizar el usuario o no se encontraron cambios.");
            }
        }
        echo json_encode($response);
    }

    /**
     * Maneja las peticiones DELETE (Eliminar un usuario).
     *
     * Lógica:
     * - Extrae el ID del usuario de la URL.
     * - Valida que el ID exista.
     * - Llama al `UserModel` para eliminar el usuario.
     * - Devuelve una respuesta 200.
     */
    private function delete(): void
    {
        if (!isset($this->params[1])) {
            $response = ResponseHttp::status400("Se requiere el ID del usuario a eliminar.");
        } else {
            $userId = (int)$this->params[1];
            if ($this->userModel->delete($userId)) {
                $response = ResponseHttp::status200("Usuario eliminado exitosamente.");
            } else {
                $response = ResponseHttp::status404("Usuario no encontrado o error al eliminar.");
            }
        }
        echo json_encode($response);
    }

    /**
     * Envía una respuesta de error para métodos no permitidos.
     */
    private function sendMethodNotAllowedResponse(): void
    {
        $message = defined('CE_405') ? CE_405 : 'Método no permitido';
        echo json_encode(ResponseHttp::status405($message));
    }
}
