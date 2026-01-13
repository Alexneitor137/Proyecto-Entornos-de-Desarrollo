<?php
session_start();

$USER_ADMIN = "admin";
$PASS_ADMIN = "1234"; // CAMBIAR EN PRODUCCIÓN

if ($_POST['user'] === $USER_ADMIN && $_POST['pass'] === $PASS_ADMIN) {
    $_SESSION['admin'] = true;
    header("Location: panel.php");
    exit;
}

die("❌ Acceso denegado");
