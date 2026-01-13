<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require '../conexion.php';

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
$stmt->execute([$id]);

header("Location: panel.php");
exit;
