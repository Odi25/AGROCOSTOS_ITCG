<?php
// 1. Inicializar la sesión existente para poder manipularla
session_start();

// 2. Limpiar todas las variables almacenadas en $_SESSION
$_SESSION = array();

// 3. Borrar la galleta (cookie) de sesión del navegador si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destruir completamente la sesión en el servidor
session_destroy();

// 5. Redirigir al formulario de inicio de sesión
header("Location: index.php");
exit();
?>