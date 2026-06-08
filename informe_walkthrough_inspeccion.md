# Informe de Walkthrough e Inspeccion del Codebase FitZone

**Proyecto evaluado:** `app-gym-main` / FitZone  
**Fecha de revision:** 2026-05-25  
**Artefactos revisados:** codigo PHP MVC, vistas, rutas, capa de datos, configuracion Docker, pipeline Gulp/Sass, archivos de datos semilla y documentacion del repositorio.  
**Material metodologico usado:** PDFs de clase sobre walkthrough e inspeccion de calidad del software, basados en ISO/IEC/IEEE 29119-1, 29119-2 y 29119-4.

## 1. Resumen Ejecutivo

El codebase implementa una aplicacion web de gimnasio en PHP 8 con una arquitectura MVC simple, rutas publicas, flujo de autenticacion, vistas de usuario/admin, persistencia MongoDB-compatible y pipeline frontend con Gulp/Sass.

La revision encontro un estado funcional parcial. El flujo de login esta estructurado, pero hay defectos de severidad alta que afectan seguridad, registro de usuarios y reproducibilidad del build. El hallazgo mas critico es que el registro de cuenta llama a un metodo inexistente (`sincronizar()`), lo que impide crear usuarios. Tambien existen rutas administrativas sin proteccion de autenticacion/autorizacion, ausencia de CSRF en formularios, falta de regeneracion de sesion al iniciar sesion, pipeline Gulp no ejecutable por permisos y dependencias npm con vulnerabilidades reportadas.

**Resultado global:** aprobado solo como prototipo academico/local. No se recomienda usar en produccion sin corregir los defectos de seguridad, acceso y build.

## 2. Alcance de la Revision

Archivos y modulos revisados:

- `Router.php`
- `public/index.php`
- `includes/app.php`
- `includes/database.php`
- `includes/funciones.php`
- `models/ActiveRecord.php`
- `models/Usuario.php`
- `controllers/LoginController.php`
- `controllers/InicioAdminController.php`
- `controllers/InicioUserController.php`
- `controllers/PrincipalController.php`
- `views/layout.php`
- `views/templates/alertas.php`
- `views/auth/*.php`
- `src/scss/**/*.scss`
- `gulpfile.js`
- `Dockerfile`
- `docker-compose.yml`
- `package.json`
- `composer.json`
- `database.mongodb.json`
- `database.sql`

Aproximadamente se inspeccionaron **1.983 lineas** entre codigo, vistas, estilos y configuracion.

## 3. Metodologia Aplicada

### 3.1 Walkthrough

Segun el material de walkthrough, esta tecnica es una revision estatica informal, guiada por el autor o por quien explica el artefacto, enfocada en comprension, alternativas de diseno, aclaracion de dudas y deteccion temprana de errores.

Para este informe se simulo el walkthrough con foco en:

- Comprender arquitectura y flujo principal.
- Identificar riesgos tempranos de diseno.
- Revisar consistencia entre rutas, controladores, modelos y vistas.
- Revisar viabilidad tecnica del despliegue local/Docker.
- Levantar dudas y acciones de seguimiento.

### 3.2 Inspeccion

Segun el material de inspeccion, esta tecnica es una revision estatica formal, con checklist riguroso, clasificacion de defectos y severidad. Para este informe se aplico inspeccion sobre codigo critico: autenticacion, rutas, persistencia, build y configuracion.

Escala de severidad usada:

- **5 - Critica:** rompe flujo principal, permite acceso indebido o impide operar el sistema.
- **4 - Alta:** defecto de seguridad/funcionalidad importante con impacto directo.
- **3 - Media:** inconsistencia tecnica o mantenibilidad que puede causar fallos.
- **2 - Baja:** problema menor de estilo, UX o limpieza.
- **1 - Informativa:** observacion sin impacto inmediato.

## 4. Walkthrough del Codebase

### 4.1 Arquitectura Observada

El proyecto usa una estructura MVC sencilla:

- `public/index.php` define rutas HTTP y asigna controladores.
- `Router.php` resuelve la ruta actual y renderiza vistas.
- `controllers/` contiene los casos de uso principales: landing, login, inicio admin e inicio usuario.
- `models/ActiveRecord.php` implementa acceso generico a MongoDB.
- `models/Usuario.php` modela usuarios, validacion, hashing y verificacion de password.
- `views/` contiene plantillas PHP.
- `src/scss/` contiene estilos fuente y `public/build/` contiene assets compilados.
- Docker intenta servir Apache con DocumentRoot en `public/`.

### 4.2 Flujo de Autenticacion Observado

1. El usuario accede a `/login`.
2. `LoginController::login()` recibe POST y crea un objeto `Usuario` con `$_POST`.
3. Se valida presencia de email/password.
4. Se busca usuario por email en MongoDB.
5. Se verifica password con `password_verify()`.
6. Se crean variables de sesion.
7. Si `admin === 1`, se redirige a `/inicio-admin`; de lo contrario a `/inicio-user`.

El flujo base es correcto en intencion, pero tiene controles incompletos: no hay regeneracion de ID de sesion, no hay CSRF, no hay proteccion centralizada de rutas y las vistas internas son accesibles si se conoce la URL.

### 4.3 Checklist de Walkthrough

| Item | Cumple | Observacion | Prioridad |
|---|---:|---|---:|
| Arquitectura entendible | Si | MVC simple y facil de seguir. | Media |
| Rutas principales definidas | Si | Landing, login, registro, recuperacion, admin y usuario estan declaradas. | Media |
| Requisitos trazables/completos | Parcial | README describe limitaciones, pero no hay historias de usuario ni matriz de trazabilidad. | Media |
| Login implementado | Parcial | Tiene verificacion de password, pero faltan controles de sesion y acceso. | Alta |
| Registro implementado | No | Llama a `sincronizar()` inexistente; el flujo falla en runtime. | Alta |
| Recuperacion de password | No | Solo muestra mensaje/stub. | Media |
| Rutas protegidas | No | El router tiene comentarios de proteccion, pero no aplica control real. | Alta |
| Persistencia consistente | Parcial | Codigo actual usa MongoDB, pero quedan artefactos SQL y documentacion mixta. | Media |
| Build frontend reproducible | No | `npm run gulp` falla por permisos del binario local. | Alta |
| Seguridad basica | Parcial | Hay hashing bcrypt, pero faltan CSRF, autorizacion y hardening de sesion. | Alta |
| Pruebas automatizadas | No | No se encontro suite de pruebas. | Media |
| Responsive/UX | Parcial | Hay SCSS modular, pero varios layouts usan grids fijos y fuentes grandes sin media queries aplicadas. | Media |

### 4.4 Dudas y Acuerdos de Walkthrough

- Se debe decidir si el proyecto queda definitivamente en MongoDB o si se conserva soporte MySQL. Actualmente hay mezcla de ambos artefactos.
- Se debe definir matriz de roles: admin, usuario autenticado y visitante.
- Se debe decidir si las rutas `/Planes`, `/Suscripciones`, `/Clases`, `/administrarPlanes` y `/administrarClases` seran paginas reales o placeholders.
- Se debe definir alcance de recuperacion de password: token, email, expiracion y almacenamiento seguro.
- Se debe corregir el pipeline Gulp antes de exigir evidencias visuales reproducibles.

## 5. Inspeccion Formal

### 5.1 Checklist de Inspeccion

| Categoria | Criterio | Resultado |
|---|---|---|
| Sintactico | El codigo no referencia metodos inexistentes | Falla: `sincronizar()` no existe. |
| Semantico | El flujo de registro crea usuarios validos | Falla por error fatal esperado. |
| Seguridad | Rutas internas protegidas por login y rol | Falla: no hay middleware/guard. |
| Seguridad | Formularios POST protegidos contra CSRF | Falla: no hay token CSRF. |
| Seguridad | Sesion endurecida despues de login | Falla: no se regenera ID. |
| Seguridad | Salida HTML escapada | Parcial: formularios usan `s()`, alertas no escapan. |
| Datos | Conexion DB maneja fallos explicitamente | Parcial: registra error, pero la app sigue en modo degradado. |
| Build | Pipeline frontend ejecutable | Falla: binario `gulp` sin permiso de ejecucion. |
| Dependencias | Dependencias sin vulnerabilidades relevantes | Falla: `npm audit` reporta 96 vulnerabilidades. |
| Mantenibilidad | Documentacion consistente con implementacion | Parcial: README habla de MongoDB, pero `database.sql` sigue presente. |
| Calidad | Pruebas automatizadas disponibles | Falla: no se encontro suite de pruebas. |

### 5.2 Defectos Encontrados

| ID | Severidad | Categoria | Evidencia | Descripcion | Accion recomendada |
|---|---:|---|---|---|---|
| INS-01 | 5 | Semantico/funcional | `controllers/LoginController.php:94` | El registro llama a `$usuario->sincronizar($_POST)`, pero no existe `sincronizar()` en `Usuario` ni `ActiveRecord`. El alta de usuarios fallara en runtime. | Implementar `sincronizar(array $args)` en `ActiveRecord` o cambiar a `new Usuario($_POST)`. Agregar prueba del flujo de registro. |
| INS-02 | 5 | Seguridad/autorizacion | `Router.php:23-31`, `public/index.php:39-48` | Las rutas protegidas estan solo comentadas. Cualquier visitante puede acceder directamente a `/inicio-admin`, `/administrarPlanes`, `/administrarClases`, `/inicio-user`, etc. | Agregar guard centralizado en router/controladores: exigir `$_SESSION['login']` y rol admin para rutas administrativas. |
| INS-03 | 4 | Seguridad/sesion | `controllers/LoginController.php:21-33` | Al autenticar correctamente no se llama a `session_regenerate_id(true)`. Esto deja riesgo de session fixation. | Regenerar ID de sesion inmediatamente despues de verificar password. |
| INS-04 | 4 | Seguridad/CSRF | `views/auth/login.php:14`, `views/auth/crear-cuenta.php:13`, `views/auth/olvide-password.php:13` | Los formularios POST no incluyen token CSRF. | Crear token por sesion, renderizar input oculto y validarlo antes de procesar POST. |
| INS-05 | 4 | Seguridad/XSS | `views/templates/alertas.php:6-7` | Las alertas imprimen `$key` y `$mensaje` sin escape HTML. Hoy los mensajes son internos, pero si en el futuro incluyen datos del usuario puede haber XSS. | Usar `s($key)` y `s($mensaje)`. |
| INS-06 | 3 | Datos/robustez | `includes/database.php:7-17`, `models/ActiveRecord.php:53-57` | Si falta la extension MongoDB o `MONGODB_URI`, la app continua con `$db = null` y luego devuelve alertas genericas. Esto dificulta diagnostico y pruebas. | Fallar temprano en entornos no dev o mostrar pagina de error configurada. Documentar variables obligatorias. |
| INS-07 | 3 | Seguridad/token | `models/Usuario.php:66-68` | `crearToken()` usa `uniqid()`, que no es criptograficamente seguro para recuperacion de password. | Usar `bin2hex(random_bytes(32))` y almacenar expiracion. |
| INS-08 | 3 | Funcionalidad incompleta | `controllers/LoginController.php:71-85`, `public/index.php:27-30` | Recuperacion de password no esta implementada y `/recuperar` solo imprime texto. | Implementar flujo completo o retirar ruta hasta tener funcionalidad. |
| INS-09 | 3 | Datos/validacion | `models/Usuario.php:29-36`, `models/Usuario.php:48-55` | La validacion de email es solo presencia; no normaliza email ni asegura unicidad a nivel base de datos. | Validar con `filter_var`, normalizar a lowercase y crear indice unico en MongoDB para `email`. |
| INS-10 | 3 | Build/reproducibilidad | `node_modules/.bin/gulp`, `gulpfile.js:63-65` | `npm run gulp -- --tasks-simple` falla con `Permission denied`; el binario local de Gulp no tiene permiso de ejecucion. | Regenerar `node_modules` con `npm ci` o corregir permisos. Evitar versionar `node_modules`. |
| INS-11 | 3 | Dependencias/seguridad | `package.json`, `package-lock.json` | `npm audit --omit=optional` reporto 96 vulnerabilidades: 41 high, 51 moderate, 4 low. | Actualizar dependencias principales (`browser-sync`, `gulp`, `cssnano`, `gulp-autoprefixer`) evaluando cambios breaking. |
| INS-12 | 3 | Mantenibilidad/datos | `database.sql`, `database.mongodb.json`, `README.md` | Hay coexistencia de seed SQL y MongoDB. El codigo actual usa MongoDB, pero `database.sql` puede inducir configuracion incorrecta. | Marcar SQL como legado o removerlo si el proyecto migro a MongoDB. |
| INS-13 | 2 | UX/contenido | `views/auth/login.php:25`, `views/auth/inicio-admin.php:18`, `views/auth/inicio-user.php:19` | Hay errores de texto: link sin cierre de signo de interrogacion y typo `deerminación`. | Corregir copy y revisar textos visibles. |
| INS-14 | 2 | Frontend/responsive | `src/scss/layout/_contacto.scss:4-8`, `src/scss/layout/_servicios.scss:8-10`, `src/scss/layout/_video.scss:10-17` | Layouts con grids fijos y tipografias grandes sin media queries aplicadas; riesgo de mala visualizacion movil. | Aplicar mixins responsive existentes o reglas mobile-first. |
| INS-15 | 2 | Limpieza/configuracion | `public/.DS_Store`, `src/.DS_Store`, `src/scss/.DS_Store` | Archivos `.DS_Store` aparecen en carpetas del proyecto, incluso bajo `public/`. | Agregar `.DS_Store` a `.gitignore` y limpiar artefactos del repo. |
| INS-16 | 2 | HTML/asset | `views/auth/principal.php:26`, `views/auth/inicio-admin.php:23`, `views/auth/inicio-user.php:24` | Las vistas referencian `build/vid/videoFlexiones.webm`, pero no se encontro ese archivo en `public/build/vid`. | Generar el `.webm` o retirar la referencia. |

### 5.3 Metricas de Inspeccion

- Lineas revisadas aproximadas: **1.983**.
- Defectos registrados: **16**.
- Defectos severidad 5: **2**.
- Defectos severidad 4: **3**.
- Defectos severidad 3: **7**.
- Defectos severidad 2: **4**.
- Densidad aproximada: **8,1 defectos/KLOC**.
- Hallazgos bloqueantes para produccion: **INS-01, INS-02, INS-03, INS-04, INS-10, INS-11**.

## 6. Evidencia de Verificacion Ejecutada

Comandos ejecutados durante la revision:

```bash
rg --files -g '!node_modules/**' -g '!vendor/**' -g '!public/build/**' -g '!build/**'
```

Resultado: permitio delimitar los archivos fuente principales.

```bash
npm run gulp -- --tasks-simple
```

Resultado: fallo con `node_modules/.bin/gulp: Permission denied`.

```bash
npm audit --omit=optional --json
```

Resultado: reporto 96 vulnerabilidades en dependencias npm: 41 high, 51 moderate y 4 low.

```bash
docker compose config
```

Resultado: la configuracion Docker resuelve, pero advierte que `MONGODB_URI` no esta definida y queda vacia.

No se ejecuto `php -l` porque el sistema local no tiene `php` disponible en PATH. Para completar la inspeccion dinamica se debe ejecutar dentro del contenedor Docker o instalar PHP localmente.

## 7. Priorizacion de Correcciones

### Prioridad Alta

1. Corregir `sincronizar()` inexistente para recuperar el flujo de registro.
2. Implementar proteccion de rutas y autorizacion admin/usuario.
3. Agregar `session_regenerate_id(true)` en login exitoso.
4. Agregar proteccion CSRF a todos los formularios POST.
5. Corregir pipeline Gulp (`node_modules/.bin/gulp` sin permiso de ejecucion).
6. Actualizar dependencias npm o definir plan de mitigacion.

### Prioridad Media

1. Implementar recuperacion real de password o retirar stubs.
2. Reemplazar `uniqid()` por tokens criptograficos.
3. Normalizar/validar emails y crear indice unico en base de datos.
4. Unificar documentacion y artefactos de persistencia MongoDB vs SQL.
5. Agregar pruebas automatizadas de login, registro, autorizacion y rutas.

### Prioridad Baja

1. Corregir textos visibles y typos.
2. Eliminar `.DS_Store` y reforzar `.gitignore`.
3. Corregir referencia a asset `.webm` inexistente.
4. Mejorar responsive de vistas principales.

## 8. Plan de Pruebas Recomendado

| Caso | Tipo | Resultado esperado |
|---|---|---|
| Registro con datos validos | Funcional | Crea usuario y redirige a `/login`. |
| Registro con email existente | Funcional | Muestra alerta sin crear duplicado. |
| Registro con password menor a 8 caracteres | Validacion | Muestra error de longitud. |
| Login admin valido | Funcional/seguridad | Regenera sesion y redirige a `/inicio-admin`. |
| Login usuario valido | Funcional/seguridad | Regenera sesion y redirige a `/inicio-user`. |
| Visitante accede a `/inicio-admin` | Seguridad | Redirige a `/login` o muestra 403. |
| Usuario no admin accede a `/administrarPlanes` | Seguridad | Respuesta 403 o redireccion autorizada. |
| POST sin CSRF | Seguridad | Request rechazado. |
| POST con CSRF valido | Seguridad | Request procesado. |
| Falta `MONGODB_URI` | Configuracion | Error claro de configuracion, no fallo silencioso. |
| `npm run gulp` | Build | Compila CSS/JS sin errores. |

## 9. Conclusion

El walkthrough muestra que el proyecto tiene una base entendible y una separacion MVC util para un prototipo academico. Sin embargo, la inspeccion formal identifica defectos que bloquean confiabilidad y seguridad: registro roto, ausencia de controles de acceso, falta de CSRF, sesion no endurecida y pipeline frontend no reproducible.

El siguiente incremento debe concentrarse en seguridad y flujo de usuario antes de agregar nuevas pantallas. Una vez corregidos los defectos de severidad 4 y 5, se recomienda ejecutar pruebas dinamicas en Docker con MongoDB configurado y agregar una suite minima de pruebas para evitar regresiones.
