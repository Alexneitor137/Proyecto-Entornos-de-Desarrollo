<?php
session_start();
if (isset($_SESSION['admin'])) {
    header("Location: panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login Admin</title>
<style>
body{background:#0b0c24;color:white;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:#161b33;padding:30px;border-radius:8px;width:300px;}
input{width:100%;padding:10px;margin-bottom:15px;}
button{width:100%;padding:10px;background:#00ff00;border:none;font-weight:bold;}
</style>
</head>
<body>

<form method="POST" action="auth.php">
<h2>Panel Admin</h2>
<input type="text" name="user" placeholder="Usuario" required>
<input type="password" name="pass" placeholder="Contraseña" required>
<button type="submit">Entrar</button>
</form>

</body>
</html>
