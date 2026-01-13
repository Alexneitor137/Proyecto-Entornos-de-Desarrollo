<?php
/* 
---------------------------------------------------
 ARCHIVO: admin/eliminar.php
 FUNCIÓN:
 - Borrar una reserva por ID
---------------------------------------------------
*/
session_start();
require '../conexion.php';

// Seguridad
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Obtener ID de la URL
$id = (int) $_GET['id'];

// Eliminar reserva
$stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
$stmt->execute([$id]);

// Volver al panel
header("Location: panel.php");
