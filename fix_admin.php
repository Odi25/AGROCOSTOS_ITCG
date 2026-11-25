<?php
require_once _DIR_ . '/conexion_db.php';

// Asegurar que la columna soporte los 60 caracteres del hash Bcrypt
$conn->query("ALTER TABLE usuarios MODIFY password_hash VARCHAR(255) NOT NULL");

$usuario_objetivo = 'admin';
$nueva_clave      = 'Admin123*';
$nuevo_hash       = password_hash($nueva_clave, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET password_hash = ?, activo = 1 WHERE nombre_usuario = ?");
$stmt->bind_param("ss", $nuevo_hash, $usuario_objetivo);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Contraseña encriptada y guardada correctamente.<br>";
    echo "Usuario: <strong>$usuario_objetivo</strong><br>";
    echo "Contraseña para el login: <strong>$nueva_clave</strong><br><br>";
    echo "<em>Elimina este archivo (fix_admin.php) después de iniciar sesión.</em>";
} else {
    echo "No se encontró el usuario <strong>$usuario_objetivo</strong> en la tabla <code>usuarios</code>.";
}
$stmt->close();