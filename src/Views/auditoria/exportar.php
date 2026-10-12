<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría de Huellas - Sistema Guataparo</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #001540; padding-bottom: 15px; }
        .header h2 { margin: 0 0 5px 0; color: #001540; font-size: 18px; }
        .header p { margin: 2px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f8f9fa; color: #001540; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        tr:nth-child(even) { background-color: #fcfcfc; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .bg-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .bg-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .bg-primary { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        @media print {
            body { font-size: 10pt; padding: 0; }
            .no-print { display: none; }
            table { box-shadow: none; border-collapse: collapse; }
            th { -webkit-print-color-adjust: exact; background-color: #f8f9fa !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <img src="assets/img/logo_guataparo.png" alt="Logo" style="height: 50px; margin-bottom: 10px;">
        <h2>Reporte de Auditoría de Huellas Dactilares</h2>
        <p><strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Filtro Aplicado:</strong> <?php echo strtoupper(str_replace('_', ' ', $tipo)); ?> (Muestra de <?php echo count($logs); ?> registros)</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Evento</th>
                <th>Usuario</th>
                <th>Cédula</th>
                <th>Similitud</th>
                <th>Dirección IP</th>
                <th>Detalles Técnicos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
                <tr>
                    <td>
                        <strong><?php echo date('d/m/Y', strtotime($log['fecha_hora'])); ?></strong><br>
                        <span style="color: #666; font-size: 9px;"><?php echo date('H:i:s', strtotime($log['fecha_hora'])); ?></span>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'bg-primary';
                        if ($log['tipo_evento'] == 'login_exitoso') $badgeClass = 'bg-success';
                        else if ($log['tipo_evento'] == 'login_fallido') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper(str_replace('_', ' ', $log['tipo_evento'])); ?></span>
                    </td>
                    <td><strong><?php echo htmlspecialchars($log['usuario'] ?? 'Sistema'); ?></strong></td>
                    <td><?php echo htmlspecialchars($log['cedula'] ?? '-'); ?></td>
                    <td><?php echo $log['similitud_porcentaje'] ? number_format($log['similitud_porcentaje'], 1) . '%' : '-'; ?></td>
                    <td style="font-family: monospace; font-size: 10px;"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                    <td style="font-size: 9px; color: #444;"><?php echo htmlspecialchars($log['detalles'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($logs)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 20px;">No hay registros de auditoría para el filtro seleccionado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
