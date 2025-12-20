<?php
/**
 * =====================================================================================
 * SCRIPT PARA GENERAR UN HASH DE CONTRASEÑA SEGURO
 * =====================================================================================
 *
 * Propósito:
 * ----------
 * Este script se utiliza para crear un hash criptográfico de una contraseña de texto plano.
 * El hash resultante se puede almacenar de forma segura en una base de datos para
 * la autenticación de usuarios. Utiliza la función nativa de PHP `password_hash()`,
 * que es la forma recomendada y más segura de manejar contraseñas en PHP.
 *
 * ¡ADVERTENCIA DE SEGURIDAD!:
 * --------------------------
 * Este archivo es una herramienta de desarrollo y NUNCA debe subirse a un servidor
 * de producción. Exponer este archivo podría permitir a un atacante generar hashes
 * para contraseñas conocidas y comprometer la seguridad del sistema.
 *
 */

// --- 1. Definición de la Contraseña ---
// Aquí se define la contraseña en texto plano que se va a hashear.
// Cambia 'admin123' por la contraseña que necesites para tus pruebas iniciales.
$password = 'admin123';

// --- 2. Generación del Hash ---
// Se utiliza `password_hash()` para crear el hash.
//
// Parámetros utilizados:
//   - $password: La contraseña en texto plano.
//   - PASSWORD_BCRYPT: El algoritmo de hashing a utilizar. BCRYPT es el estándar
//     actual, ya que es fuerte y lento a propósito para dificultar los ataques de
//     fuerza bruta. PHP gestiona automáticamente la generación de una "sal" (salt)
//     única para cada hash, lo que aumenta aún más la seguridad.
//   - ['cost' => 10]: El "costo" define cuántas iteraciones se usarán para generar
//     el hash. Un costo más alto hace que el hash sea más seguro (más lento de calcular),
//     pero también consume más recursos del servidor. El valor por defecto es 10,
//     que es un buen equilibrio entre seguridad y rendimiento. Puedes aumentarlo
//     a 11 o 12 si tu hardware lo permite.
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// --- 3. Visualización del Resultado ---
// Se imprime el hash en la pantalla (o en la consola si se ejecuta vía CLI).
// Este hash es el que deberías copiar y pegar en tu base de datos, por ejemplo,
// en la columna 'password' de la tabla 'users' para la semilla de datos inicial.
echo "Contraseña original: " . $password . "\n";
echo "Hash generado: " . $hash . "\n";

/**
 * Cómo funciona la verificación:
 * -----------------------------
 * Para verificar si una contraseña introducida por un usuario es correcta,
 * NO se vuelve a hashear la contraseña introducida para compararla con el hash almacenado.
 * En su lugar, se usa la función `password_verify()`:
 *
 * Ejemplo:
 * if (password_verify($contraseña_del_usuario, $hash_de_la_db)) {
 *     // La contraseña es correcta
 * } else {
 *     // La contraseña es incorrecta
 * }
 *
 * `password_verify` se encarga de extraer la sal del hash almacenado y realizar la
 * comparación de forma segura.
 */
?>
