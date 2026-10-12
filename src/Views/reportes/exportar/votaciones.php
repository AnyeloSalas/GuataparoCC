<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Votación - Impresión</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .stats-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #f9f9f9; }
        .candidate { margin-bottom: 15px; page-break-inside: avoid; }
        .bar-container { background-color: #f1f1f1; height: 20px; width: 100%; border: 1px solid #ccc; }
        .bar { background-color: #4CAF50; height: 100%; text-align: center; line-height: 20px; color: white; font-size: 12px; }
        h2, h3 { margin: 5px 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <img src="assets/img/logo_guataparo.png" alt="Logo" style="height: 80px; margin-bottom: 10px;">
        <h2>SISTEMA DE GESTIÓN GUATAPARO</h2>
        <h3>ACTA DE RESULTADOS ELECTORALES</h3>
        <p>Fecha de emisión: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="stats-box">
        <strong>RESUMEN DE PARTICIPACIÓN</strong><br><br>
        Total Votos: <?php echo $estadisticas['total_votos']; ?><br>
        Socios Habilitados: <?php echo $estadisticas['socios_activos']; ?><br>
        Porcentaje de Participación: <?php echo $estadisticas['porcentaje_participacion']; ?>%
    </div>

    <h3>RESULTADOS DETALLADOS</h3>
    
    <?php 
    $currentTipo = '';
    foreach ($resultados as $candidato): 
        if ($currentTipo != $candidato['tipomiembro']):
            $currentTipo = $candidato['tipomiembro'];
            echo "<h4 style='margin-top:20px; border-bottom:1px solid #ccc; text-transform:uppercase'>$currentTipo</h4>";
        endif;
        
        $porcentaje = ($estadisticas['total_votos'] > 0) 
            ? round(($candidato['votos'] / $estadisticas['total_votos']) * 100, 1) 
            : 0;
    ?>
        <div class="candidate">
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <span><?php echo htmlspecialchars($candidato['nombre']); ?></span>
                <strong><?php echo $candidato['votos']; ?> votos (<?php echo $porcentaje; ?>%)</strong>
            </div>
            <div class="bar-container">
                <div class="bar" style="width: <?php echo $porcentaje; ?>%"><?php echo $porcentaje > 5 ? $porcentaje.'%' : ''; ?></div>
            </div>
        </div>
    <?php endforeach; ?>

    <div style="margin-top: 50px; text-align: center;">
        <br><br><br>
        __________________________<br>
        Firma Responsable
    </div>

    <div style="margin-top: 30px; font-size: 10px; text-align: right; color: #666;">
        Generado por: <?php echo $_SESSION['usuario']; ?>
    </div>
</body>
</html>
