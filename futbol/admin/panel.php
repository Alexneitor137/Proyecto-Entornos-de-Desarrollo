<?php
/* 
---------------------------------------------------
 ARCHIVO: admin/panel.php
 FUNCIÓN:
 - Mostrar todas las reservas
 - Permitir borrarlas
---------------------------------------------------
*/
session_start();
require '../conexion.php';

// Seguridad: solo admins
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Obtener todas las reservas
$reservas = $pdo->query("
    SELECT * FROM reservas
    ORDER BY fecha, hora_inicio
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>PANEL DE ADMINISTRADOR</h2>
<a href="logout.php">Cerrar sesión</a>

<table border="1">
<tr>
    <th>Campo</th>
    <th>Cliente</th>
    <th>Fecha</th>
    <th>Horario</th>
    <th>Eliminar</th>
</tr>

<?php foreach ($reservas as $r): ?>
<tr>
    <td><?= $r['campo_id'] ?></td>
    <td><?= $r['nombre'] ?></td>
    <td><?= $r['fecha'] ?></td>
    <td><?= $r['hora_inicio'] ?> - <?= $r['hora_fin'] ?></td>
    <td>
        <a href="eliminar.php?id=<?= $r['id'] ?>"
           onclick="return confirm('¿Eliminar reserva?')">
           ❌
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>


