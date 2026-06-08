<?php

use MongoDB\Driver\Manager;

$db = null;

if (!extension_loaded('mongodb')) {
    error_log('MongoDB extension is not available.');
    return;
}

$mongoUri = getenv('MONGODB_URI') ?: '';
$mongoDatabase = getenv('MONGODB_DATABASE') ?: 'appgym';

if ($mongoUri === '') {
    error_log('Missing MONGODB_URI environment variable.');
    return;
}

try {
    $db = new Manager($mongoUri, [
        'connectTimeoutMS' => (int) (getenv('MONGODB_CONNECT_TIMEOUT_MS') ?: 5000),
        'socketTimeoutMS' => (int) (getenv('MONGODB_SOCKET_TIMEOUT_MS') ?: 5000),
        'serverSelectionTimeoutMS' => (int) (getenv('MONGODB_SERVER_SELECTION_TIMEOUT_MS') ?: 5000),
    ]);
} catch (\Throwable $e) {
    error_log('Could not initialize Cosmos DB for MongoDB connection: ' . $e->getMessage());
}
