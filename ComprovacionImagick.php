<?php
declare(strict_types=1);

// Incluir el autoloader de Composer para poder usar nuestras clases
require_once __DIR__ . '/vendor/autoload.php';

// Activar nuestro manejador de errores personalizado.
// Es una buena práctica usar el mismo casing que en la definición del namespace (App\Config)
// y referenciar las clases desde el namespace raíz con un '\' al inicio.
use App\Config\ErrorLog;
use App\Config\ResponseHttp;

ErrorLog::activateErrorLog();

/*
 php_sapi_name()==> Devuelve el tipo de interfaz que hay entre PHP y el servidor Devuelve una cadena en minúsculas que describe el tipo de interfaz (la API de Servidor, SAPI) que está utilizando PHP. Por ejemplo, en PHP CLI esta cadena será "cli" mientras que en Apache podría tener varios valores diferentes dependiendo de la SAPI que se utilice
 */
// Detectar el entorno (web o línea de comandos)
$is_cli = (php_sapi_name() === 'cli');
$nl = $is_cli ? PHP_EOL : '<br>';

// Cláusula de Guarda ( ): Si la extensión no está cargada, muestra un mensaje útil y termina.
if (!extension_loaded('imagick')) {
    // Si la extensión no está cargada, muestra un mensaje útil.
    $message = 'La extensión Imagick NO está instalada o no está activada.';
    error_log($message); // También lo mandamos al log

    if (!$is_cli) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(ResponseHttp::status500($message));
    } else {
        echo $message . $nl;
        echo 'Para solucionarlo, edita el php.ini correcto y asegúrate de que las dependencias de ImageMagick sean accesibles.' . $nl;
    }
    exit;
}

// Si llegamos aquí, la extensión está cargada.
if ($is_cli) {
    echo 'La extensión Imagick está instalada y cargada.' . $nl;
}

// Ahora, intenta usar la clase.
try {
    // Se añade '\' para indicar que Imagick e ImagickPixel son clases del namespace global.
    $image = new Imagick();
    $image->newImage(100, 100, new ImagickPixel('red'));
    $image->setImageFormat('png');

    if (!$is_cli) {
        // Envía el header justo antes de la salida de la imagen.
        header('Content-Type: image/png');
        echo $image->getImageBlob(); // Usa getImageBlob() para obtener la imagen como cadena
    } else {
        echo 'La clase Imagick se ha instanciado correctamente.' . $nl;
    }
} catch (\Exception $e) {
    // Registrar el error detallado en nuestro archivo de log
    error_log("Error al usar Imagick en info.php: " . $e->getMessage());

    // Mostrar un mensaje genérico y amigable
    if (!$is_cli) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(ResponseHttp::status500('Ocurrió un error con el procesamiento de la imagen. Revise el log.'));
    } else {
        echo "Error al usar Imagick. Revisa el archivo de log para más detalles." . $nl;
    }
}
