<?php

declare(strict_types=1);

// --- CONFIGURACIÓN INICIAL ---
header("Content-Type: application/json");

// --- CARGA DE ARCHIVOS ESENCIALES ---
// Carga el autoloader para poder usar nuestras clases.
require_once dirname(__DIR__) . "/vendor/autoload.php";
// Carga tus mensajes de estado personalizados.
require_once dirname(__DIR__) . "/public/codigosEstado.php";

//^ Importa la clase que vamos a probar.
use App\Config\ResponseHttp;

// --- INICIO DE LA PRUEBA ---

// 1. Preparamos un array con los datos que queremos enviar.
$userData = [
	'id'    => 123,
	'name'  => 'Juan Pérez',
	'email' => 'juan.perez@example.com',
	'roles' => ['editor', 'nocturno'],
];

// 2. Usamos el método data() para "cargar" los datos en la clase.
// Esta llamada no devuelve nada (es void).
ResponseHttp::data($userData);

// 3. A continuación, generamos la respuesta de éxito.
// La clase ResponseHttp internamente recordará los datos y los incluirá.
$response = ResponseHttp::status200(CE_200); // Usamos la constante de éxito.

// 4. Imprimimos la respuesta final en formato JSON.
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

/*
--- ¿QUÉ PASARÍA SI INTENTAMOS USAR EL MÉTODO ANTIGUO? ---

 El siguiente código ahora producirá un ERROR FATAL, lo cual es CORRECTO.
 "Fatal error: Uncaught Error: Call to a member function status200() on void"
 Esto nos protege de usar la clase de una manera que no fue diseñada.

 ResponseHttp::data($userData)->status200(CE_200); // <-- ESTO YA NO FUNCIONA Y ES BUENO QUE ASÍ SEA.
*/
