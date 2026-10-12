<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios - Impresión</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>SISTEMA DE GESTIÓN GUATAPARO</h2>
        <h3>Listado de Usuarios del Sistema</h3>
        <p>Fecha: <?php echo date('d/m/Y'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Puesto de Trabajo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                <td><?php echo htmlspecialchars($usuario['tipousuario']); ?></td>
                <td><?php echo htmlspecialchars($usuario['puestotrabajo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
