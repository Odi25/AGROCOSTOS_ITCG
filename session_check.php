<?php
session_start();

// Redirigir al login si no hay sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php?error=no_session");
    exit();
}

/**
 * Función para verificar el rol autorizado en el dashboard actual
 */
function verificarRol($rolEsperado) {
    if ($_SESSION['id_rol'] != $rolEsperado) {
        // Redirigir según el rol real si intenta entrar a una página no permitida
        switch ($_SESSION['id_rol']) {
            case 1:
                header("Location: dashboard_admin.php");
                break;
            case 2:
                header("Location: dashboard_usuario.php");
                break;
            case 3:
                header("Location: dashboard_reportes.php");
                break;
            default:
                header("Location: index.php");
                break;
        }
        exit();
    }
}
?>