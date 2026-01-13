<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require '../conexion.php';

$reservas = $pdo->query("
    SELECT id, campo_id, nombre, telefono, fecha, hora_inicio, hora_fin
    FROM reservas
    ORDER BY fecha DESC, hora_inicio DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin</title>
<style>
body{font-family:sans-serif;background:#0b0c24;color:white;padding:30px;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border-bottom:1px solid #333;}
a{color:#ff4444;text-decoration:none;}
.top{display:flex;justify-content:space-between;margin-bottom:20px;}
</style>
</head>
<body>

<div class="top">
<h2>Reservas</h2>
<a href="logout.php">Cerrar sesión</a>
</div>

<table>
<tr>
<th>Campo</th>
<th>Nombre</th>
<th>Teléfono</th>
<th>Fecha</th>
<th>Hora</th>
<th>Acción</th>
</tr>

<?php foreach($reservas as $r): ?>
<tr>
<td><?= $r['campo_id'] ?></td>
<td><?= htmlspecialchars($r['nombre']) ?></td>
<td><?= $r['telefono'] ?></td>
<td><?= $r['fecha'] ?></td>
<td><?= $r['hora_inicio'] ?> - <?= $r['hora_fin'] ?></td>
<td>
<a href="eliminar.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar reserva?')">🗑 Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
