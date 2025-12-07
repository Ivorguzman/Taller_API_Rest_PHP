<?php

declare(strict_types=1);

// --- CONFIGURACIÓN INICIAL ---
header("Content-Type: application/json; charset=utf-8");

// --- CARGA DE ===ARCHIVOS=== ESENCIALES ---
require_once dirname(__DIR__) . "/vendor/autoload.php";
require_once dirname(__DIR__) . "/public/codigosEstado.php";

// Importa las ===CLASES=== que vamos a usar.
use App\Config\Security;
use App\Config\ResponseHttp;
use Firebase\JWT\ExpiredException;

/**
 * =================================================================
 * SCRIPT DE PRUEBA PARA Security::validarTokenJwt()
 * =================================================================
 * Este script demuestra cómo usar el nuevo método y capturar sus excepciones.
 */

// El bloque try...catch es ahora el mecanismo principal para manejar la validación.
try {
	// --- 1. INTENTO DE VALIDACIÓN ---
	// Llamamos a validarTokenJwt. Le pasamos las cabeceras de la petición actual.
	// Si el token es válido, la función devolverá el payload y la ejecución continuará.
	// Si el token falta, es inválido o ha expirado, la función lanzará una excepción
	// y la ejecución saltará inmediatamente al bloque 'catch' correspondiente.
	$datosUsuario = Security::validarTok3enJwt(getallheaders());

	// --- 2. SI LA VALIDACIÓN ES EXITOSA ---
	// Si el código llega a esta línea, significa que el token era válido.
	// Preparamos una respuesta de éxito para demostrarlo.
	ResponseHttp::data([
		'message'      => '¡Token validado con éxito!',
		'user_payload' => $datosUsuario,
	]);

	// Enviamos la respuesta 200 (OK).
	echo json_encode(ResponseHttp::status200(CE_200));

	}
catch (ExpiredException $e) {
	// --- 3. CAPTURA DE EXCEPCIÓN ESPECÍFICA (TOKEN EXPIRADO) ---
	// Este bloque se ejecuta SOLAMENTE si la librería JWT lanza una ExpiredException.
	// Esto nos permite dar un mensaje de error más específico.
	$response = ResponseHttp::status401("El token ha expirado. Por favor, inicie sesión de nuevo.");
	echo json_encode($response);

	}
catch (\Throwable $e) {
	// --- 4. CAPTURA DE CUALQUIER OTRA EXCEPCIÓN DE VALIDACIÓN ---
	// Este bloque 'catch' es la "red de seguridad". Atrapa cualquier otra excepción
	// lanzada desde validarTokenJwt (ej. falta de cabecera, formato incorrecto, firma inválida).
	// Usamos el mensaje de la propia excepción, que es muy descriptivo.
	$response = ResponseHttp::status401($e->getMessage());
	echo json_encode($response);
	}
