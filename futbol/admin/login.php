<?php
/* 
---------------------------------------------------
 ARCHIVO: admin/login.php
 FUNCIÓN:
 - Mostrar formulario de acceso
---------------------------------------------------
*/
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Administrador</title>
</head>
<body>

<h2>Acceso Administrador</h2>

<form method="POST" action="auth.php">
    <input type="text" name="user" placeholder="Usuario" required>
    <input type="password" name="pass" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
</form>

</body>
</html>


