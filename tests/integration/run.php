<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../support/SimpleTestRunner.php';

use Model\ActiveRecord;
use Model\Usuario;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Tests\Support\SimpleTestRunner;

$mongoUri = getenv('MONGODB_URI') ?: 'mongodb://127.0.0.1:27017';
$databaseName = getenv('MONGODB_DATABASE') ?: 'appgym_test';
$collectionName = getenv('MONGODB_USERS_COLLECTION') ?: 'usuarios_integracion';

putenv("MONGODB_USERS_COLLECTION={$collectionName}");

$manager = new Manager($mongoUri);
ActiveRecord::setDB($manager, $databaseName);

$namespace = "{$databaseName}.{$collectionName}";
$cleanup = function () use ($manager, $namespace): void {
    $bulk = new BulkWrite();
    $bulk->delete([], ['limit' => 0]);
    $manager->executeBulkWrite($namespace, $bulk);
};

$cleanup();

$runner = new SimpleTestRunner();

$runner->run('PI-FZ-001', 'guardar inserta usuario y where lo consulta por email', function () use ($runner) {
    $usuario = new Usuario([
        'nombre' => 'Test',
        'apellido' => 'Integracion',
        'email' => 'integracion@example.com',
        'password' => '12345678'
    ]);

    $resultado = $usuario->guardar();
    $encontrado = Usuario::where('email', 'integracion@example.com');

    $runner->assertTrue($resultado['resultado'], 'guardar debe retornar resultado exitoso.');
    $runner->assertNotEmpty($resultado['id'], 'guardar debe retornar id.');
    $runner->assertSame('integracion@example.com', $encontrado->email, 'where debe recuperar el usuario insertado.');
});

$runner->run('PI-FZ-002', 'existeUsuario detecta correo duplicado en MongoDB', function () use ($runner) {
    $usuarioDuplicado = new Usuario([
        'email' => 'integracion@example.com'
    ]);

    $resultado = $usuarioDuplicado->existeUsuario();
    $alertas = Usuario::getAlertas();

    $runner->assertTrue($resultado instanceof Usuario, 'existeUsuario debe retornar el usuario encontrado.');
    $runner->assertContains('El usuario ya está registrado', $alertas['error'] ?? [], 'Debe registrar alerta de usuario duplicado.');
});

$runner->run('PI-FZ-003', 'guardar actualiza usuario existente por id', function () use ($runner) {
    $usuario = Usuario::where('email', 'integracion@example.com');

    $usuario->nombre = 'Actualizado';
    $resultado = $usuario->guardar();
    $actualizado = Usuario::find($usuario->id);

    $runner->assertTrue($resultado, 'guardar debe actualizar cuando el usuario tiene id.');
    $runner->assertSame('Actualizado', $actualizado->nombre, 'find debe recuperar el nombre actualizado.');
});

$runner->run('PI-FZ-004', 'eliminar remueve usuario persistido', function () use ($runner) {
    $usuario = Usuario::where('email', 'integracion@example.com');

    $resultado = $usuario->eliminar();
    $eliminado = Usuario::where('email', 'integracion@example.com');

    $runner->assertTrue($resultado, 'eliminar debe retornar verdadero.');
    $runner->assertTrue($eliminado === null, 'where no debe encontrar el usuario eliminado.');
});

$exitCode = $runner->summary('SCRUM-151 - Pruebas de integracion');
$cleanup();

exit($exitCode);
