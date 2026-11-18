<?php
session_start();

// 1. Protección de Sesión y Control de Acceso por Roles (Solo Administrador - Rol 1)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: index.php?error=no_session");
    exit();
}

require_once __DIR__ . '/conexion_db.php';

$nombre_usuario = htmlspecialchars($_SESSION['nombre_completo'] ?? 'Administrador');
$rol_usuario    = htmlspecialchars($_SESSION['nombre_rol'] ?? 'Administrador');

// Conteo dinámico de usuarios registrados
$total_usuarios = 1;
if ($res = $conn->query("SELECT COUNT(*) AS total FROM usuarios")) {
    $fila = $res->fetch_assoc();
    $total_usuarios = $fila['total'] ?? 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGROCOSTOS - Panel de Administración</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Iconos Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tipografía Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
        }
        .card-shadow {
            box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="text-slate-700 min-h-screen flex flex-col">

    <!-- HEADER PRINCIPAL (Mismo estilo que registro_agrocostos.html) -->
    <header class="bg-emerald-800 text-white shadow-md border-b border-emerald-900 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/10 p-2 rounded-lg backdrop-blur-sm">
                    <i data-lucide="sprout" class="w-6 h-6 text-emerald-300"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide">AGROCOSTOS</h1>
                    <p class="text-xs text-emerald-200">Sistema Agrícola de Gestión Financiera • TecNM Cd. Guzmán</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-700 text-emerald-100 border border-emerald-600">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-1.5 text-emerald-300"></i>
                    <?= $rol_usuario ?>
                </span>
                <a href="logout.php" class="flex items-center text-xs bg-red-600/90 hover:bg-red-700 text-white px-3 py-2 rounded-md transition-colors font-medium border border-red-500/50">
                    <i data-lucide="log-out" class="w-4 h-4 mr-1.5"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row gap-6">

        <!-- BARRA DE NAVEGACIÓN LATERAL -->
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl border border-slate-200 card-shadow overflow-hidden sticky top-22">
                <div class="p-4 border-b border-slate-100 bg-slate-50">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sesión Activa</p>
                    <p class="text-sm font-bold text-slate-800 truncate mt-0.5"><?= $nombre_usuario ?></p>
                    <p class="text-xs text-emerald-600 font-medium">ID Usuario #<?= $_SESSION['id_usuario'] ?></p>
                </div>

                <nav class="p-2 space-y-1 text-xs font-medium">
                    <a href="dashboard_admin.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200/70">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-600"></i>
                        Panel General
                    </a>
                    <a href="gestion_usuarios.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                        Usuarios y Roles (RBAC)
                    </a>
                    <a href="registro_agrocostos.html" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-slate-400"></i>
                        Registro de Costos (Año 1)
                    </a>
                    <a href="cultivos.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="trees" class="w-4 h-4 text-slate-400"></i>
                        Catálogo de Cultivos
                    </a>
                    <a href="reportes.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-slate-400"></i>
                        Reportes Financieros
                    </a>
                    <a href="respaldos.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="database" class="w-4 h-4 text-slate-400"></i>
                        Respaldos BD
                    </a>
                </nav>
            </div>
        </aside>

        <!-- ÁREA DE CONTENIDO -->
        <main class="flex-1 space-y-6">

            <!-- SECCIÓN 1: CABECERA DE BIENVENIDA -->
            <section class="bg-white rounded-xl p-5 border border-slate-200 card-shadow">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="sliders-horizontal" class="w-5 h-5 text-emerald-600"></i>
                            Panel de Administración General
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Bienvenido, <strong class="text-slate-700"><?= $nombre_usuario ?></strong>. Control centralizado de cuentas, permisos RBAC y proyecciones agrícolas.
                        </p>
                    </div>
                    <a href="gestion_usuarios.php" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-sm">
                        <i data-lucide="user-plus" class="w-4 h-4 mr-1.5"></i> Nuevo Usuario
                    </a>
                </div>
            </section>

            <!-- SECCIÓN 2: TARJETAS DE INDICADORES (KPIs) -->
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 card-shadow">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">Usuarios Activos</p>
                        <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 mt-2"><?= $total_usuarios ?></p>
                    <p class="text-[11px] text-slate-400 mt-1">Cuentas con acceso RBAC</p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 card-shadow">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">Cultivos Base</p>
                        <i data-lucide="sprout" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-emerald-600 mt-2">1</p>
                    <p class="text-[11px] text-slate-400 mt-1">Aguacate - Proyecto Tipo A</p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 card-shadow">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">Ciclo Vigente</p>
                        <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 mt-2">Año 1</p>
                    <p class="text-[11px] text-slate-400 mt-1">Mayo 2025 - Abril 2026</p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 card-shadow">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">Base de Datos</p>
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-indigo-600 mt-2">En Línea</p>
                    <p class="text-[11px] text-slate-400 mt-1">agrocostos_db (MySQL)</p>
                </div>
            </section>

            <!-- SECCIÓN 3: ACCIONES RÁPIDAS Y ESTADO DEL SISTEMA -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 card-shadow overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4 text-emerald-600"></i>
                            Accesos Directos de Administración
                        </h3>
                    </div>
                    <div class="divide-y divide-slate-100 text-xs">
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="font-semibold text-slate-800">Control de Usuarios y Permisos</p>
                                <p class="text-slate-500 mt-0.5">Alta, edición y asignación de roles (Administrador, Operador, Consulta).</p>
                            </div>
                            <a href="gestion_usuarios.php" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-800 font-semibold bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 transition-colors">
                                Administrar Usuarios <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="font-semibold text-slate-800">Captura de Costos Agrícolas (Año 1)</p>
                                <p class="text-slate-500 mt-0.5">Matriz mensual por concepto técnico, jornales e indicadores B/C.</p>
                            </div>
                            <a href="registro_agrocostos.html" class="inline-flex items-center gap-1 text-slate-700 hover:text-slate-900 font-semibold bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                                Abrir Matriz <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="font-semibold text-slate-800">Respaldo de Base de Datos</p>
                                <p class="text-slate-500 mt-0.5">Exportación preventiva de tablas y registros financieros.</p>
                            </div>
                            <a href="respaldos.php" class="inline-flex items-center gap-1 text-slate-700 hover:text-slate-900 font-semibold bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                                Respaldar <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 card-shadow overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                            <i data-lucide="server" class="w-4 h-4 text-emerald-600"></i>
                            Ficha Técnica de Sesión
                        </h3>
                    </div>
                    <div class="p-4 space-y-3 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Institución:</span>
                            <span class="font-semibold text-slate-800">TecNM Cd. Guzmán</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Perfil RBAC:</span>
                            <span class="font-semibold text-emerald-700"><?= $rol_usuario ?> (Nivel 1)</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Cifrado Claves:</span>
                            <span class="font-semibold text-slate-800">Bcrypt (PASSWORD_DEFAULT)</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-500">Base de Datos:</span>
                            <span class="font-semibold text-slate-800">agrocostos_db</span>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>