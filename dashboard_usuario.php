<?php
require_once 'session_check.php';
verificarRol(2); // Exclusivo para Usuario del Sistema
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGROCOSTOS - Captura y Operación</title>
    <style>
        :root {
            --verde-primario: #2E7D32;
            --verde-oscuro: #1B5E20;
            --fondo: #F4F9F4;
            --blanco: #FFFFFF;
        }
        body { font-family: 'Arial', sans-serif; background-color: var(--fondo); margin: 0; padding: 0; }
        header { background-color: var(--verde-primario); color: var(--blanco); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #D32F2F; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: var(--blanco); border-radius: 8px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--verde-primario); }
        .card h3 { color: var(--verde-primario); margin-bottom: 10px; }
        .card a { display: inline-block; margin-top: 10px; color: var(--verde-oscuro); font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

    <header>
        <div>
            <h2>AGROCOSTOS — Captura de Campo</h2>
        </div>
        <div>
            Bienvenido, <strong><?php echo $_SESSION['nombre_completo']; ?></strong>
            <a href="logout.php" class="btn-logout" style="margin-left:15px;">Cerrar Sesión</a>
        </div>
    </header>

    <div class="container">
        <h1>Módulo de Captura de Datos Operativos</h1>
        <p>Registro de actividades en campo, aplicaciones de insumos y costos acumulados por parcela.</p>

        <div class="card-grid">
            <div class="card">
                <h3>Registro de Parcelas</h3>
                <p>Alta de hectáreas, tipos de riego y delimitación de superficies.</p>
                <a href="#">Registrar Parcela &rarr;</a>
            </div>
            <div class="card">
                <h3>Aplicación de Insumos</h3>
                <p>Captura de dosis, fertilizantes y agroquímicos aplicados en lote.</p>
                <a href="#">Capturar Insumo &rarr;</a>
            </div>
            <div class="card">
                <h3>Control de Jornales</h3>
                <p>Registro de mano de obra, horas trabajadas y tareas de campo.</p>
                <a href="#">Registrar Jornal &rarr;</a>
            </div>
            <div class="card">
                <h3>Costos Indirectos</h3>
                <p>Captura de costos fijos (mantenimiento, rentas, servicios).</p>
                <a href="#">Registrar Costo Fijo &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>