<?php
namespace Controllers;

use MVC\Router;

class InicioUserController{

    public static function inicioUser(Router $router){
        $router->render('auth/inicio-user');
    }

    public static function planes(Router $router){
        $router->render('auth/inicio-user');
    }

    public static function suscripciones(Router $router){
        $router->render('auth/inicio-user');
    }

    public static function clases(Router $router){
        $router->render('auth/inicio-user');
    }

}