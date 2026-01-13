<?php
/* 
---------------------------------------------------
 ARCHIVO: admin/logout.php
 FUNCIÓN:
 - Cerrar sesión del administrador
---------------------------------------------------
*/
session_start();
session_destroy();
header("Location: login.php");

