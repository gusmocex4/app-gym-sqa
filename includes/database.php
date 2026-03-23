<?php

$db = mysqli_connect('localhost', 'root', '12345678', 'appgym', '3306');
$db->set_charset("utf8");


if (!$db) {
    echo "Error: No se pudo conectar a MySQL.";
    echo "errno de depuración: " . mysqli_connect_errno();
    echo "error de depuración: " . mysqli_connect_error();
    exit;
}

