<?php

declare(strict_types=1);

/**
 * @file CodigosEstado.php
 * @brief Archivo para definir mensajes de estado estandarizados para la API.
 *
 * --- ¿QUÉ HACE ESTE ARCHIVO? ---
 * Este archivo define un conjunto de "constantes". Una constante es como una variable
 * cuyo valor nunca puede cambiar una vez que se ha definido.
 *
 * --- ¿POR QUÉ USAR CONSTANTES PARA LOS MENSAJES? ---
 * 1. Evita "Magic Strings" (Cadenas Mágicas): En lugar de escribir el mensaje
 *    "Error Interno: " en 10 lugares diferentes del código, usamos la constante `CE_500`.
 * 2. Facilita el Mantenimiento: Si queremos cambiar un mensaje, solo lo hacemos
 *    en este archivo, y el cambio se reflejará en toda la aplicación.
 * 3. Previene Errores de Tipeo: Es más fácil que el autocompletado nos ayude con `CE_404`
 *    a que escribamos mal un mensaje de texto.
 * 4. Consistencia: Asegura que todos los mensajes de error y éxito que la API
 *    devuelve al cliente sean siempre los mismos, manteniendo una comunicación coherente.
 *
 * En resumen, este archivo es nuestro "diccionario" central de mensajes de la API.
 */

// --- CÓDIGOS DE ÉXITO (Serie 2xx) ---
// Estos códigos indican que la petición del cliente fue recibida, entendida y aceptada con éxito.
define("CE_200", "Operación exitosa"); // OK. La petición se completó correctamente.
define("CE_201", "Recurso creado exitosamente"); // Created. Se ha creado un nuevo recurso (ej. un nuevo usuario).
define("CE_204", "Petición procesada sin contenido para retornar"); // No Content. La petición fue exitosa, pero no hay nada que devolver (ej. en un DELETE).

// --- CÓDIGOS DE ERROR DEL CLIENTE (Serie 4xx) ---
// Estos códigos indican que ha ocurrido un error por parte del cliente.
// La petición es incorrecta, no está autorizada, o solicita un recurso que no existe.
define("CE_400", "Petición incorrecta"); // Bad Request. La sintaxis de la petición es inválida.
define("CE_401", "No autorizado. Se requiere autenticación"); // Unauthorized. El cliente no ha iniciado sesión.
define("CE_403", "Acceso prohibido. No tienes permisos para este recurso"); // Forbidden. El cliente inició sesión pero no tiene los permisos necesarios.
define("CE_404", "Recurso no encontrado"); // Not Found. El endpoint o el recurso específico no existe.
define("CE_405", "Método no permitido"); // Method Not Allowed. Se usó un verbo HTTP (ej. GET) en un endpoint que no lo soporta.
define("CE_410", "El recurso solicitado ya no está disponible"); // Gone. El recurso existió pero fue eliminado permanentemente.
define("CE_422", "Entidad no procesable. Los datos enviados son incorrectos"); // Unprocessable Entity. Errores de validación (ej. email inválido).

// --- CÓDIGOS DE ERROR DEL SERVIDOR (Serie 5xx) ---
// Estos códigos indican que el servidor falló al intentar procesar una petición aparentemente válida.
// El problema está en el servidor, no en el cliente.
define("CE_500", "Error interno del servidor"); // Internal Server Error. Un error genérico que no fue capturado.
define("CE_503", "Servicio no disponible"); // Service Unavailable. El servidor está sobrecargado o en mantenimiento.
