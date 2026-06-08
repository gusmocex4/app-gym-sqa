# Lista de Chequeo de Seguridad - FitZone

**Proyecto:** `app-gym-main` / FitZone  
**Objetivo:** guiar la inspeccion de seguridad del codebase PHP MVC, rutas, autenticacion, vistas, persistencia MongoDB, Docker y dependencias.  
**Fecha sugerida de uso:** 2026-05-25  

## Instrucciones de Uso

Para cada item marque:

- `[ ] Cumple`
- `[ ] No cumple`
- `[ ] N/A`

Registre siempre evidencia concreta: archivo, linea, comando, captura, salida de herramienta o prueba manual.

Severidad sugerida:

- **Critica:** permite acceso indebido, exposicion de datos sensibles o compromiso del sistema.
- **Alta:** debilita autenticacion, autorizacion, sesiones, entradas/salidas o secretos.
- **Media:** aumenta riesgo por configuracion, dependencias, logging o falta de controles.
- **Baja:** mejora defensiva, limpieza o documentacion de seguridad.

## 1. Alcance de Inspeccion

- [ ] Confirmar que el alcance incluye `public/index.php`, `Router.php`, `controllers/`, `models/`, `views/`, `includes/`, `Dockerfile`, `docker-compose*.yml`, `composer.json`, `package.json`, `package-lock.json`, `database.mongodb.json` y documentacion.
- [ ] Identificar rutas publicas, rutas autenticadas y rutas administrativas.
- [ ] Identificar todos los formularios POST y sus controladores.
- [ ] Identificar datos sensibles: password, token, email, roles, string de conexion MongoDB y variables de entorno.
- [ ] Confirmar si la persistencia oficial es MongoDB/Cosmos DB y si `database.sql` es legado.

## 2. Autenticacion

- [ ] Verificar que `/login` valida email y password antes de consultar la base de datos.
- [ ] Verificar que el password se compara con `password_verify()` y nunca con comparacion directa.
- [ ] Verificar que los passwords nuevos se guardan con `password_hash()` usando un algoritmo fuerte.
- [ ] Verificar que el registro de usuarios funciona sin errores fatales.
- [ ] Verificar que el email se valida con formato correcto y se normaliza antes de persistirlo.
- [ ] Verificar que no existe enumeracion de usuarios por mensajes demasiado especificos en login o recuperacion.
- [ ] Verificar que no hay credenciales reales o reutilizables para ambientes no locales en seeds, docs o configuracion.
- [ ] Verificar que las cuentas de prueba documentadas solo se pueden usar en local/QA.

**Archivos foco:** `controllers/LoginController.php`, `models/Usuario.php`, `database.mongodb.json`, `README.md`.

## 3. Autorizacion y Control de Acceso

- [ ] Verificar que las rutas de usuario requieren `$_SESSION['login'] === true`.
- [ ] Verificar que las rutas admin requieren login y rol admin.
- [ ] Verificar que visitantes no pueden acceder directamente a `/inicio-admin`, `/administrarPlanes`, `/administrarClases`, `/inicio-user`, `/Planes`, `/Suscripciones` o `/Clases`.
- [ ] Verificar que el rol admin no se puede modificar desde formularios de registro o parametros del cliente.
- [ ] Verificar que la autorizacion se aplica de forma centralizada o consistente en cada controlador protegido.
- [ ] Verificar que los redirects de acceso denegado usan `exit` despues de `header('Location: ...')`.
- [ ] Verificar que los endpoints no dependen solo de ocultar links en la UI.

**Archivos foco:** `Router.php`, `public/index.php`, `controllers/InicioAdminController.php`, `controllers/InicioUserController.php`, `controllers/LoginController.php`.

## 4. Sesiones

- [ ] Verificar que la sesion inicia antes de leer o escribir `$_SESSION`.
- [ ] Verificar que el ID de sesion se regenera despues de login exitoso con `session_regenerate_id(true)`.
- [ ] Verificar que logout limpia `$_SESSION`, destruye la sesion y redirige.
- [ ] Verificar que las cookies de sesion se configuran con `HttpOnly`.
- [ ] Verificar que las cookies de sesion se configuran con `Secure` en HTTPS.
- [ ] Verificar que las cookies de sesion se configuran con `SameSite=Lax` o `Strict`.
- [ ] Verificar que no se almacenan datos sensibles innecesarios en sesion.
- [ ] Verificar que las rutas protegidas no funcionan despues de logout.

**Archivos foco:** `Router.php`, `controllers/LoginController.php`, configuracion PHP/Apache/Docker.

## 5. CSRF

- [ ] Verificar que cada formulario POST incluye un token CSRF oculto.
- [ ] Verificar que el token CSRF se genera con `random_bytes()` o equivalente seguro.
- [ ] Verificar que el token CSRF esta ligado a la sesion.
- [ ] Verificar que cada controlador POST valida el token antes de procesar datos.
- [ ] Verificar que los tokens se invalidan o rotan cuando corresponde.
- [ ] Verificar que fallos CSRF no ejecutan acciones parciales.

**Formularios foco:** `views/auth/login.php`, `views/auth/crear-cuenta.php`, `views/auth/olvide-password.php`.

## 6. Validacion de Entrada

- [ ] Verificar que todos los campos de `$_POST` se validan por presencia, tipo, longitud y formato.
- [ ] Verificar que `email` usa `filter_var($email, FILTER_VALIDATE_EMAIL)`.
- [ ] Verificar que `nombre` y `apellido` tienen longitud maxima y caracteres permitidos definidos.
- [ ] Verificar que `password` tiene minimo de longitud y politica acorde al riesgo.
- [ ] Verificar que no se acepta `admin`, `id`, `token` u otros campos sensibles desde el cliente al crear cuenta.
- [ ] Verificar que las consultas MongoDB no permiten inyeccion por operadores enviados desde el cliente.
- [ ] Verificar que los IDs usados en consultas se validan antes de llegar al modelo.
- [ ] Verificar que no se usan directamente `$_GET`, `$_POST` o `$_REQUEST` en vistas.

**Archivos foco:** `controllers/LoginController.php`, `models/Usuario.php`, `models/ActiveRecord.php`.

## 7. Salida HTML y XSS

- [ ] Verificar que toda salida dinamica en vistas pasa por `s()` o `htmlspecialchars()`.
- [ ] Verificar que `s()` usa `ENT_QUOTES | ENT_SUBSTITUTE` y `UTF-8`.
- [ ] Verificar que mensajes de alerta escapan tanto clase/tipo como mensaje.
- [ ] Verificar que valores re-renderizados de formularios estan escapados.
- [ ] Verificar que no se imprime contenido de error interno al navegador.
- [ ] Verificar que las vistas no insertan datos del usuario en JavaScript inline sin codificacion segura.
- [ ] Verificar que se puede agregar CSP en Apache/app sin romper assets actuales.

**Archivos foco:** `views/templates/alertas.php`, `views/**/*.php`, `includes/funciones.php`, `views/layout.php`.

## 8. Recuperacion de Password y Tokens

- [ ] Verificar si la recuperacion de password esta implementada o explicitamente deshabilitada.
- [ ] Verificar que los tokens se generan con `bin2hex(random_bytes(32))` o equivalente.
- [ ] Verificar que los tokens tienen expiracion.
- [ ] Verificar que los tokens se invalidan despues de uso.
- [ ] Verificar que no se revela si un email existe durante recuperacion.
- [ ] Verificar que no se imprime un token en pantalla, logs o URL innecesariamente.
- [ ] Verificar que cualquier futuro envio de email use enlaces HTTPS.

**Archivos foco:** `controllers/LoginController.php`, `models/Usuario.php`, rutas `/olvide` y `/recuperar`.

## 9. Persistencia y Base de Datos

- [ ] Verificar que `MONGODB_URI` no esta hardcodeado en el repositorio.
- [ ] Verificar que la app falla de forma controlada si falta `MONGODB_URI` o la extension MongoDB.
- [ ] Verificar que el nombre de base de datos y coleccion no pueden manipularse desde request HTTP.
- [ ] Verificar que existe indice unico para `email` en la coleccion `usuarios`.
- [ ] Verificar que los passwords seed estan hasheados y no en texto plano.
- [ ] Verificar que no se registran strings de conexion completos con usuario/password en logs.
- [ ] Verificar que los errores de MongoDB no se exponen al usuario final.
- [ ] Verificar que datos legacy SQL no inducen configuraciones inseguras o inconsistentes.

**Archivos foco:** `includes/database.php`, `models/ActiveRecord.php`, `database.mongodb.json`, `database.sql`, docs de despliegue.

## 10. Secretos y Configuracion

- [ ] Verificar que `.env`, secretos, credenciales, llaves y connection strings no estan versionados.
- [ ] Verificar que `azure/appsettings.example.env` contiene solo placeholders.
- [ ] Verificar que `.gitignore` cubre `.env`, `.env.*`, `.DS_Store`, dumps, logs y artefactos sensibles.
- [ ] Verificar que Docker Compose no define secretos reales por defecto.
- [ ] Verificar que el README no contiene credenciales de produccion.
- [ ] Verificar que variables requeridas estan documentadas.
- [ ] Verificar que configuraciones de local/QA/prod estan separadas.

**Archivos foco:** `.gitignore`, `.dockerignore`, `docker-compose.yml`, `docker-compose.azure.yml`, `azure/appsettings.example.env`, `README.md`.

## 11. Docker, Apache y Hardening de Runtime

- [ ] Verificar que Apache sirve solo desde `public/`.
- [ ] Verificar que `vendor/`, `src/`, `database*.json/sql`, docs y archivos internos no son servidos publicamente.
- [ ] Verificar que el contenedor no copia archivos innecesarios por falta de `.dockerignore`.
- [ ] Verificar que no se ejecuta el contenedor con privilegios elevados innecesarios.
- [ ] Verificar que `display_errors` esta desactivado en ambientes no dev.
- [ ] Verificar que logs de error no exponen secretos.
- [ ] Verificar que HTTPS se fuerza en el ambiente de despliegue.
- [ ] Verificar headers de seguridad: `Content-Security-Policy`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` y proteccion de clickjacking.

**Archivos foco:** `Dockerfile`, `docker-compose.yml`, `public/.htaccess`, configuracion de Azure/Apache.

## 12. Dependencias y Supply Chain

- [ ] Ejecutar `composer audit` si la version de Composer lo soporta.
- [ ] Ejecutar `npm audit --omit=optional`.
- [ ] Verificar que `composer.lock` y `package-lock.json` estan presentes y actualizados.
- [ ] Verificar que no se versiona `node_modules/`.
- [ ] Verificar que `vendor/` no contiene paquetes modificados manualmente.
- [ ] Verificar que dependencias abandonadas o vulnerables tienen plan de actualizacion.
- [ ] Verificar que scripts de build no descargan codigo remoto no confiable.
- [ ] Verificar que el build usa `npm ci` en CI/deploy para reproducibilidad.

**Archivos foco:** `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `gulpfile.js`, `Dockerfile`.

## 13. Logging, Errores y Observabilidad

- [ ] Verificar que errores de autenticacion no revelan detalles sensibles.
- [ ] Verificar que excepciones internas se registran de forma segura y no se imprimen al usuario.
- [ ] Verificar que logs no contienen password, token, cookies, connection strings o hashes innecesarios.
- [ ] Verificar que existe logging minimo para fallos de login, fallos DB y errores de autorizacion.
- [ ] Verificar que el manejo de 404 no expone rutas internas ni stack traces.
- [ ] Verificar que las respuestas de error usan codigo HTTP apropiado cuando sea posible.

**Archivos foco:** `Router.php`, `includes/database.php`, `models/ActiveRecord.php`, controladores.

## 14. Pruebas de Seguridad Recomendadas

- [ ] Probar acceso directo a rutas admin sin sesion.
- [ ] Probar acceso directo a rutas usuario sin sesion.
- [ ] Probar acceso a rutas admin con sesion de usuario no admin.
- [ ] Probar login con credenciales invalidas.
- [ ] Probar login con password correcto y verificar regeneracion de sesion.
- [ ] Probar registro con email invalido.
- [ ] Probar registro enviando campo `admin=1` desde el cliente.
- [ ] Probar payload XSS en nombre/apellido/email y verificar escape en vistas.
- [ ] Probar envio POST sin token CSRF.
- [ ] Probar envio POST con token CSRF invalido.
- [ ] Probar recuperacion de password y confirmar que no revela existencia de email.
- [ ] Probar app sin `MONGODB_URI` y confirmar error controlado.

## 15. Comandos de Evidencia

```bash
rg -n "session_|\\$_POST|\\$_GET|\\$_REQUEST|header\\(|password_|csrf|token|admin|login" controllers models views includes public Router.php
```

```bash
npm audit --omit=optional
```

```bash
composer audit
```

```bash
docker compose config
```

```bash
docker compose up --build
```

```bash
find . -type f \( -name ".env*" -o -name "*.pem" -o -name "*.key" -o -name "*.log" -o -name "*.sql" -o -name "*.json" \) -maxdepth 4
```

## 16. Registro de Hallazgos

| ID | Item | Severidad | Evidencia | Riesgo | Recomendacion | Estado |
|---|---|---|---|---|---|---|
| SEC-01 |  |  |  |  |  |  |
| SEC-02 |  |  |  |  |  |  |
| SEC-03 |  |  |  |  |  |  |
| SEC-04 |  |  |  |  |  |  |
| SEC-05 |  |  |  |  |  |  |

## 17. Criterios Minimos Para Aprobar

- [ ] No hay rutas administrativas accesibles sin rol admin.
- [ ] No hay rutas de usuario accesibles sin login.
- [ ] Login regenera sesion y logout invalida sesion.
- [ ] Formularios POST tienen CSRF.
- [ ] Salida dinamica esta escapada.
- [ ] Passwords y tokens usan primitivas criptograficas seguras.
- [ ] No hay secretos reales en el repositorio.
- [ ] Dependencias criticas no tienen vulnerabilidades conocidas sin mitigacion.
- [ ] La app no expone errores internos en navegador.
- [ ] Docker/Apache sirven solo contenido publico.
