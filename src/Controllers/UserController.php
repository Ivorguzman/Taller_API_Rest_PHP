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

namespace App\Controllers;

/**
 * ==========================================================================
 * CONTROLADOR DE USUARIOS (UserController)
 * ==========================================================================
 *
 * Se encarga de gestionar todas las peticiones entrantes relacionadas con
 * el recurso "user". Actúa como intermediario entre las rutas, el modelo
 * de datos y las vistas (aunque en una API, la "vista" es la respuesta JSON).

 */
class UserController
{
    // --------------------------------------------------------------------------
    // PROPIEDADES
    // --------------------------------------------------------------------------

    /**
     * @var string El método HTTP de la petición (ej: 'get', 'post').
     */
    private string $method;

    /**
     * @var string La ruta solicitada por el cliente (ej: 'users/1').
     */
    private string $route;

    /**
     * @var array Los parámetros extraídos de la ruta.
     */
    private array $parameters;

    /**
     * @var mixed El cuerpo (body) de la petición, generalmente para POST y PUT.
     */
    private $data;

    /**
     * @var mixed Las cabeceras (headers) de la petición.
     */
    private $headers;

    // --------------------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------------------

    /**
     * Inicializa el controlador con los detalles de la petición actual.
     *
     * @param string $method     El método HTTP de la petición.
     * @param string $route      La ruta solicitada.
     * @param array  $parameters Parámetros de la URL.
     * @param mixed  $data       Cuerpo de la petición.
     * @param mixed  $headers    Cabeceras de la petición.
     */
    public function __construct(string $method, string $route, array $parameters, $data, $headers)
    {
        $this->method = $method;
        $this->route = $route;
        $this->parameters = $parameters; 
        $this->data = $data;
        $this->headers = $headers;
    }
       // --------------------------------------------------------------------------
