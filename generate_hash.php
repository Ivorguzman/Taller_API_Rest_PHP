<?php
// Script para generar un hash de contraseña seguro con BCRYPT.
// ¡No Subir este archivo a  servidor de producción!

// La contraseña que quieres hashear. ¡Puedes cambiar 'admin123' por la que prefieras!
$password = 'admin123';

// Generamos el hash usando el algoritmo BCRYPT, que es el estándar y muy seguro.
 $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Imprimimos el hash resultante para que puedas copiarlo.
echo "Contraseña original: " . $password . "\n";
echo "Hash generado: " . $hash . "\n";
?>
