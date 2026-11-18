<?php
session_start();
require_once __DIR__ . '/conexion_db.php'; // Reutiliza la conexión $conn creada en conexion.php

// 1. Validar que la petición venga únicamente por método POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Obtener y limpiar datos enviados desde el formulario
    $usuario_input  = isset($_POST['nombre_usuario']) ? trim($_POST['nombre_usuario']) : '';
    $password_input = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($usuario_input) || empty($password_input)) {
        header("Location: index.php?error=campos_vacios");
        exit();
    }

    // 2. Consulta preparada para evitar inyección SQL
    $sql = "SELECT u.id_usuario, u.nombre_completo, u.password_hash, u.activo, r.id_rol, r.nombre_rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.id_rol = r.id_rol 
            WHERE u.nombre_usuario = ? OR u.correo_electronico = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario_input, $usuario_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        // 3. Verificar estado de la cuenta
        if ($usuario['activo'] != 1) {
            $stmt->close();
            $conn->close();
            header("Location: index.php?error=inactivo");
            exit();
        }

        // 4. Verificar la contraseña con hash bcrypt
        if (password_verify($password_input, $usuario['password_hash'])) {
            
            // Guardar datos en la sesión activa
            $_SESSION['id_usuario']      = $usuario['id_usuario'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
            $_SESSION['id_rol']          = $usuario['id_rol'];
            $_SESSION['nombre_rol']      = $usuario['nombre_rol'];

            $stmt->close();
            $conn->close();

            // 5. Redirección según Rol (RBAC)
            switch ($usuario['id_rol']) {
                case 1: // Administrador
                    header("Location: dashboard_admin.php");
                    break;
                case 2: // Usuario del Sistema
                    header("Location: dashboard_usuario.php");
                    break;
                case 3: // admin.reportes (Consulta)
                    header("Location: dashboard_reportes.php");
                    break;
                default:
                    header("Location: index.php?error=sin_rol");
                    break;
            }
            exit();

        } else {
            // Contraseña incorrecta
            $stmt->close();
            $conn->close();
            header("Location: index.php?error=credenciales");
            exit();
        }
    } else {
        // Usuario no encontrado
        $stmt->close();
        $conn->close();
        header("Location: index.php?error=credenciales");
        exit();
    }
} else {
    // Redirigir al formulario si intentan ingresar por URL directa (GET)
    header("Location: index.php");
    exit();
}
?>