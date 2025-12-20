<?php

/**
 * =====================================================================================
 * SCRIPT DE PRUEBA PARA LA CLASE `ResponseHttp`
 * =====================================================================================
 *
 * Propósito:
 * ----------
 * Este archivo sirve para verificar el funcionamiento correcto de nuestra clase
 * de utilidad `ResponseHttp`. La clase `ResponseHttp` está diseñada para estandarizar
 * y simplificar la creación de respuestas JSON en toda la API.
 *
 * Escenario de Prueba:
 * --------------------
 * 1. Simula la obtención de datos (en este caso, un array de usuario).
 * 2. Utiliza el método estático `ResponseHttp::data()` para "cargar" esos datos
 *    en el estado interno de la clase.
 * 3. Llama a un método de estado (ej. `ResponseHttp::status200()`) para construir
 *    la estructura final de la respuesta, que incluirá los datos previamente cargados.
 * 4. Imprime el resultado en formato JSON para que podamos verlo.
 *
 * Este script es una herramienta de desarrollo para asegurar que `ResponseHttp` se
 * comporta como esperamos antes de usarla en los controladores reales.
 */

// --- 1. CONFIGURACIÓN INICIAL DEL ENTORNO DE PRUEBA ---

// Activa el modo estricto de tipos para mayor robustez.
declare(strict_types=1);

// Establece la cabecera para indicar que la salida será JSON.
header("Content-Type: application/json");

// --- 2. CARGA DE ARCHIVOS ESENCIALES ---

// Carga el autoloader de Composer. Sin esto, no podríamos encontrar la clase `ResponseHttp`.
require_once dirname(__DIR__) . "/vendor/autoload.php";
// Carga nuestras constantes de mensajes (ej. CE_200) para usarlas en la respuesta.
require_once dirname(__DIR__) . "/public/codigosEstado.php";

// --- 3. IMPORTACIÓN DE LA CLASE A PROBAR ---

// Importa la clase `ResponseHttp` para poder referirnos a ella con su nombre corto.
use App\Config\ResponseHttp;

// --- 4. EJECUCIÓN DE LA PRUEBA ---

echo "--- INICIO DE LA PRUEBA PARA ResponseHttp ---\n\n";

// Paso A: Preparar un conjunto de datos de ejemplo.
// Esto simula los datos que un controlador obtendría de la base de datos.
$userData = [
    'id'    => 123,
    'name'  => 'Juan Pérez',
    'email' => 'juan.perez@example.com',
    'roles' => ['editor', 'nocturno'],
];
echo "Datos de prueba preparados:\n";
print_r($userData);
echo "\n";

// Paso B: Cargar los datos en el estado de la clase ResponseHttp.
// El método `data()` es estático y `void`. Su única función es almacenar
// los datos en una propiedad estática interna de la clase para su uso posterior.
ResponseHttp::data($userData);
echo "Llamada a ResponseHttp::data() para cargar los datos (no devuelve nada).\n\n";

// Paso C: Generar la respuesta final de éxito.
// El método `status200()` ahora recuperará los datos almacenados en el paso anterior
// y los combinará con el mensaje y el código de estado para formar la respuesta completa.
echo "Llamada a ResponseHttp::status200() para construir la respuesta...\n";
$response = ResponseHttp::status200(CE_200); // Usamos la constante para el mensaje.

// Paso D: Imprimir el resultado final.
// Se usa `json_encode` con flags para que la salida sea legible (indentada y con caracteres UTF-8).
echo "\n--- RESPUESTA JSON GENERADA ---\n";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n--- FIN DE LA PRUEBA ---";

/*
 * =====================================================================================
 * NOTA IMPORTANTE SOBRE EL DISEÑO DE LA CLASE
 * =====================================================================================
 *
 * El diseño anterior permitía encadenar métodos: `ResponseHttp::data($datos)->status200()`.
 * Esto se ha modificado deliberadamente para que los métodos sean puramente estáticos
 * y no devuelvan una instancia de la clase (`$this`).
 *
 * ¿Por qué el cambio?
 * -------------------
 * Para forzar un uso más claro y menos propenso a errores:
 * 1. Primero, se cargan los datos con `data()`.
 * 2. Después, se construye la respuesta con `statusXXX()`.
 *
 * El siguiente código ahora producirá un ERROR FATAL, lo cual es el comportamiento DESEADO.
 *
 *      "Fatal error: Uncaught Error: Call to a member function status200() on void"
 *
 * Esto nos protege de intentar usar la clase de una manera para la que ya no está diseñada.
 *
 *      ResponseHttp::data($userData)->status200(CE_200); // <-- ESTO YA NO FUNCIONA Y ES BUENO QUE ASÍ SEA.
 *
 * =====================================================================================
 */
