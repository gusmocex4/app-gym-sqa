# Informe de Inspeccion Formal y Walkthrough - FitZone

## 1. Formal Software Inspection

### Inspection Information

**Project:** FitZone / `app-gym-main`  
**Artifact Inspected:** repositorio completo de aplicacion PHP MVC, vistas, controladores, modelos, configuracion Docker, pipeline frontend y artefactos de datos.  
**Inspection Scope:** seguridad, confiabilidad, mantenibilidad, eficiencia de desempeno y correccion funcional, usando IEEE 1028, ISO/IEC/IEEE 29119 e ISO/IEC 25010 como referencia.  
**Inspection Date:** 2026-05-30  
**Inspection Type:** revision estatica formal enfocada en encontrar defectos.  
**Approximate Source Size:** 1.705 lineas revisadas en PHP, SCSS y JS fuente, excluyendo `vendor/`, `node_modules/` y assets compilados.  

### Defect Log

| ID | File | Lines | Quality Attribute | Severity | Description |
|---|---|---:|---|---|---|
| INS-01 | `controllers/LoginController.php` | 88-95 | Functional Suitability / Reliability | Critical | El flujo de registro llama a `$usuario->sincronizar($_POST)`, pero ese metodo no existe en `Usuario` ni en `ActiveRecord`. El alta de usuarios falla en ejecucion. |
| INS-02 | `Router.php`; `public/index.php` | 23-31; 39-48 | Security | Critical | Las rutas protegidas solo estan comentadas y no existe verificacion real de login o rol. Un visitante puede invocar rutas admin y de usuario directamente. |
| INS-03 | `controllers/LoginController.php` | 21-33 | Security | High | Despues de autenticar correctamente no se regenera el ID de sesion. Esto deja riesgo de session fixation. |
| INS-04 | `views/auth/login.php`; `views/auth/crear-cuenta.php`; `views/auth/olvide-password.php` | 14-28; 13-31; 13-22 | Security | High | Los formularios POST no incluyen token CSRF ni se observa validacion CSRF en los controladores. |
| INS-05 | `views/templates/alertas.php` | 6-7 | Security | High | Las alertas imprimen `$key` y `$mensaje` sin escape HTML. Si algun mensaje incorpora datos externos, existe riesgo de XSS reflejado o almacenado. |
| INS-06 | `models/Usuario.php` | 29-36; 48-55; 70-78 | Security / Functional Suitability | High | La validacion de cuenta es incompleta: no valida formato de email, no normaliza email y el modelo permite persistir `admin`, `token` e `id` derivados de datos de entrada si se sincronizan sin lista blanca. |
| INS-07 | `models/Usuario.php` | 66-68 | Security | Medium | `crearToken()` usa `uniqid()`, que no es criptograficamente seguro para recuperacion de password o confirmacion de cuenta. |
| INS-08 | `controllers/LoginController.php`; `public/index.php` | 71-85; 27-30 | Functional Suitability / Reliability | Medium | La recuperacion de password esta expuesta como ruta, pero no esta implementada. `/recuperar` imprime texto plano y no ejecuta un flujo seguro. |
| INS-09 | `includes/database.php`; `models/ActiveRecord.php` | 7-17; 45-57 | Reliability / Maintainability | Medium | Si falta la extension MongoDB o `MONGODB_URI`, la aplicacion continua con `$db = null` y produce alertas genericas. Esto degrada silenciosamente el sistema y dificulta diagnostico. |
| INS-10 | `package.json`; `package-lock.json` | N/A | Security / Maintainability | High | `npm audit --omit=optional` reporta 96 vulnerabilidades en dependencias frontend: 41 high, 51 moderate y 4 low. Afecta principalmente dependencias de build como `browser-sync`, `gulp`, `cssnano`, `gulp-webp` y transitorios. |
| INS-11 | `Dockerfile`; `.dockerignore`; `docker-compose.yml` | 35-39; N/A; 9-14 | Security / Portability | Medium | El Dockerfile copia todo el contexto a `/var/www/html`; si `.dockerignore` no excluye artefactos sensibles, archivos internos pueden entrar en la imagen. En Compose, `MONGODB_URI` queda vacio si no se define. |
| INS-12 | Repository metadata | N/A | Maintainability / Portability | Low | Se encontraron archivos `.DS_Store` en raiz, `public/`, `views/`, `src/` y `src/scss/`. Son artefactos locales que no aportan al producto y pueden contaminar entregas. |

### Impact and Best-Practice Rationale

- **INS-01** bloquea una funcion principal del sistema. Un flujo de registro roto impide validar usuarios nuevos y reduce la confiabilidad del producto.
- **INS-02** viola control de acceso basico. Segun buenas practicas de seguridad web, autorizacion debe aplicarse en servidor, no depender de enlaces ocultos o navegacion esperada.
- **INS-03** deja abierta la posibilidad de fijacion de sesion. Despues de login exitoso, la sesion debe regenerarse para evitar reutilizacion de identificadores previos.
- **INS-04** incumple defensa contra solicitudes forjadas. Todo endpoint que cambia estado mediante POST debe validar un token ligado a la sesion.
- **INS-05** viola codificacion segura de salida. Toda salida dinamica en HTML debe escaparse antes de renderizarse.
- **INS-06** mezcla validacion insuficiente con riesgo de asignacion masiva. Campos como `admin` y `token` no deben ser controlables desde el cliente.
- **INS-07** usa una primitiva predecible para tokens. Los tokens de seguridad deben generarse con aleatoriedad criptografica.
- **INS-08** expone una funcion incompleta. Las rutas publicas no deben ofrecer flujos de seguridad parciales o confusos.
- **INS-09** reduce observabilidad y confiabilidad. Fallar de forma clara ante configuracion obligatoria faltante facilita operacion y pruebas.
- **INS-10** introduce riesgo de supply chain. Aunque muchas dependencias sean de desarrollo, siguen afectando reproducibilidad y seguridad del pipeline.
- **INS-11** debilita portabilidad y hardening de despliegue. El contexto Docker debe ser minimo y los secretos deben inyectarse de forma controlada.
- **INS-12** es deuda de higiene del repositorio. No es critico, pero afecta calidad de entrega.

### Metrics

- **Total defects:** 12
- **Defects by severity:**
  - Critical: 2
  - High: 5
  - Medium: 4
  - Low: 1
- **Defect density:** 12 defectos / 1,705 KLOC = **7,04 defectos/KLOC**
- **Top recurring defect categories:**
  - Security controls missing: autorizacion, CSRF, sesion, XSS.
  - Incomplete functional flows: registro y recuperacion de password.
  - Configuration and dependency risk: MongoDB env, Docker context, npm audit.
  - Validation and data handling: email, role, token.

### Verification Evidence

Comandos ejecutados:

```bash
find controllers models views includes public src -type f \( -name '*.php' -o -name '*.js' -o -name '*.scss' \) -not -path '*/build/*' -print0 | xargs -0 wc -l
```

Resultado: 1.705 lineas fuente revisadas.

```bash
npm audit --omit=optional --audit-level=low --json
```

Resultado: 96 vulnerabilidades, distribuidas en 41 high, 51 moderate y 4 low.

```bash
docker compose config
```

Resultado: la configuracion resuelve, pero muestra `MONGODB_URI: ""` cuando la variable no esta definida.

```bash
composer audit
```

Resultado: no ejecutado; `composer` no esta disponible en el PATH local.

### Inspection Verdict

**FAIL**

El sistema puede aceptarse como prototipo academico/local, pero no debe considerarse listo para produccion ni para una evaluacion de seguridad aprobatoria hasta corregir los defectos criticos y altos.

### Follow-up Actions

| Action | Responsible | Priority |
|---|---|---|
| Implementar o reemplazar `sincronizar()` para recuperar el flujo de registro. | Developer | High |
| Agregar guard centralizado para rutas autenticadas y rutas admin. | Developer | High |
| Regenerar sesion tras login exitoso y configurar cookies `HttpOnly`, `Secure` y `SameSite`. | Developer | High |
| Implementar CSRF en todos los formularios POST. | Developer | High |
| Escapar alertas y toda salida dinamica con una funcion `s()` robusta. | Developer | High |
| Validar y normalizar email; impedir asignacion de `admin`, `token` o `id` desde el cliente. | Developer | High |
| Reemplazar `uniqid()` por `bin2hex(random_bytes(32))` para tokens. | Developer | Medium |
| Implementar o retirar temporalmente recuperacion de password. | Developer / Product Owner | Medium |
| Actualizar dependencias npm y revisar cambios breaking. | Developer | Medium |
| Endurecer Docker con `.dockerignore`, variables obligatorias y build reproducible. | Developer / DevOps | Medium |

---

## 2. Walkthrough Review

### Session Header

**Artifact Reviewed:** FitZone PHP MVC codebase.  
**Duration:** 90 minutos estimados.  
**Participants:** Inspector de seguridad, revisor de arquitectura, revisor de calidad, tester funcional.  
**Author:** Equipo desarrollador de FitZone.  
**Recorder:** Equipo SQA.  
**Review Type:** walkthrough segun IEEE 1028, orientado a comprension, arquitectura, mantenibilidad y deuda tecnica.  

### Checklist Results

| Item | Compliant (Yes/No) | Observation | Priority |
|---|---|---|---|
| Arquitectura general entendible | Yes | La estructura MVC es simple: `public/index.php` define rutas, `Router.php` despacha, `controllers/` gestionan casos de uso, `models/` acceden a datos y `views/` renderizan HTML. | Medium |
| Organizacion de carpetas clara | Yes | Las carpetas principales son reconocibles. `src/` contiene fuentes frontend y `public/build/` contiene assets compilados. | Low |
| Separacion de responsabilidades | No | El router inicia sesion, decide rutas y renderiza vistas. Falta una capa clara para middleware/guards de autenticacion. | High |
| Convenciones de nombres consistentes | No | Hay mezcla de nombres en espanol e ingles, rutas con mayusculas (`/Planes`) y nombres de proyecto heredados como `appsalon`. | Medium |
| Flujo de autenticacion legible | Yes | El flujo de login se puede seguir, pero contiene responsabilidades mezcladas: validacion, consulta, sesion y redireccion en el mismo metodo. | Medium |
| Manejo de errores comprensible | No | Errores de base de datos se degradan a alertas genericas; 404 imprime texto plano; no hay codigos HTTP ni vista de error. | Medium |
| Testabilidad | No | No hay suite de pruebas ni separacion suficiente para probar rutas, guardas, modelos y controladores de forma aislada. | High |
| Documentacion basica | Yes | README describe stack, rutas y limitaciones. Tambien existe documentacion de Azure. | Medium |
| Cumplimiento de SOLID | Partial | La aplicacion es pequena, pero algunos metodos concentran demasiadas responsabilidades y no hay interfaces/repositorios para persistencia. | Medium |
| Mantenibilidad frontend | Partial | SCSS esta modularizado, pero algunos layouts usan grids fijos y tamanos grandes sin estrategia responsive suficiente. | Low |
| Portabilidad de despliegue | Partial | Docker existe y apunta a `public/`, pero depende de variables externas y copia todo el contexto. | Medium |
| Claridad del modelo de datos | Partial | El codigo actual usa MongoDB, pero conviven `database.sql` y `database.mongodb.json`, lo que puede confundir al equipo. | Medium |

### Discussion

El sistema es facil de recorrer por su tamano y por la estructura MVC basica. La ruta de entrada esta centralizada en `public/index.php`, y los controladores principales permiten comprender rapidamente los casos de uso: landing, login, registro, recuperacion, dashboard de admin y dashboard de usuario.

La principal dificultad de comprension esta en los limites de responsabilidad. `Router.php` no solo despacha rutas, tambien inicia sesion y renderiza vistas. La proteccion de rutas esta comentada, por lo que no queda claro si el control de acceso pertenece al router, a los controladores o a una futura capa de middleware.

El flujo de autenticacion es lineal y entendible, pero el metodo `LoginController::login()` concentra validacion, consulta, verificacion de password, escritura de sesion y redireccion. Para un prototipo es aceptable; para mantenimiento convendria separar validacion, autenticacion y autorizacion.

La persistencia muestra una decision de migracion hacia MongoDB/Cosmos DB, pero aun quedan rastros SQL. Esto no rompe por si mismo la arquitectura, pero aumenta ambiguedad para nuevos revisores y para despliegues.

En frontend, la modularizacion SCSS ayuda a navegar por secciones. Sin embargo, hay estilos con medidas fijas y layouts que podrian dificultar adaptacion movil. No fue el foco principal de esta revision, pero se considera deuda tecnica de mantenibilidad.

### Suggested Improvements

1. Introducir una funcion o clase de guardas para rutas: visitante, usuario autenticado y admin.
2. Separar el flujo de autenticacion en metodos mas pequenos: validar input, buscar usuario, verificar credenciales, iniciar sesion y redirigir.
3. Agregar pruebas automatizadas para login, registro, rutas protegidas, logout y recuperacion de password.
4. Documentar una matriz de roles y permisos.
5. Unificar la decision de persistencia: MongoDB/Cosmos DB como fuente oficial o soporte explicito para SQL.
6. Agregar una vista de error 404/403 y codigos HTTP correctos.
7. Reducir ambiguedad de nombres heredados (`appsalon`, SQL legacy, rutas con mayusculas).
8. Crear un checklist de despliegue que obligue a definir `MONGODB_URI` y secretos antes de levantar la app.
9. Revisar SCSS con enfoque mobile-first en secciones de contacto, servicios, video y login.
10. Mantener `public/` como unico DocumentRoot y excluir archivos internos del contexto Docker.

### Follow-up Actions

| Action | Responsible Party | Priority |
|---|---|---|
| Definir matriz de roles y rutas protegidas. | Product Owner / Developer | High |
| Refactorizar `Router.php` para soportar guards o middleware simple. | Developer | High |
| Refactorizar `LoginController` en metodos pequenos y testeables. | Developer | Medium |
| Crear pruebas para autenticacion y autorizacion. | QA / Developer | High |
| Eliminar o marcar como legado `database.sql`. | Developer | Medium |
| Agregar guia breve de configuracion local y variables requeridas. | Developer / DevOps | Medium |
| Revisar naming de rutas para usar convenciones consistentes. | Developer | Low |

---

## 3. ISO/IEC 25010 Quality Assessment Matrix

| Quality Characteristic | Score 1-5 | Evidence | Risks | Recommendations |
|---|---:|---|---|---|
| Functional Suitability | 2 | Login base existe, pero registro falla por metodo inexistente y recuperacion no esta implementada. | Funciones principales incompletas. | Corregir registro e implementar/retirar recuperacion. |
| Performance Efficiency | 3 | App pequena, sin consultas complejas; frontend compilado. | No hay mediciones ni pruebas de carga. Dependencias de build obsoletas. | Agregar pruebas basicas de tiempo de respuesta y revisar pipeline. |
| Compatibility | 3 | Docker y Apache facilitan entorno comun. | Dependencia fuerte de extension MongoDB y variables externas. | Documentar requisitos y validar entorno al iniciar. |
| Usability | 3 | Vistas simples y flujo de login comprensible. | Textos incompletos, rutas con mayusculas y responsive limitado. | Corregir copy y revisar layout movil. |
| Reliability | 2 | Manejo de errores DB existe pero degrada silenciosamente. | Fallos de configuracion pueden pasar a runtime sin diagnostico claro. | Fallar temprano en configuraciones obligatorias y agregar pruebas. |
| Security | 1 | Password hashing presente. | Sin guards, sin CSRF, sin regeneracion de sesion, XSS posible en alertas. | Priorizar controles criticos de autenticacion, autorizacion y salida. |
| Maintainability | 2 | MVC simple y archivos pequenos. | Responsabilidades mezcladas, sin tests, nombres inconsistentes, datos SQL/Mongo mezclados. | Refactorizar router/controladores y agregar pruebas. |
| Portability | 3 | Dockerfile y Compose disponibles. | `MONGODB_URI` puede quedar vacio; contexto Docker puede copiar archivos innecesarios. | Endurecer Docker y validar variables obligatorias. |

**Overall quality score:** 2,4 / 5  
**Quality conclusion:** aceptable como prototipo academico, no aceptable para produccion sin acciones correctivas.
