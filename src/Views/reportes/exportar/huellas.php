<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Huellas - Sistema Guataparo</title>
    <style>
        @page {
            size: letter landscape;
            margin: 1cm;
        }
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 9pt; 
            color: #000;
            line-height: 1.3;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .text-center { text-align: center; }
        .main-title { 
            font-size: 14pt; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 20px;
        }
        .header-container {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-col {
            display: table-cell;
            vertical-align: top;
            width: 70%;
        }
        .logo-col {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            width: 30%;
        }
        .logo-col img {
            max-height: 80px;
            width: auto;
        }
        .info-item {
            margin-bottom: 3px;
        }
        .info-item strong {
            font-weight: bold;
        }
        
        table.report-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        table.report-table th { 
            background-color: #a4c2f4;
            color: #000;
            font-weight: bold;
            font-size: 8.5pt;
            border: 1px solid #dee2e6;
            padding: 8px 5px;
            text-align: center;
        }
        table.report-table td { 
            padding: 8px 5px; 
            border: 1px solid #dee2e6;
            font-size: 8pt;
            vertical-align: middle;
        }
        
        .footer { 
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 7pt; 
            color: #999;
            text-align: right;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="main-title">Sistema Guataparo / Reporte de Huellas</div>
    
    <div class="header-container">
        <div class="info-col">
            <div class="info-item"><strong>Generado:</strong> <?php echo date('d/m/Y, H:i'); ?></div>
            <div class="info-item">
                <strong>Filtros / Búsqueda:</strong> 
                <?php 
                    $filtrosText = [];
                    if(!empty($_GET['cedula'])) $filtrosText[] = "Cédula: " . htmlspecialchars($_GET['cedula']);
                    if(!empty($_GET['busqueda'])) $filtrosText[] = "Búsqueda: " . htmlspecialchars($_GET['busqueda']);
                    
                    echo empty($filtrosText) ? 'Ninguno' : implode(' | ', $filtrosText);
                ?>
            </div>
            <div class="info-item">
                <strong>Estadísticas:</strong> 
                Socios con Huella: <?php echo $porcentajeHuellas['con_huella']; ?> |
                Socios sin Huella: <?php echo $porcentajeHuellas['sin_huella']; ?> |
                Cobertura: <?php echo $porcentajeHuellas['porcentaje_con_huella']; ?>%
            </div>
            <div class="info-item"><strong>Total de Registros:</strong> <?php echo count($huellas); ?></div>
        </div>
        <div class="logo-col">
            <img src="/assets/img/logo_guataparo.png" alt="Logo Guataparo">
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th width="15%">Fecha Registro</th>
                <th width="15%">Acción</th>
                <th width="20%">Cédula</th>
                <th width="35%">Socio</th>
                <th width="15%">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($huellas)): ?>
            <tr>
                <td colspan="5" class="text-center">No hay registros de huellas.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($huellas as $huella): ?>
                <tr>
                    <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($huella['created_at'])); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($huella['accion']); ?></td>
                    <td class="text-center">V-<?php echo htmlspecialchars($huella['cedula']); ?></td>
                    <td><?php echo htmlspecialchars($huella['nombre']); ?></td>
                    <td class="text-center">REGISTRADA</td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Generado por: <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Sistema'); ?> - Página 1
    </div>
</body>
</html>
