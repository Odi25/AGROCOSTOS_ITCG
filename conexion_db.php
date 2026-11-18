<?php
// Parámetros de conexión a MySQL local
$host     = "localhost";
$usuario  = "root";
$password = "Pass2026.+Admin"; // Reemplaza con la contraseña que le asignaste a MySQL root
$db_name  = "agrocostos_db";
$puerto   = 3306;

// Crear la instancia de conexión con mysqli
$conn = new mysqli($host, $usuario, $password, $db_name, $puerto);

// Verificar si ocurrió algún error de conexión
if ($conn->connect_error) {
    die("Error crítico de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar el conjunto de caracteres a UTF-8 para soporte de tildes y caracteres especiales
$conn->set_charset("utf8mb4");
?>