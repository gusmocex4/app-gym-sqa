<?php
namespace Controllers;

use MVC\Router;

class PrincipalController{
    public static function principal(Router $router){
        $router->render('auth/principal');
    }
}