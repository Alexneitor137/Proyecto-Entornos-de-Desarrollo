<?php
/* 
---------------------------------------------------
 ARCHIVO: admin/auth.php
 FUNCIÓN:
 - Verificar usuario y contraseña
 - Crear sesión
---------------------------------------------------
*/
session_start();

// Credenciales (hardcodeadas por simplicidad)
$USER = "admin";
$PASS = "1234";

// Comparar datos enviados
if ($_POST['user'] === $USER && $_POST['pass'] === $PASS) {

    // Crear sesión de administrador
    $_SESSION['admin'] = true;

    // Redirigir al panel
    header("Location: panel.php");
} else {
    echo "Usuario o contraseña incorrectos";
}


