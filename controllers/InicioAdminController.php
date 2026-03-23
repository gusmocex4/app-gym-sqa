<?php
namespace Controllers;

use MVC\Router;

class InicioAdminController{

    public static function inicioAdmin(Router $router){
        $router->render('auth/inicio-admin');
    }

}