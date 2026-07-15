<?php

namespace Tests\Support;

use Model\ActiveRecord;
use ReflectionClass;
use Throwable;

class SimpleTestRunner
{
    private array $results = [];

    public function run(string $id, string $description, callable $test): void
    {
        try {
            $this->resetActiveRecordAlerts();
            $test();

            $this->results[] = [
                'id' => $id,
                'description' => $description,
                'status' => 'Aprobado',
                'error' => ''
            ];
        } catch (Throwable $exception) {
            $this->results[] = [
                'id' => $id,
                'description' => $description,
                'status' => 'Fallido',
                'error' => $exception->getMessage()
            ];
        }
    }

    public function assertTrue(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    public function assertFalse(bool $condition, string $message): void
    {
        $this->assertTrue(!$condition, $message);
    }

    public function assertSame($expected, $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($message . " Esperado: " . var_export($expected, true) . ". Actual: " . var_export($actual, true) . ".");
        }
    }

    public function assertNotSame($expected, $actual, string $message): void
    {
        if ($expected === $actual) {
            throw new \RuntimeException($message);
        }
    }

    public function assertNotEmpty($actual, string $message): void
    {
        if (empty($actual)) {
            throw new \RuntimeException($message);
        }
    }

    public function assertContains(string $needle, array $haystack, string $message): void
    {
        if (!in_array($needle, $haystack, true)) {
            throw new \RuntimeException($message . " Valor buscado: {$needle}.");
        }
    }

    public function summary(string $suiteName): int
    {
        $passed = 0;
        $failed = 0;

        echo PHP_EOL . "{$suiteName}" . PHP_EOL;
        echo str_repeat('=', strlen($suiteName)) . PHP_EOL;

        foreach ($this->results as $result) {
            $mark = $result['status'] === 'Aprobado' ? 'OK' : 'ERROR';
            echo "[{$mark}] {$result['id']} - {$result['description']} - {$result['status']}" . PHP_EOL;

            if ($result['error'] !== '') {
                echo "      {$result['error']}" . PHP_EOL;
            }

            if ($result['status'] === 'Aprobado') {
                $passed++;
            } else {
                $failed++;
            }
        }

        $total = count($this->results);
        $coverage = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

        echo PHP_EOL . "Metricas" . PHP_EOL;
        echo "- Casos ejecutados: {$total}" . PHP_EOL;
        echo "- Casos aprobados: {$passed}" . PHP_EOL;
        echo "- Casos fallidos: {$failed}" . PHP_EOL;
        echo "- Cobertura de ejecucion planificada: {$coverage}%" . PHP_EOL;

        return $failed === 0 ? 0 : 1;
    }

    private function resetActiveRecordAlerts(): void
    {
        if (!class_exists(ActiveRecord::class)) {
            return;
        }

        $reflection = new ReflectionClass(ActiveRecord::class);
        $property = $reflection->getProperty('alertas');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}
