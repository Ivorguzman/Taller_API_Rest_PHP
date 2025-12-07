<?php
/*
Divide un string en varios string. Devuelve un array de string, 
siendo cada uno un substring del parámetro string 
formado por la división realizada por los delimitadores 
indicados en el parámetro string separator. 
 MNOTA: Un string que no contiene el separador  simplemente devolverá un array de un elemento con el string original.
 */
// Ejemplo 0
print_r("<pre>");
print_r("============================= Ejemplo  SIN separador  ===============================" . "\n");
echo"<br><br>";
print_r("\n");
$hola = "Hola°como°estas";
$saludo = explode(' ',$hola);
//  print('$saludo := ')."\n";
print_r($saludo);
print_r("\n");
print_r("\n");
print_r("\n");
print_r("\n");
echo"<br><br>";


// Ejemplo 1
print_r("<pre>");
print_r("============================= Ejemplo 1 separador (º) ===============================" . "\n");
echo"<br><br>";
print_r("\n");
$hola = "Hola°como°estas";
$saludo = explode('°', $hola);
//  print('$saludo := ')."\n";
print_r($saludo);
print_r("\n");
print_r("\n");
print_r("\n");
print_r("\n");
echo"<br><br>";

// Ejemplo 2
print_r("========================= Ejemplo 2 separador (\" \") =================================" . "\n");
echo"<br><br>";
print_r("\n");
$pizza = "piece1 piece2 piece3 piece4 piece5 piece6";
$pieces = explode(" ", $pizza);
//  print ('$pieces := ') . "\n";
print_r($pieces);
print_r("\n");
print_r("\n");
print_r("\n");
echo "<br><br>";


/*  Ejemplo 3 : */
print_r("====================== Ejemplo 3 separador (,) ===============================" . "\n");
echo"<br><br>";
print_r(" Ejemplo 3 : Un string que no contiene el delimitador simplemente devolverá un array de un elemento con el string original." . "\n");
echo"<br><br>";
print_r("\n");
$input1 = "hola como estas";
//  print ('$input1 := ') . "\n";
print_r(explode(',', $input1));
print_r("\n");

$input2 = "hello,como estas";
//  print ('$input2 := ') . "\n";
print_r(explode(',', $input2));
print_r("\n");
print_r("\n");
echo "<br><br><br>";

// Ejemplo 4
print_r("========================= Ejemplo 4  separador (/) =================================" . "\n");
$routes = ['GET/POST/PUT/DELETE'];
$route = explode("/", $routes[0]);
print_r($route);
print_r("\n");
print_r("\n");
echo"<br><br>";






// Ejemplo #4 Ejemplos del parámetro limit 
print_r("============== Ejemplo 4 Ejemplos del parámetro limit ===========================" . "\n");
echo"<br><br>";
/*
1. $separator (delimitador): El carácter o texto donde se cortará el string. En tu caso, es '|'.

2. $string (la cadena): El string que se va a dividir. En tu caso, es ' A | B | C | D | E | F '.

3. $limit (límite): Este es un parámetro opcional que controla el número máximo de elementos que tendrá el array resultante.


*/
 

print_r("\n");
$str = ' A | B | C | D | E | F  ';


print_r("=====  (limites positivos) =====" . "\n");


//limit con valor 0 (El Caso Especia. Aquí está el punto clave que causa la confusión. Según la documentación oficial de PHP, un limit de 0 es un caso especial que es tratado exactamente como un limit de 1.             No significa "sin límite" ni "cero elementos". PHP simplemente lo interpreta como si hubieras escrito 1
print ('limit  0 ') . "\n";
print_r(explode('|', $str, 0));
print_r("\n");

 

//Cuando se usa  limit = 1, le estás diciendo a PHP: "Divide este string, pero devuélveme un array que tenga, como máximo, un solo elemento".
print ('limit 1') . "\n";
print_r(explode('|', $str, 1));
print_r("\n");


print ('limit 2') . "\n";
print_r(explode('|', $str, 2));
print_r("\n");

print ('limit 3') . "\n";
print_r(explode('|', $str, 3));
print_r("\n");

print ('limit 4') . "\n";
print_r(explode('|', $str, 4));
print_r("\n");
echo"<br><br>";


print ('limit 5 ') . "\n";
print_r(explode('|', $str, 5));
echo "<br><br><br>";


print ('limit 6 ') . "\n";
print_r(explode('|', $str, 6));
echo "<br><br><br>";




/*
 Explicación del Límite Negativo
Cuando usas un limit negativo (por ejemplo, -N), le estás diciendo a PHP:

"Divide el string en todos los delimitadores que encuentres, pero NO incluyas los últimos N elementos en el resultado final."

Es como un filtro para descartar las partes del final.



Resumen Comparativo:
limit positivo (> 1): Controla el máximo de elementos. El último elemento contiene el resto del string sin dividir.

limit 1 o 0: Caso especial. Devuelve un array con un solo elemento (el string original).

limit negativo (< 0): Divide por completo y luego descarta el número de elementos del final que indique el límite.

Por eso limit = -6 sí produce un resultado diferente y coherente con su propia regla, a diferencia del caso especial de limit = 0.


*/
print_r("=====  (limites negativos) =====" . "\n");


print ('limit 0 ') . "\n";
print_r(explode('|', $str, 0));
print_r("\n");


print ('limit -1 ') . "\n";
print_r(explode('|', $str, -1));
print_r("\n");

print ('limit -2 ') . "\n";
print_r(explode('|', $str, -2));
print_r("\n");

print ('limit -3 ') . "\n";
print_r(explode('|', $str, -3));
print_r("\n");

print ('limit -4 ') . "\n";
print_r(explode('|', $str, -4));

print ('limit -5 ') . "\n";
print_r(explode('|', $str, -5));
echo "<br><br><br>";

print ('limit -6 ') . "\n";
print_r(explode('|', $str, -6));
echo "<br><br><br>";


print_r("</pre>");
