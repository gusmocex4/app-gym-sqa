<?php
namespace Controllers;

use MVC\Router;

class InicioAdminController{

    public static function inicioAdmin(Router $router){
        $router->render('auth/inicio-admin');
    }

    public static function administrarPlanes(Router $router){
        $router->render('auth/inicio-admin');
    }

    public static function administrarClases(Router $router){
        $router->render('auth/inicio-admin');
    }

}