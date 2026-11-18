<?php
require_once 'session_check.php';
verificarRol(3); // Exclusivo para admin.reportes
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGROCOSTOS - Módulo de Reportes</title>
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
            <h2>AGROCOSTOS — Consulta y Reportes</h2>
        </div>
        <div>
            Bienvenido, <strong><?php echo $_SESSION['nombre_completo']; ?></strong> (admin.reportes)
            <a href="logout.php" class="btn-logout" style="margin-left:15px;">Cerrar Sesión</a>
        </div>
    </header>

    <div class="container">
        <h1>Centro de Consultas y Reportes Financieros</h1>
        <p>Visualización de proyecciones, rentabilidad por cultivo y análisis de costos por hectárea.</p>

        <div class="card-grid">
            <div class="card">
                <h3>Reporte General de Costos</h3>
                <p>Resumen detallado de costos fijos y variables por temporada.</p>
                <a href="#">Generar Reporte &rarr;</a>
            </div>
            <div class="card">
                <h3>Costo por Hectárea</h3>
                <p>Cálculo analítico del costo unitario por cultivo y parcela.</p>
                <a href="#">Ver Análisis &rarr;</a>
            </div>
            <div class="card">
                <h3>Proyección de Rentabilidad</h3>
                <p>Simulador de punto de equilibrio según rendimiento de cosecha.</p>
                <a href="#">Simular Proyección &rarr;</a>
            </div>
            <div class="card">
                <h3>Exportación / Impresión</h3>
                <p>Descarga de reportes en PDF/Excel para firma y entrega institucional.</p>
                <a href="#">Exportar Documentos &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>