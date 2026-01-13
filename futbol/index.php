<?php
/****************************************************
 * INDEX.PHP
 * Página principal de reservas de campos de fútbol
 * Tecnologías:
 * - PHP 8
 * - MySQL (PDO)
 * - HTML + CSS
 * - JavaScript
 ****************************************************/

// Cargamos la conexión a la base de datos
// Este archivo crea la variable $pdo
require 'conexion.php';

/* =================================================
   PROCESAR FORMULARIO DE RESERVA (POST)
   ================================================= */

// Comprobamos si el formulario ha sido enviado
if (isset($_POST['confirmar'])) {

    // Campo seleccionado (1 a 6)
    $campo_id = (int) $_POST['campo'];

    // Fecha seleccionada (YYYY-MM-DD)
    $fecha = $_POST['fecha'];

    // Hora de inicio (HH:MM)
    $hora_inicio = $_POST['hora'];

    // Duración en horas (1 o 2)
    $duracion = (int) $_POST['duracion'];

    // Calculamos la hora de fin sumando la duración
    $hora_fin = date("H:i", strtotime("$hora_inicio + $duracion hours"));

    /* ---------------------------------------------
       COMPROBAR SOLAPAMIENTO DE RESERVAS
       ---------------------------------------------
       Se comprueba si existe alguna reserva:
       - del mismo campo
       - el mismo día
       - cuya franja horaria se cruce
    */

    $check = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reservas
        WHERE campo_id = ?
          AND fecha = ?
          AND (? < hora_fin AND ? > hora_inicio)
    ");

    // Ejecutamos la consulta con los valores reales
    $check->execute([
        $campo_id,
        $fecha,
        $hora_inicio,
        $hora_fin
    ]);

    // Si hay alguna coincidencia, no se puede reservar
    if ($check->fetchColumn() > 0) {
        $error = "❌ HORARIO OCUPADO. SELECCIONA OTRA HORA.";
    } 
    // Si no hay solapamiento, guardamos la reserva
    else {

        $stmt = $pdo->prepare("
            INSERT INTO reservas
            (campo_id, nombre, email, telefono, fecha, hora_inicio, hora_fin, duracion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $campo_id,
            $_POST['nombre'],
            $_POST['email'],
            $_POST['telefono'],
            $fecha,
            $hora_inicio,
            $hora_fin,
            $duracion
        ]);

        $mensaje_exito = "RESERVA CONFIRMADA PARA " . strtoupper($_POST['nombre']);
    }
}

/* =================================================
   FUNCIÓN PARA SABER SI UN CAMPO ESTÁ OCUPADO AHORA
   ================================================= */

function obtener_estado($campo_id, $pdo, $fecha = null) {

    // Si no se pasa fecha, usamos hoy
    $fecha = $fecha ?? date('Y-m-d');

    // Hora actual
    $ahora = date('H:i');

    // Buscamos si hay una reserva activa ahora mismo
    $stmt = $pdo->prepare("
        SELECT hora_fin
        FROM reservas
        WHERE campo_id = ?
          AND fecha = ?
          AND ? BETWEEN hora_inicio AND hora_fin
        LIMIT 1
    ");

    $stmt->execute([$campo_id, $fecha, $ahora]);

    // Si hay una reserva activa
    if ($r = $stmt->fetch()) {

        // Calculamos minutos restantes
        $restante = round(
            (strtotime($r['hora_fin']) - strtotime($ahora)) / 60
        );

        return [
            'ocupado' => true,
            'libre_en' => $restante
        ];
    }

    // Si no hay reservas activas
    return ['ocupado' => false];
}

/* =================================================
   OBTENER TODAS LAS RESERVAS PARA JAVASCRIPT
   ================================================= */

$stmt = $pdo->query("
    SELECT 
        campo_id AS campo,
        fecha,
        hora_inicio AS hora,
        hora_fin AS fin
    FROM reservas
");

// Convertimos las reservas en un array PHP
$reservas_js = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<title>SOFIVE MANISES | RESERVAS</title>

<!-- =====================
     ESTILOS CSS
     ===================== -->
<style>
:root{
--neon-green:#00ff00;
--dark-bg:#0b0c24;
--card-bg:#161b33;
--text-gray:#a0a0a0;
}
body{
background:var(--dark-bg);
color:white;
font-family:'Segoe UI',sans-serif;
margin:0;
padding:20px;
}
.container{max-width:1000px;margin:auto;}
.grid-campos{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;}
.campo-card{
background:var(--card-bg);
border:1px solid #333;
padding:20px;
cursor:pointer;
transition:.3s;
}
.campo-card.selected{border-color:var(--neon-green);}
.ocupado{opacity:.4;cursor:not-allowed;}
input,select{width:100%;padding:10px;margin-bottom:10px;}
.btn-reserve{
background:var(--neon-green);
border:none;
padding:15px;
font-weight:bold;
cursor:pointer;
}
#horas{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;}
#horas button{padding:8px;}
</style>
</head>

<body>
<div class="container">

<h1>SOFIVE MANISES</h1>

<!-- MENSAJES -->
<?php if(isset($error)): ?>
<div style="background:#ff4444;padding:10px"><?= $error ?></div>
<?php endif; ?>

<?php if(isset($mensaje_exito)): ?>
<div style="background:#00ff00;color:black;padding:10px">
<?= $mensaje_exito ?>
</div>
<?php endif; ?>

<form method="POST">

<!-- =====================
     CAMPOS DE FÚTBOL
     ===================== -->
<div class="grid-campos">
<?php
for($i=1;$i<=6;$i++):
$estado = obtener_estado($i,$pdo);
?>
<label class="campo-card <?= $estado['ocupado']?'ocupado':'' ?>">
<?php if(!$estado['ocupado']): ?>
<input type="radio" name="campo" value="<?= $i ?>" required>
<?php endif; ?>
<strong>CAMPO <?= $i ?></strong><br>
<?php if($estado['ocupado']): ?>
<span style="color:red">OCUPADO (<?= $estado['libre_en'] ?> min)</span>
<?php else: ?>
<span style="color:lime">DISPONIBLE</span>
<?php endif; ?>
</label>
<?php endfor; ?>
</div>

<!-- =====================
     FORMULARIO
     ===================== -->
<input type="text" name="nombre" placeholder="Nombre" required>
<input type="email" name="email" placeholder="Email" required>
<input type="tel" name="telefono" placeholder="Teléfono" required>
<input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>

<input type="hidden" name="hora" id="hora" required>

<select name="duracion">
<option value="1">1 hora</option>
<option value="2">2 horas</option>
</select>

<div id="horas"></div>

<button class="btn-reserve" name="confirmar">Confirmar Reserva</button>
</form>
</div>

<!-- =====================
     JAVASCRIPT
     ===================== -->
<script>

// Reservas recibidas desde PHP (MySQL → JSON)
let reservas = <?= json_encode($reservas_js) ?>;

// Generar horas disponibles
function generarHoras(){
let fecha=document.querySelector('[name="fecha"]').value;
let campo=document.querySelector('input[name="campo"]:checked')?.value;
if(!fecha || !campo) return;

let cont=document.getElementById('horas');
cont.innerHTML='';

for(let h=9;h<25;h++){
let hora=(h%24).toString().padStart(2,'0')+':00';
let ocupada=false;

reservas.forEach(r=>{
if(r.campo==campo && r.fecha==fecha){
let ini=new Date(fecha+' '+hora);
let rini=new Date(r.fecha+' '+r.hora);
let rfin=new Date(r.fecha+' '+r.fin);
if(ini<rfin && ini>=rini) ocupada=true;
}
});

let b=document.createElement('button');
b.type='button';
b.textContent=hora;
b.disabled=ocupada;
b.onclick=()=>document.getElementById('hora').value=hora;
cont.appendChild(b);
}
}

document.querySelectorAll('input[name="campo"]').forEach(e=>e.onclick=generarHoras);
document.querySelector('[name="fecha"]').onchange=generarHoras;
</script>

</body>
</html>

