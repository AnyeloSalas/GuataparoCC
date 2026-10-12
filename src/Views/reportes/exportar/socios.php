<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Socios - Sistema Guataparo</title>
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
            -webkit-print-color-adjust: exact; /* Fuerza a imprimir los colores de fondo */
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
            background-color: #a4c2f4; /* azul claro como en la imagen */
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
    <div class="main-title">Sistema Guataparo / Reporte de Socios</div>
    
    <div class="header-container">
        <div class="info-col">
            <div class="info-item"><strong>Generado:</strong> <?php echo date('d/m/Y, H:i'); ?></div>
            <div class="info-item">
                <strong>Rango de fechas / Filtros:</strong> 
                <?php 
                    $filtrosText = [];
                    if(!empty($_GET['estado'])) $filtrosText[] = "Estado: " . htmlspecialchars($_GET['estado']);
                    if(!empty($_GET['profesion'])) $filtrosText[] = "Categoría: " . htmlspecialchars($_GET['profesion']);
                    if(!empty($_GET['tipo_accion'])) $filtrosText[] = "Tipo Acción: " . htmlspecialchars($_GET['tipo_accion']);
                    if(!empty($_GET['sexo'])) $filtrosText[] = "Género: " . htmlspecialchars($_GET['sexo']);
                    if(!empty($_GET['cedula'])) $filtrosText[] = "Cédula: " . htmlspecialchars($_GET['cedula']);
                    if(!empty($_GET['accion'])) $filtrosText[] = "Nro Acción: " . htmlspecialchars($_GET['accion']);
                    if(!empty($_GET['busqueda'])) $filtrosText[] = "Búsqueda: " . htmlspecialchars($_GET['busqueda']);
                    
                    if(!empty($_GET['fecha_desde']) || !empty($_GET['fecha_hasta'])) {
                        $desde = !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '...';
                        $hasta = !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '...';
                        $filtrosText[] = htmlspecialchars($desde . " / " . $hasta);
                    }
                    echo empty($filtrosText) ? 'Ninguno' : implode(' | ', $filtrosText);
                ?>
            </div>
            <div class="info-item"><strong>Total de Socios:</strong> <?php echo count($socios); ?></div>
        </div>
        <div class="logo-col">
            <img src="/assets/img/logo_guataparo.png" alt="Logo Guataparo">
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th width="8%"># Acción</th>
                <th width="12%">Cédula</th>
                <th width="33%">Nombre Completo</th>
                <th width="12%">Categoría</th>
                <th width="10%">Estado</th>
                <th width="10%">Teléfono</th>
                <th width="15%">Correo Electrónico</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($socios as $socio): ?>
            <tr>
                <td class="text-center"><?php echo htmlspecialchars($socio['accion']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars(($socio['nacionalidad'] ?? 'V') . '-' . ($socio['cedula'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($socio['nombre']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($socio['profesion'] ?? 'Sin Profesión'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars(ucfirst(strtolower($socio['estado']))); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($socio['telefonos'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($socio['email'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Generado por: <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Sistema'); ?> - Página 1
    </div>
</body>
</html>
