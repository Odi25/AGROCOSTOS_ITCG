<?php
session_start();

// Redirigir automáticamente si ya existe una sesión activa
if (isset($_SESSION['id_rol'])) {
    switch ($_SESSION['id_rol']) {
        case 1:
            header("Location: dashboard_admin.php");
            exit();
        case 2:
            header("Location: dashboard_usuario.php");
            exit();
        case 3:
            header("Location: dashboard_reportes.php");
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGROCOSTOS - Inicio de Sesión</title>
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
<body class="text-slate-700 min-h-screen flex flex-col justify-between">

    <!-- FRANJA SUPERIOR INSTITUCIONAL -->
    <div class="w-full h-1.5 bg-emerald-800"></div>

    <!-- CONTENEDOR CENTRAL -->
    <main class="flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 card-shadow overflow-hidden">
            
            <!-- CABECERA CON LOGOTIPOS INSTITUCIONALES -->
            <div class="px-6 pt-6 pb-4 bg-slate-50/70 border-b border-slate-100">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <img src="assets/logo_tecnm.png" alt="TecNM" class="h-10 object-contain">
                    <img src="assets/logo_tecnm_cdguzman.png" alt="TecNM Campus Ciudad Guzmán" class="h-10 object-contain">
                </div>

                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-emerald-800 text-emerald-300 flex items-center justify-center shadow-sm mb-3">
                        <i data-lucide="sprout" class="w-7 h-7"></i>
                    </div>
                    <h1 class="text-xl font-bold text-slate-800 tracking-wide">AGROCOSTOS</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Sistema Agrícola de Gestión Financiera</p>
                </div>
            </div>

            <!-- CUERPO DEL FORMULARIO -->
            <div class="p-6 sm:p-7">
                <!-- Alerta Dinámica de Errores -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium flex items-start gap-2.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5"></i>
                        <span>
                            <?php
                            switch ($_GET['error']) {
                                case 'credenciales':
                                    echo "Usuario o contraseña incorrectos. Verifica tus datos.";
                                    break;
                                case 'inactivo':
                                    echo "Tu cuenta se encuentra inactiva. Contacta al administrador.";
                                    break;
                                case 'no_session':
                                    echo "Debes iniciar sesión para acceder al panel solicitado.";
                                    break;
                                case 'campos_vacios':
                                    echo "Por favor completa todos los campos obligatorios.";
                                    break;
                                case 'sin_rol':
                                    echo "Tu usuario no tiene un rol válido asignado en el sistema.";
                                    break;
                                default:
                                    echo "Error de autenticación. Inténtalo nuevamente.";
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <form action="Autenticacion_Usuario.php" method="POST" class="space-y-4">
                    <div>
                        <label for="nombre_usuario" class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Usuario o Correo Electrónico
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </span>
                            <input type="text" 
                                   id="nombre_usuario" 
                                   name="nombre_usuario" 
                                   placeholder="Ej. admin o correo@itcg.edu.mx" 
                                   required 
                                   autocomplete="username"
                                   class="w-full text-xs font-medium text-slate-800 border border-slate-300 rounded-lg pl-9 pr-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Contraseña
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required 
                                   autocomplete="current-password"
                                   class="w-full text-xs font-medium text-slate-800 border border-slate-300 rounded-lg pl-9 pr-10 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all">
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    title="Mostrar u ocultar contraseña"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i data-lucide="eye" id="iconoOjo" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>

            <!-- PIE DE TARJETA -->
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                    Acceso seguro RBAC
                </span>
                <span>TecNM Cd. Guzmán • 2026</span>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function togglePassword() {
            const input = document.getElementById('password');
            const icono = document.getElementById('iconoOjo');
            if (input.type === 'password') {
                input.type = 'text';
                icono.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icono.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>