<?php

namespace Model;

class Usuario extends ActiveRecord{
    protected static $collection = 'usuarios';

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $admin;
    public $token;
    public $password;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->admin = (int) ($args['admin'] ?? 0);
        $this->token = $args['token'] ?? '';
        $this->password = $args['password'] ?? '';
    }

    //mensajes validacion

    public function validarNuevaCuenta(){
        if(!$this->apellido || !$this->email || !$this->password || !$this->nombre ){
            self::$alertas['error'][]='Todos los campos son obligatorios';
        }
        if (strlen($this->password)<8 && $this->password ){
            self::$alertas['error'][]='La contraseña debe tener al menos 8 caracteres';
        }
        return self::$alertas;
    }

    public function validarLogin(){
        if( !$this->email || !$this->password ){
            self::$alertas['error'][]='Todos los campos son obligatorios';
        }
        return self::$alertas;
    }

    //validar que el usuario no este registrado

    public function existeUsuario(){
        $resultado = self::where('email', $this->email);

        if ($resultado){
            self::$alertas['error'][]='El usuario ya está registrado';
        }
        return $resultado;
    }

    public function verificarPassword($password){
        $resultado = password_verify($password, $this->password);
        return $resultado;
    }

    public function hashPassword(){
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken(){
        $this->token= uniqid();
    }

    protected function atributos(): array {
        return [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'admin' => (int) $this->admin,
            'token' => $this->token,
            'password' => $this->password
        ];
    }

    protected static function collectionName(): string {
        return getenv('MONGODB_USERS_COLLECTION') ?: static::$collection;
    }
}
