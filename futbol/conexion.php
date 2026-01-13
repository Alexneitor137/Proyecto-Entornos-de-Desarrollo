<?php
// Dirección del servidor MySQL (en XAMPP siempre es localhost)
$host = "localhost";

// Nombre de la base de datos
$db = "futbol";

// Usuario por defecto de MySQL en XAMPP
$user = "root";

// Contraseña por defecto (vacía)
$pass = "";

try {
    // Creamos la conexión PDO a MySQL
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass
    );

    // Configuramos PDO para que muestre errores como excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    // Si la conexión falla, detenemos el programa y mostramos el error
    die("Error de conexión con la base de datos: " . $e->getMessage());
}



