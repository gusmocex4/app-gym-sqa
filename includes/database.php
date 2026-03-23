<?php

$db = mysqli_connect(
    getenv('DB_HOST') ?: 'localhost', 
    getenv('DB_USER') ?: 'root', 
    getenv('DB_PASS') ?: '12345678', 
    getenv('DB_NAME') ?: 'appgym',
    getenv('DB_PORT') ?: '3306'
);
$db->set_charset("utf8");

if (!$db) {
    echo "Error: No se pudo conectar a MySQL.";
    echo "errno de depuración: " . mysqli_connect_errno();
    echo "error de depuración: " . mysqli_connect_error();
    exit;
}

