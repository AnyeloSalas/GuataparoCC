<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Postulados - Sistema Guataparo</title>
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
    <div class="main-title">Sistema Guataparo / Reporte de Postulados</div>
    
    <div class="header-container">
        <div class="info-col">
            <div class="info-item"><strong>Generado:</strong> <?php echo date('d/m/Y, H:i'); ?></div>
            <div class="info-item">
                <strong>Rango de fechas / Filtros:</strong> 
                <?php 
                    $filtrosText = [];
                    if(!empty($_GET['tipomiembro'])) $filtrosText[] = "Tipo Miembro: " . htmlspecialchars($_GET['tipomiembro']);
                    if(isset($_GET['en_votacion']) && $_GET['en_votacion'] !== '') {
                        $filtrosText[] = "Votación: " . ($_GET['en_votacion'] == '1' ? 'EN VOTACIÓN' : 'NO EN VOTACIÓN');
                    }
                    if(!empty($_GET['cedula'])) $filtrosText[] = "Cédula: " . htmlspecialchars($_GET['cedula']);
                    if(!empty($_GET['busqueda'])) $filtrosText[] = "Búsqueda: " . htmlspecialchars($_GET['busqueda']);
                    
                    echo empty($filtrosText) ? 'Ninguno' : implode(' | ', $filtrosText);
                ?>
            </div>
            <div class="info-item"><strong>Total de Postulados:</strong> <?php echo count($postulados); ?></div>
        </div>
        <div class="logo-col">
            <img src="/assets/img/logo_guataparo.png" alt="Logo Guataparo">
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th width="12%">Cédula</th>
                <th width="28%">Nombre Completo</th>
                <th width="12%">Tipo Miembro</th>
                <th width="15%">Profesión</th>
                <th width="13%">Referencia</th>
                <th width="10%">Votación</th>
                <th width="10%">Correo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($postulados as $p): ?>
            <tr>
                <td class="text-center"><?php echo htmlspecialchars(($p['nacionalidad_codigo'] ?? 'V') . '-' . ($p['cedula'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($p['tipomiembro'] ?? 'Sin tipo'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($p['profesion'] ?? '-'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($p['tipo_referencia'] ?? '-'); ?><br><?php echo htmlspecialchars($p['referencia_id'] ?? ''); ?></td>
                <td class="text-center"><?php echo $p['en_votacion'] ? 'ACTIVO' : 'INACTIVO'; ?></td>
                <td><?php echo htmlspecialchars($p['correo_electronico'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Generado por: <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Sistema'); ?> - Página 1
    </div>
</body>
</html>
