# Chat Conversation

Note: _This is purely the output of the chat conversation and does not contain any raw data, codebase snippets, etc. used to generate the output._

### User Input

hola gemini en for resumida podrias decirmo  el  flujo de esjecucion de el proyecto api por los momentos

*Listed directory [api](file:///c:/xampp/htdocs/api) *

*Listed directory [public](file:///c:/xampp/htdocs/api/public) *

*Viewed [index.php](file:///c:/xampp/htdocs/api/public/index.php) *

*Viewed [user.php](file:///c:/xampp/htdocs/api/src/routes/user.php) *

### Planner Response

El flujo de ejecución de tu proyecto API es un **Front Controller** clásico. Aquí te resumo los pasos que sigue cada petición que llega a tu servidor:

1.  **Punto de Entrada Único ([public/index.php](cci:7://file:///c:/xampp/htdocs/api/public/index.php:0:0-0:0))**
    *   Todas las peticiones (independientemente de la URL) son redirigidas a este archivo (probablemente mediante [.htaccess](cci:7://file:///c:/xampp/htdocs/api/.htaccess:0:0-0:0)).
    *   Es el "portero" que recibe todo el tráfico.

2.  **Carga de Dependencias y Configuración**
    *   **Autoload**: Carga las librerías de Composer ([vendor/autoload.php](cci:7://file:///c:/xampp/htdocs/api/vendor/autoload.php:0:0-0:0)).
    *   **Base de Datos**: Establece la conexión ([src/DB/dataDB.php](cci:7://file:///c:/xampp/htdocs/api/src/DB/dataDB.php:0:0-0:0)).
    *   **Constantes**: Carga códigos de estado HTTP personalizados ([public/codigosEstado.php](cci:7://file:///c:/xampp/htdocs/api/public/codigosEstado.php:0:0-0:0)).
    *   **Logs**: Activa el sistema de registro de errores (`ErrorLog::activateErrorLog()`).

3.  **Análisis y Validación (Enrutamiento)**
    *   **Definición de Rutas**: Tienes un array `$routes` que actúa como "lista blanca" (actualmente solo permite `user` y `auth`).
    *   **Lectura de la Petición**: Obtiene el método HTTP (`GET`, `POST`, etc.) y la URL solicitada.
    *   **Validaciones de Seguridad**:
        1.  ¿Se pidió una ruta? (Si no, error 400).
        2.  ¿La ruta existe en `$routes`? (Si no, error 404).
        3.  ¿El método HTTP está permitido para esa ruta? (Si no, error 405).

4.  **Delegación (Despacho)**
    *   Si pasa las validaciones, el script busca y carga dinámicamente el archivo correspondiente en `src/routes/` (por ejemplo, `src/routes/user.php`).
    *   Aquí es donde se ejecutaría la lógica específica de ese endpoint (controladores, modelos, etc.).

5.  **Respuesta o Manejo de Errores**
    *   Si todo va bien, el archivo de ruta genera la salida.
    *   Si ocurre cualquier excepción en el proceso, el bloque `try...catch` global la captura y devuelve una respuesta JSON con error 500, evitando que el usuario vea errores de PHP "crudos".