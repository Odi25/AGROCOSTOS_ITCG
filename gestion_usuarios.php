<?php
session_start();

// 1. Protección de Sesión y Control de Acceso por Roles (Solo Administrador - Rol 1)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: index.php?error=no_session");
    exit();
}

require_once __DIR__ . '/conexion_db.php';

$mensaje = '';
$tipo_alerta = 'success'; // 'success' | 'danger'

// 2. Procesamiento de Formularios (CRUD en MySQL)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // A. CREAR NUEVO USUARIO
    if ($accion === 'crear') {
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $correo          = trim($_POST['correo_electronico'] ?? '');
        $usuario         = trim($_POST['nombre_usuario'] ?? '');
        $password        = $_POST['password'] ?? '';
        $id_rol          = (int)($_POST['id_rol'] ?? 0);
        $activo          = isset($_POST['activo']) ? 1 : 0;

        if (!empty($nombre_completo) && !empty($correo) && !empty($usuario) && !empty($password) && $id_rol > 0) {
            // Verificar si el usuario o correo ya existen
            $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? OR correo_electronico = ?");
            $check->bind_param("ss", $usuario, $correo);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $mensaje = "El nombre de usuario o correo electrónico ya está registrado.";
                $tipo_alerta = "danger";
            } else {
                // Encriptar contraseña con Bcrypt
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (id_rol, nombre_completo, correo_electronico, nombre_usuario, password_hash, activo) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssi", $id_rol, $nombre_completo, $correo, $usuario, $password_hash, $activo);

                if ($stmt->execute()) {
                    $mensaje = "Nuevo usuario creado con éxito y contraseña encriptada.";
                    $tipo_alerta = "success";
                } else {
                    $mensaje = "Error al guardar en la base de datos: " . $conn->error;
                    $tipo_alerta = "danger";
                }
                $stmt->close();
            }
            $check->close();
        } else {
            $mensaje = "Completa todos los campos obligatorios.";
            $tipo_alerta = "danger";
        }
    }

    // B. EDITAR USUARIO EXISTENTE
    if ($accion === 'editar') {
        $id_usuario      = (int)($_POST['id_usuario'] ?? 0);
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $correo          = trim($_POST['correo_electronico'] ?? '');
        $usuario         = trim($_POST['nombre_usuario'] ?? '');
        $id_rol          = (int)($_POST['id_rol'] ?? 0);
        $activo          = isset($_POST['activo']) ? 1 : 0;
        $password        = $_POST['password'] ?? '';

        if ($id_usuario > 0 && !empty($nombre_completo) && !empty($correo) && !empty($usuario) && $id_rol > 0) {
            if (!empty($password)) {
                // Actualizar incluyendo nueva contraseña encriptada
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios 
                        SET id_rol = ?, nombre_completo = ?, correo_electronico = ?, nombre_usuario = ?, password_hash = ?, activo = ? 
                        WHERE id_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssii", $id_rol, $nombre_completo, $correo, $usuario, $password_hash, $activo, $id_usuario);
            } else {
                // Conservar la contraseña actual
                $sql = "UPDATE usuarios 
                        SET id_rol = ?, nombre_completo = ?, correo_electronico = ?, nombre_usuario = ?, activo = ? 
                        WHERE id_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssii", $id_rol, $nombre_completo, $correo, $usuario, $activo, $id_usuario);
            }

            if ($stmt->execute()) {
                $mensaje = "Usuario actualizado con éxito.";
                $tipo_alerta = "success";
            } else {
                $mensaje = "Error al actualizar usuario: " . $conn->error;
                $tipo_alerta = "danger";
            }
            $stmt->close();
        }
    }

    // C. ELIMINAR USUARIO
    if ($accion === 'eliminar') {
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);

        if ($id_usuario === (int)$_SESSION['id_usuario']) {
            $mensaje = "No puedes eliminar tu propia cuenta de administrador activa.";
            $tipo_alerta = "danger";
        } elseif ($id_usuario > 0) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                $mensaje = "Usuario eliminado del sistema.";
                $tipo_alerta = "danger";
            } else {
                $mensaje = "No se puede eliminar el usuario porque tiene registros asociados. Desactívalo en su lugar.";
                $tipo_alerta = "danger";
            }
            $stmt->close();
        }
    }
}

// 3. Consultar Roles desde MySQL
$roles_result = $conn->query("SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol ASC");
$roles = [];
while ($r = $roles_result->fetch_assoc()) {
    $roles[$r['id_rol']] = $r['nombre_rol'];
}

// 4. Consultar Usuarios desde MySQL
$sql_usuarios = "SELECT u.id_usuario, u.id_rol, u.nombre_completo, u.correo_electronico, u.nombre_usuario, u.activo, r.nombre_rol 
                 FROM usuarios u 
                 INNER JOIN roles r ON u.id_rol = r.id_rol 
                 ORDER BY u.id_usuario ASC";
$usuarios_result = $conn->query($sql_usuarios);

$usuarios = [];
$statTotal = 0;
$statRol1 = 0;
$statRol2 = 0;
$statRol3 = 0;

while ($u = $usuarios_result->fetch_assoc()) {
    $usuarios[] = $u;
    $statTotal++;
    if ((int)$u['id_rol'] === 1) $statRol1++;
    elseif ((int)$u['id_rol'] === 2) $statRol2++;
    elseif ((int)$u['id_rol'] === 3) $statRol3++;
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGROCOSTOS - Módulo de Gestión de Usuarios</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        agro: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-full font-sans text-slate-800 antialiased flex flex-col min-h-screen">

    <!-- Top Navigation Bar / Banner de Integración -->
    <header class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Branding AGROCOSTOS -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-agro-600 to-emerald-400 flex items-center justify-center text-white shadow-lg shadow-agro-600/30">
                        <i class="fa-solid fa-seedling text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg tracking-tight text-white flex items-center gap-2">
                            AGROCOSTOS <span class="text-xs px-2 py-0.5 rounded-full bg-agro-500/20 text-agro-400 font-medium border border-agro-500/30">Admin</span>
                        </span>
                        <p class="text-xs text-slate-400">Plataforma de Cálculo y Proyección Agrícola</p>
                    </div>
                </div>

                <!-- Botón de Retorno al Menú Principal -->
                <div class="flex items-center gap-3">
                    <a href="dashboard_admin.php" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 hover:text-white rounded-lg border border-slate-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-agro-500">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Volver al Menú Principal</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Banner Informativo del Módulo -->
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-info text-amber-600 text-lg mt-0.5"></i>
            <div class="text-sm">
                <span class="font-semibold">Módulo de Administración de Usuarios:</span> Este apartado permite gestionar las credenciales (cifradas con Bcrypt), nombres completos y roles dentro del ecosistema <strong>AGROCOSTOS</strong>.
            </div>
        </div>

        <!-- Section Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-agro-600"></i>
                    Gestión de Usuarios
                </h1>
                <p class="mt-1 text-sm text-slate-500">Administra los accesos y roles del equipo técnico y operativo.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-3">
                <button onclick="abrirModalUsuario()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-agro-600 hover:bg-agro-700 text-white text-sm font-medium rounded-xl shadow-lg shadow-agro-600/20 transition-all hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-agro-500 focus:ring-offset-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Nuevo Usuario</span>
                </button>
            </div>
        </div>

        <!-- Dashboard Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Usuarios</p>
                    <h3 class="text-2xl font-bold text-slate-900"><?= $statTotal ?></h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 text-lg">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($roles[1] ?? 'Administradores') ?></p>
                    <h3 class="text-2xl font-bold text-slate-900"><?= $statRol1 ?></h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-lg">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($roles[2] ?? 'Operadores') ?></p>
                    <h3 class="text-2xl font-bold text-slate-900"><?= $statRol2 ?></h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($roles[3] ?? 'Consulta') ?></p>
                    <h3 class="text-2xl font-bold text-slate-900"><?= $statRol3 ?></h3>
                </div>
            </div>
        </div>

        <!-- Controls Section: Search & Filters -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Real-time Search Input -->
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" id="searchInput" oninput="filtrarUsuarios()" placeholder="Buscar por nombre, usuario o correo..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition-all">
                </div>

                <!-- Role Filter -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label for="roleFilter" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Filtrar por Rol:</label>
                    <select id="roleFilter" onchange="filtrarUsuarios()" class="w-full md:w-52 py-2.5 px-3 bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-agro-500 transition-all">
                        <option value="todos">Todos los Roles</option>
                        <?php foreach ($roles as $id_r => $nombre_r): ?>
                            <option value="<?= $id_r ?>"><?= htmlspecialchars($nombre_r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="py-4 px-6">Usuario</th>
                            <th class="py-4 px-6">Cuenta / Acceso</th>
                            <th class="py-4 px-6">Correo Electrónico</th>
                            <th class="py-4 px-6">Nivel / Rol</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="divide-y divide-slate-100 text-sm">
                        <?php foreach ($usuarios as $user): 
                            $partes = explode(' ', trim($user['nombre_completo']));
                            $iniciales = strtoupper(substr($partes[0] ?? 'U', 0, 1) . substr($partes[1] ?? '', 0, 1));
                            $busqueda_texto = strtolower($user['nombre_completo'] . ' ' . $user['nombre_usuario'] . ' ' . $user['correo_electronico']);
                        ?>
                        <tr class="usuario-fila hover:bg-slate-50/80 transition-colors" 
                            data-search="<?= htmlspecialchars($busqueda_texto) ?>" 
                            data-rol="<?= (int)$user['id_rol'] ?>">
                            
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-semibold text-slate-600 text-xs uppercase">
                                        <?= htmlspecialchars($iniciales) ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900"><?= htmlspecialchars($user['nombre_completo']) ?></div>
                                        <div class="text-xs text-slate-400 sm:hidden"><?= htmlspecialchars($user['correo_electronico']) ?></div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-6 text-slate-600 font-mono text-xs">
                                @<?= htmlspecialchars($user['nombre_usuario']) ?>
                            </td>

                            <td class="py-4 px-6 text-slate-600">
                                <?= htmlspecialchars($user['correo_electronico']) ?>
                            </td>

                            <td class="py-4 px-6">
                                <?php if ((int)$user['id_rol'] === 1): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200/60">
                                        <i class="fa-solid fa-user-shield text-[10px]"></i> <?= htmlspecialchars($user['nombre_rol']) ?>
                                    </span>
                                <?php elseif ((int)$user['id_rol'] === 2): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200/60">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i> <?= htmlspecialchars($user['nombre_rol']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200/60">
                                        <i class="fa-solid fa-eye text-[10px]"></i> <?= htmlspecialchars($user['nombre_rol']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="py-4 px-6">
                                <?php if ((int)$user['activo'] === 1): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-agro-100 text-agro-700 border border-agro-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-agro-600"></span> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick='editarUsuario(<?= htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8") ?>)' title="Editar" class="w-8 h-8 rounded-lg text-slate-500 hover:text-agro-600 hover:bg-agro-50 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button type="button" onclick='confirmarEliminar(<?= (int)$user["id_usuario"] ?>, <?= htmlspecialchars(json_encode($user["nombre_completo"]), ENT_QUOTES, "UTF-8") ?>)' title="Eliminar" class="w-8 h-8 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="<?= count($usuarios) > 0 ? 'hidden' : '' ?> py-12 text-center">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-800">No se encontraron usuarios</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Prueba cambiando el criterio de búsqueda o añade un nuevo usuario al sistema.</p>
            </div>
        </div>

    </main>

    <!-- Modal Form: Crear / Editar Usuario -->
    <div id="userModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="userModalCard">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-agro-600"></i>
                    Nuevo Usuario
                </h3>
                <button type="button" onclick="cerrarModalUsuario()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body / Form -->
            <form method="POST" id="userForm" class="p-6 space-y-4">
                <input type="hidden" name="accion" id="formAccion" value="crear">
                <input type="hidden" name="id_usuario" id="userId" value="">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nombre Completo -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="nombre_completo">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre_completo" id="nombre_completo" required placeholder="Ej. Carlos Mendoza Ruiz" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-agro-500 focus:outline-none transition-all">
                    </div>

                    <!-- Nombre de Usuario -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="nombre_usuario">
                            Usuario de Acceso <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre_usuario" id="nombre_usuario" required placeholder="Ej. cmendoza" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-agro-500 focus:outline-none transition-all">
                    </div>

                    <!-- Nivel de Usuario / Rol -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="id_rol">
                            Nivel de Usuario <span class="text-red-500">*</span>
                        </label>
                        <select name="id_rol" id="id_rol" required class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-agro-500 focus:outline-none transition-all">
                            <option value="" disabled selected>Selecciona un rol</option>
                            <?php foreach ($roles as $id_r => $nombre_r): ?>
                                <option value="<?= $id_r ?>"><?= htmlspecialchars($nombre_r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Correo Electrónico -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="correo_electronico">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="correo_electronico" id="correo_electronico" required placeholder="ejemplo@agrocostos.com" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-agro-500 focus:outline-none transition-all">
                    </div>

                    <!-- Contraseña (Encriptada con Bcrypt) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="password">
                            Contraseña <span id="pwdRequiredMark" class="text-red-500">*</span>
                            <span id="pwdHelpText" class="text-[11px] font-normal normal-case text-slate-400"></span>
                        </label>
                        <input type="password" name="password" id="password" placeholder="••••••••" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-agro-500 focus:outline-none transition-all">
                    </div>

                    <!-- Estado Activo -->
                    <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                        <input type="checkbox" name="activo" id="activo" value="1" checked class="w-4 h-4 rounded text-agro-600 focus:ring-agro-500 border-slate-300">
                        <label for="activo" class="text-sm font-medium text-slate-700">Cuenta activa (permitir inicio de sesión)</label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="cerrarModalUsuario()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 bg-agro-600 hover:bg-agro-700 text-white text-sm font-medium rounded-xl shadow-md shadow-agro-600/20 transition-all">
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Confirmación de Eliminación -->
    <div id="deleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">¿Eliminar usuario?</h3>
            <p class="text-xs text-slate-500 mb-6">Esta acción no se puede deshacer. Se eliminará al usuario <span id="deleteUserName" class="font-semibold text-slate-800"></span>.</p>
            
            <form method="POST" class="flex gap-3 justify-center">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_usuario" id="deleteUserId" value="">
                <button type="button" onclick="cerrarModalEliminar()" class="w-full py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="w-full py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md shadow-red-600/20 transition-colors">
                    Sí, eliminar
                </button>
            </form>
        </div>
    </div>

    <!-- Message Toast Alert -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 translate-y-10 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 text-sm">
            <i id="toastIcon" class="fa-solid fa-circle-check text-agro-400 text-lg"></i>
            <span id="toastMessage">Operación realizada con éxito</span>
        </div>
    </div>

    <!-- Logic Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($mensaje)): ?>
                mostrarToast(<?= json_encode($mensaje) ?>, <?= json_encode($tipo_alerta) ?>);
            <?php endif; ?>
        });

        // Filtrado en tiempo real sobre las filas de MySQL
        function filtrarUsuarios() {
            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const roleVal = document.getElementById('roleFilter').value;
            const filas = document.querySelectorAll('.usuario-fila');
            const emptyState = document.getElementById('emptyState');

            let visibles = 0;

            filas.forEach(fila => {
                const texto = fila.getAttribute('data-search') || '';
                const rol = fila.getAttribute('data-rol') || '';

                const matchSearch = texto.includes(searchVal);
                const matchRole = (roleVal === 'todos') || (rol === roleVal);

                if (matchSearch && matchRole) {
                    fila.classList.remove('hidden');
                    visibles++;
                } else {
                    fila.classList.add('hidden');
                }
            });

            if (visibles === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        // Abrir Modal para Crear o Editar
        function abrirModalUsuario(user = null) {
            const modal = document.getElementById('userModal');
            const card = document.getElementById('userModalCard');
            const form = document.getElementById('userForm');
            const modalTitle = document.getElementById('modalTitle');
            const pwdInput = document.getElementById('password');
            const pwdReq = document.getElementById('pwdRequiredMark');
            const pwdHelp = document.getElementById('pwdHelpText');

            form.reset();

            if (user) {
                document.getElementById('formAccion').value = 'editar';
                document.getElementById('userId').value = user.id_usuario;
                document.getElementById('nombre_completo').value = user.nombre_completo;
                document.getElementById('nombre_usuario').value = user.nombre_usuario;
                document.getElementById('correo_electronico').value = user.correo_electronico;
                document.getElementById('id_rol').value = user.id_rol;
                document.getElementById('activo').checked = parseInt(user.activo) === 1;

                pwdInput.required = false;
                pwdReq.classList.add('hidden');
                pwdHelp.textContent = '(déjala en blanco para conservar la actual)';
                modalTitle.innerHTML = `<i class="fa-solid fa-user-pen text-agro-600"></i> Editar Usuario`;
            } else {
                document.getElementById('formAccion').value = 'crear';
                document.getElementById('userId').value = '';
                document.getElementById('activo').checked = true;

                pwdInput.required = true;
                pwdReq.classList.remove('hidden');
                pwdHelp.textContent = '';
                modalTitle.innerHTML = `<i class="fa-solid fa-user-plus text-agro-600"></i> Nuevo Usuario`;
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }, 10);
        }

        function editarUsuario(user) {
            abrirModalUsuario(user);
        }

        function cerrarModalUsuario() {
            const modal = document.getElementById('userModal');
            const card = document.getElementById('userModalCard');
            modal.classList.add('opacity-0');
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Modal Confirmación Eliminar
        function confirmarEliminar(id, nombreCompleto) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = nombreCompleto;

            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
        }

        function cerrarModalEliminar() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Toast de Notificación
        function mostrarToast(mensaje, tipo = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');

            toastMessage.textContent = mensaje;

            if (tipo === 'danger') {
                toastIcon.className = 'fa-solid fa-circle-xmark text-red-400 text-lg';
            } else {
                toastIcon.className = 'fa-solid fa-circle-check text-agro-400 text-lg';
            }

            toast.classList.remove('translate-y-10', 'opacity-0');

            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
            }, 3500);
        }
    </script>
</body>
</html>