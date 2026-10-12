# Requerimientos No Funcionales (RNF) del Sistema Guataparo Country Club

A partir del análisis de la arquitectura del proyecto, la documentación técnica y de defensa de grado, se han extraído los siguientes **Requerimientos No Funcionales** que sustentan el diseño y la infraestructura del sistema:

## 1. Seguridad (Security) y Control de Acceso
*   **Prevención de Inyecciones SQL (SQLi):** El sistema debe utilizar de manera estricta consultas preparadas y parametrizadas (`bindValue` / `bindParam`) a través de la capa de abstracción PDO para el 100% de las iteraciones con la base de datos.
*   **Protección contra falsificación de peticiones (CSRF):** Todas las solicitudes que alteren el estado (métodos POST) deben exigir la validación de un token criptográfico dinámico (`CsrfMiddleware`) único por sesión.
*   **Gestión Segura de Sesiones:** Las cookies de sesión del usuario deben contar forzosamente con los atributos de seguridad estandarizados (`HttpOnly=true`, `SameSite=Lax`, `secure` sobre HTTPS) y poseer un tiempo de vida estricto (ej. 7200 segundos).
*   **Control Dinámico de Acceso (RBAC):** La plataforma debe verificar en tiempo real, mediante un Front Controller, si el rol del usuario cuenta con los permisos necesarios para ejecutar o visualizar el recurso solicitado.
*   **Prevención IDOR:** El sistema debe comprobar a nivel backend que el objeto o recurso solicitado/modificado (ej. emisión de votos) corresponda activa e inequívocamente con el elector o usuario en sesión.

## 2. Rendimiento (Performance) y Eficiencia
*   **Baja Latencia de Ejecución:** El aplicativo backend (desarrollado en PHP 8 nativo, sin frameworks monolíticos pesados) debe procesar solicitudes optimizando al máximo el uso de la CPU y memoria RAM.
*   **Tiempo de Respuesta Biométrico:** La comunicación entre el Front-end y el Daemon Local de hardware (Bridge Python en puerto 8888) debe ejecutarse de forma prácticamente instatánea para agilizar la verificación de identidad presencial.

## 3. Fiabilidad (Reliability) y Transaccionalidad
*   **Transacciones ACID Estrictas:** Todas las operaciones de creación, edición o emisión de votos que involucren múltiples registros en la base de datos (MySQL/MariaDB) deben ejecutarse bajo un encapsulamiento transaccional (`beginTransaction`, `commit`, `rollBack`). Esto garantiza la atomicidad y previene la interrupción de procesos ("votos huérfanos").
*   **Tolerancia a Fallas Biométricas:** El algoritmo de verificación de huellas debe rechazar automáticamente *templates* inferiores a 100 bytes (para evadir lecturas sucias) y exigir obligatoriamente el enrolamiento verificable de, al menos, dos huellas distintas.

## 4. Mantenibilidad e Ingeniería del Software
*   **Arquitectura MVC y Autoloader:** El código fuente debe estructurarse obligatoriamente bajo el patrón Modelo-Vista-Controlador.
*   **Mínimas Dependencias Externas:** El desarrollo debe evitar dependencias excesivas de terceros para minimizar fallos por actualizaciones de cadena de suministros o vulnerabilidades introducidas por agentes externos.
*   **Centralización de Rutas:** El acceso general debe pasar ineludiblemente por `public/index.php` (Front Controller), impidiendo que se invoquen archivos o clases de forma directa o aislada por URL.

## 5. Portabilidad e Infraestructura
*   **Despliegue Configurable:** La plataforma debe permitir el desacoplamiento de credenciales y variables de entorno mediante un archivo de configuración estándar (`.env`).
*   **Compatibilidad Biométrica de Sistema Operativo:** El integrador o Daemon (Bridge del SDK Fritronic) debe proveer soporte activo para funcionar en entornos Windows locales bajo un puerto designado (8888).

## 6. Integración y Usabilidad
*   **Voto Remoto con Autocaducidad:** Los enlaces criptográficos despachados vía SMTP (utilizando TLS y servicios de correo como SMTP de Gmail) para procesos remotos, deben invalidarse/autodestruirse inmediatamente al momento de consumar un proceso único de sufragio.
*   **Inteligencia Artificial Empotrada:** La función de filtrado/búsqueda de sistema debe poseer un asistente equipado con PNL (Procesamiento de Lenguaje Natural) por patrones Regex embebidos, capaz de interceptar consultas informales para facilitar el UX general.
