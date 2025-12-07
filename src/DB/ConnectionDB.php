<?php
// Activa el modo estricto de tipos en PHP.
// Esto asegura que los tipos de datos de los argumentos y los valores de retorno
// de las funciones coincidan exactamente con los declarados, previniendo errores.
declare(strict_types=1);




// Define un "espacio de nombres" para esta clase. Los espacios de nombres son como
// carpetas para tu código: organizan las clases y evitan que dos clases con el mismo
// nombre choquen entre sí.
namespace App\DB;



/* use App\Config\ResponseHttp;

use PDO; */
use PDO;
use Dotenv\Dotenv;
class ConnectionDB
{
	private static $host;
	private static $user;
	private static $password;


	final public static function from($host, $user, $password)
	{

		self::$host = $host;
		self::$user = $user;
		self::$password = $password;
	}

	final public static function getConnection()
	{

		try {

			$opt = [PDO:: ATTR_DEFAULT_FETCH_MODE => PDO:: FETCH_ASSOC, PDO:: ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION, PDO:: ATTR_PERSISTENT => true, PDO:: MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_spanish_ci"];
			$dns = new PDO(self::$host, self::$user, self::$password, $opt);
			$dns->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $dns;
		}
		catch (PDOException $th) {
			$errorMessage = $th->getMessage();
			$logMessage = defined('CE_500') ? CE_500 . " Detalles: " . $errorMessage : "Error interno del servidor Conectando DB.";
			die(json_encode(ResponseHttp::status500(CE_500)));

		}
	}

}
