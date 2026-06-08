<?php
namespace Controllers;

use Model\Usuario;
use MVC\Router;

class LoginController{
    public static function login(Router $router){

        $alertas=[];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth=new Usuario($_POST);
            $alertas=$auth->validarLogin();

            if (empty($alertas)){
                $usuario=Usuario::where('email', $auth->email);
                
                if ($usuario){
                    //autenticar sesion
                    if ($usuario->verificarPassword($auth->password)){
                        
                        // session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        //redireccionamiento
                        if($usuario->admin === 1){
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /inicio-admin');
                            exit;
                        }
                        else{
                            header('Location: /inicio-user');
                            exit;
                        }
                        
                    }
                    else{
                        Usuario::setAlerta('error', 'Password errada.');
                    }
                }

                //verificar clave
                else{
                    Usuario::setAlerta('error', 'Usuario no encontrado.');
                }
            }
        }
        $alertas= Usuario::getAlertas();
        $router->render('auth/login',[
            'alertas' => $alertas
        ]);
    }

    public static function logout(){
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        header('Location: /');
        exit;
    }

    public static function olvide(Router $router){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            Usuario::setAlerta('error', 'La recuperacion de password aun no esta implementada.');
            $alertas = Usuario::getAlertas();
        }

        $router->render('auth/olvide-password',[
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(){
        echo "Desde recuperar";
    }

    public static function crearCuenta(Router $router){
        $usuario = new Usuario;

        $alertas=[];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $usuario->sincronizar($_POST);
            $alertas= $usuario->validarNuevaCuenta();

            if(empty($alertas)){
                $resultado = $usuario->existeUsuario();

                if ($resultado){
                    $alertas = Usuario::getAlertas();
                }
                else{
                    //hashear clave
                    $usuario->hashPassword();

                    //generar token
                    $usuario->crearToken();

                    //crear el usuario
                    $resultado = $usuario->guardar();

                    if($resultado['resultado']){
                        header('Location: /login');
                        exit;
                    }

                    Usuario::setAlerta('error', 'No se pudo crear la cuenta.');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('auth/crear-cuenta',[
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }
}
