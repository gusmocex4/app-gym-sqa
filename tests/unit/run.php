<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../support/SimpleTestRunner.php';

use Model\ActiveRecord;
use Model\Usuario;
use Tests\Support\SimpleTestRunner;

ActiveRecord::setDB(null, '');

$runner = new SimpleTestRunner();

$runner->run('PU-FZ-001', 'validarLogin rechaza email y password vacios', function () use ($runner) {
    $usuario = new Usuario([
        'email' => '',
        'password' => ''
    ]);

    $alertas = $usuario->validarLogin();

    $runner->assertContains('Todos los campos son obligatorios', $alertas['error'] ?? [], 'Debe registrar alerta de campos obligatorios.');
});

$runner->run('PU-FZ-002', 'validarNuevaCuenta rechaza campos obligatorios vacios', function () use ($runner) {
    $usuario = new Usuario();

    $alertas = $usuario->validarNuevaCuenta();

    $runner->assertContains('Todos los campos son obligatorios', $alertas['error'] ?? [], 'Debe registrar alerta de campos obligatorios.');
});

$runner->run('PU-FZ-003', 'validarNuevaCuenta rechaza password menor a ocho caracteres', function () use ($runner) {
    $usuario = new Usuario([
        'nombre' => 'Test',
        'apellido' => 'Unitario',
        'email' => 'unitario@example.com',
        'password' => '1234567'
    ]);

    $alertas = $usuario->validarNuevaCuenta();

    $runner->assertContains('La contraseña debe tener al menos 8 caracteres', $alertas['error'] ?? [], 'Debe registrar alerta de longitud minima.');
});

$runner->run('PU-FZ-004', 'hashPassword cifra password y verificarPassword acepta password original', function () use ($runner) {
    $usuario = new Usuario([
        'password' => '12345678'
    ]);

    $passwordOriginal = $usuario->password;
    $usuario->hashPassword();

    $runner->assertNotSame($passwordOriginal, $usuario->password, 'El password persistente no debe permanecer en texto plano.');
    $runner->assertTrue($usuario->verificarPassword($passwordOriginal), 'El hash debe validar el password original.');
});

$runner->run('PU-FZ-005', 'verificarPassword rechaza password incorrecta', function () use ($runner) {
    $usuario = new Usuario([
        'password' => '12345678'
    ]);
    $usuario->hashPassword();

    $runner->assertFalse($usuario->verificarPassword('claveerrada'), 'El hash no debe aceptar una password incorrecta.');
});

$runner->run('PU-FZ-006', 'crearToken genera token no vacio', function () use ($runner) {
    $usuario = new Usuario();

    $usuario->crearToken();

    $runner->assertNotEmpty($usuario->token, 'El token debe generarse con un valor no vacio.');
});

$runner->run('PU-FZ-007', 'sincronizar actualiza solo propiedades existentes', function () use ($runner) {
    $usuario = new Usuario([
        'nombre' => 'Original',
        'email' => 'original@example.com'
    ]);

    $usuario->sincronizar([
        'nombre' => 'Actualizado',
        'email' => 'actualizado@example.com',
        'campo_inexistente' => 'no debe crearse'
    ]);

    $runner->assertSame('Actualizado', $usuario->nombre, 'Debe actualizar nombre.');
    $runner->assertSame('actualizado@example.com', $usuario->email, 'Debe actualizar email.');
    $runner->assertFalse(property_exists($usuario, 'campo_inexistente'), 'No debe crear propiedades fuera del modelo.');
});

exit($runner->summary('SCRUM-151 - Pruebas unitarias'));
