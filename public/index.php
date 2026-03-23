<?php 

require_once __DIR__ . '/../includes/app.php';

use Controllers\InicioAdminController;
use Controllers\InicioUserController;
use Controllers\PrincipalController;
use Controllers\LoginController;
use MVC\Router;

$router = new Router();

//Pagina principal

$router->get('/',[PrincipalController::class, 'principal']);

//Iniciar sesion

$router->get('/login',[LoginController::class, 'login']);
$router->post('/login',[LoginController::class, 'login']);

$router->get('/logout',[PrincipalController::class, 'principal']);
$router->post('/logout',[PrincipalController::class, 'principal']);

//Recuperar password

$router->get('/olvide',[LoginController::class, 'olvide']);
$router->post('/olvide',[LoginController::class, 'olvide']);
$router->get('/recuperar',[LoginController::class, 'recuperar']);
$router->post('/recuperar',[LoginController::class, 'recuperar']);

//Crear cuenta

$router->get('/crear-cuenta',[LoginController::class, 'crearCuenta']);
$router->post('/crear-cuenta',[LoginController::class, 'crearCuenta']);

//Pagina inicio admin

$router->get('/inicio-admin',[InicioAdminController::class, 'inicioAdmin']);
$router->get('/administrarPlanes',[InicioAdminController::class, 'administrarPlanes']);
$router->get('/administrarClases',[InicioAdminController::class, 'administrarClases']);

//Pagina inicio usuario

$router->get('/inicio-user',[InicioUserController::class, 'inicioUser']);
$router->get('/Planes',[InicioUserController::class, 'planes']);
$router->get('/Suscripciones',[InicioUserController::class, 'suscripciones']);
$router->get('/Clases',[InicioUserController::class, 'clases']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();