<?php
namespace Controllers;

use MVC\Router;

class InicioUserController{

    public static function inicioUser(Router $router){
        $router->render('auth/inicio-user');
    }

}