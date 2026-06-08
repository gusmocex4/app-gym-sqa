<?php

namespace Model;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\Exception as MongoDBException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

class ActiveRecord {

    protected static $db;
    protected static $databaseName = '';
    protected static $collection = '';
    protected static $alertas = [];

    public static function setDB(?Manager $database, string $databaseName) {
        self::$db = $database;
        self::$databaseName = $databaseName;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    public function sincronizar(array $args = []) {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function where($campo, $valor) {
        $resultado = static::consultarDocumentos([$campo => $valor], ['limit' => 1]);
        return array_shift($resultado);
    }

    public static function all() {
        return static::consultarDocumentos();
    }

    public static function find($id) {
        $resultado = static::consultarDocumentos(['_id' => (string) $id], ['limit' => 1]);
        return array_shift($resultado);
    }

    public static function get($limite) {
        return static::consultarDocumentos([], ['limit' => (int) $limite]);
    }

    protected static function consultarDocumentos(array $filter = [], array $options = []) {
        if (!self::$db) {
            static::setAlerta('error', 'La base de datos no esta disponible.');
            return [];
        }

        $query = new Query($filter, array_merge([
            'typeMap' => [
                'root' => 'array',
                'document' => 'array'
            ]
        ], $options));

        try {
            $cursor = self::$db->executeQuery(static::collectionNamespace(), $query);
        } catch (MongoDBException $e) {
            static::setAlerta('error', 'No se pudo consultar la base de datos.');
            return [];
        }

        $documentos = [];
        foreach ($cursor as $documento) {
            $documentos[] = static::crearObjeto((array) $documento);
        }

        return $documentos;
    }

    protected static function crearObjeto(array $registro) {
        $objeto = new static;

        foreach (static::mapearDocumento($registro) as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    protected static function mapearDocumento(array $registro): array {
        if (isset($registro['_id'])) {
            $registro['id'] = (string) $registro['_id'];
            unset($registro['_id']);
        }

        return $registro;
    }

    protected function atributos(): array {
        return [];
    }

    public function guardar() {
        if (!is_null($this->id) && $this->id !== '') {
            return $this->actualizar();
        }

        return $this->crear();
    }

    protected function documentoPersistente(): array {
        return $this->atributos();
    }

    protected function crear() {
        if (!self::$db) {
            static::setAlerta('error', 'La base de datos no esta disponible.');

            return [
                'resultado' => false,
                'id' => null
            ];
        }

        $documento = $this->documentoPersistente();
        $id = $this->id ?: bin2hex(random_bytes(12));

        $documento['_id'] = $id;

        $bulk = new BulkWrite();
        $bulk->insert($documento);

        try {
            self::$db->executeBulkWrite(static::collectionNamespace(), $bulk);
            $this->id = $id;

            return [
                'resultado' => true,
                'id' => $id
            ];
        } catch (MongoDBException $e) {
            static::setAlerta('error', 'No se pudo guardar el registro.');

            return [
                'resultado' => false,
                'id' => null
            ];
        }
    }

    protected function actualizar() {
        if (!self::$db) {
            static::setAlerta('error', 'La base de datos no esta disponible.');
            return false;
        }

        $bulk = new BulkWrite();
        $bulk->update(
            ['_id' => (string) $this->id],
            ['$set' => $this->documentoPersistente()],
            ['multi' => false, 'upsert' => false]
        );

        try {
            self::$db->executeBulkWrite(static::collectionNamespace(), $bulk);
            return true;
        } catch (MongoDBException $e) {
            static::setAlerta('error', 'No se pudo actualizar el registro.');
            return false;
        }
    }

    public function eliminar() {
        if (!self::$db) {
            static::setAlerta('error', 'La base de datos no esta disponible.');
            return false;
        }

        $bulk = new BulkWrite();
        $bulk->delete(['_id' => (string) $this->id], ['limit' => 1]);

        try {
            self::$db->executeBulkWrite(static::collectionNamespace(), $bulk);
            return true;
        } catch (MongoDBException $e) {
            static::setAlerta('error', 'No se pudo eliminar el registro.');
            return false;
        }
    }

    protected static function collectionNamespace(): string {
        return self::$databaseName . '.' . static::collectionName();
    }

    protected static function collectionName(): string {
        return static::$collection;
    }
}
