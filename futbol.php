<?php
require 'conexion.php';

/* =========================
   GUARDAR RESERVA
========================= */
if (isset($_POST['confirmar'])) {

    $campo_id = (int) $_POST['campo'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora'];
    $duracion = (int) $_POST['duracion'];

$precio_por_hora = 50;
$precio_total = $duracion * $precio_por_hora;

$hora_fin = date("H:i", strtotime("$hora_inicio + $duracion hours"));


    // COMPROBAR SOLAPAMIENTO
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM reservas
        WHERE campo_id = ?
        AND fecha = ?
        AND (? < hora_fin AND ? > hora_inicio)
    ");
    $check->execute([$campo_id, $fecha, $hora_inicio, $hora_fin]);

    if ($check->fetchColumn() > 0) {
        $error = "❌ HORARIO OCUPADO. EL COSTE HABRÍA SIDO DE {$precio_total} €.";

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO reservas
(campo_id, nombre, email, telefono, fecha, hora_inicio, hora_fin, duracion, precio)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
    $campo_id,
    $_POST['nombre'],
  $_POST['email'],
  $_POST['telefono'],
    $fecha,
    $hora_inicio,
    $hora_fin,
    $duracion,
  $precio_total
]);

        $mensaje_exito = "✅ RESERVA CONFIRMADA PARA "
               . strtoupper($_POST['nombre'])
               . " · IMPORTE TOTAL: {$precio_total} €";

    }
}

/* =========================
   ESTADO DE CAMPOS
========================= */
function obtener_estado($campo_id, $pdo, $fecha = null) {
    $fecha = $fecha ?? date('Y-m-d');
    $ahora = date('H:i');

    $stmt = $pdo->prepare("
        SELECT hora_fin
        FROM vista_reservas_detalle
        WHERE campo_id = ?
        AND fecha = ?
        AND ? BETWEEN hora_inicio AND hora_fin
        LIMIT 1
    ");

    $stmt->execute([$campo_id, $fecha, $ahora]);

    if ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $restante = round((strtotime($r['hora_fin']) - strtotime($ahora)) / 60);
        return ['ocupado' => true, 'libre_en' => $restante];
    }

    return ['ocupado' => false];
}

?>



<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>SOFIVE MANISES | RESERVAS</title>
<style>
:root {
--neon-green:#00ff00;
--dark-bg:#0b0c24;
--card-bg:#161b33;
--text-gray:#a0a0a0;
}
body{background:var(--dark-bg);color:white;font-family:'Segoe UI',sans-serif;margin:0;padding:20px;}
.container{max-width:1000px;margin:auto;}
h1{color:var(--neon-green);font-size:2.5rem;text-transform:uppercase;margin-bottom:0;}
.location-header{color:var(--text-gray);margin-bottom:20px;border-bottom:1px solid #333;padding-bottom:10px;text-transform:uppercase;letter-spacing:1px;}
.pago-aviso{background:rgba(0,255,0,.1);border:1px dashed var(--neon-green);color:var(--neon-green);padding:15px;text-align:center;font-weight:bold;margin-bottom:30px;text-transform:uppercase;}
.grid-campos{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:30px;}
.campo-card{background:var(--card-bg);border:1px solid #333;padding:20px;border-radius:4px;transition:.3s;cursor:pointer;}
.campo-card:hover:not(.ocupado){border-color:var(--neon-green);}
.campo-card.selected{border-color:var(--neon-green);background:#1a2a24;}
.campo-nombre{display:block;font-size:1.1rem;font-weight:bold;margin-top:5px;}
.status-pill{display:inline-block;margin-top:10px;font-size:.7rem;padding:4px 8px;text-transform:uppercase;}
.status-available{color:var(--neon-green);}
.status-busy{color:#ff4444;}
.form-section{background:var(--card-bg);padding:30px;border-radius:8px;}
input,select{background:#050614;border:1px solid #333;color:white;padding:12px;margin-bottom:15px;width:100%;box-sizing:border-box;}
input:focus{border-color:var(--neon-green);outline:none;}
.btn-reserve{background:var(--neon-green);color:black;border:none;padding:20px;width:100%;font-weight:bold;text-transform:uppercase;cursor:pointer;font-size:1rem;}
.contact-info{margin-top:50px;padding:20px;border-top:1px solid #333;display:flex;justify-content:space-around;color:var(--text-gray);font-size:.9rem;}
.contact-item strong{color:var(--neon-green);display:block;margin-bottom:5px;}
.ocupado{opacity:.4;cursor:not-allowed;border-color:#ff4444;}
input[type="radio"]{display:none;}
.success-msg{background:var(--neon-green);color:black;padding:15px;margin-bottom:20px;text-align:center;font-weight:bold;}
#horas{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;margin-bottom:10px;}
#horas button{padding:8px;border:none;cursor:pointer;}
#precio{margin-bottom:15px;font-weight:bold;}
</style>
</head>
<body>
<div class="container">

<h1>SOFIVE MANISES</h1>
<div class="location-header">NAVE AEROPUERTO MANISES, VALENCIA</div>
<div class="pago-aviso">EL PAGO SE REALIZA AL LLEGAR AL ESTABLECIMIENTO</div>

<?php if(isset($mensaje_exito)) echo "<div class='success-msg'>$mensaje_exito</div>"; ?>

<?php if(isset($error)) echo "<div class='success-msg' style='background:#400;color:#ff4444;'>$error</div>"; ?>


<form method="POST">
<div class="grid-campos">

<?php
$imagenes = [
 "futbol.img",
 "futbol2.img",
 "futbol3.img",
 "futbol.img",
 "futbol2.img",
 "futbol3.img"
];

for($i=1; $i<=6; $i++):
$estado = obtener_estado($i, $pdo);
?>

<label class="campo-card <?php echo $estado['ocupado'] ? 'ocupado' : ''; ?>">

 <img src="<?php echo $imagenes[$i-1]; ?>"
 style="width:100%;height:140px;object-fit:cover;border-radius:6px;margin-bottom:12px;">

 <?php if(!$estado['ocupado']): ?>
 <input type="radio" name="campo" value="<?php echo $i; ?>" required>
 <?php endif; ?>

 <span style="font-size:.65rem;color:var(--text-gray)">MANISES</span>
 <span class="campo-nombre">CAMPO 0<?php echo $i; ?></span>

 <?php if($estado['ocupado']): ?>
 <span class="status-pill status-busy">● OCUPADO (Libre en <?php echo $estado['libre_en']; ?>m)</span>
 <?php else: ?>
 <span class="status-pill status-available">● DISPONIBLE</span>
 <?php endif; ?>

</label>

<?php endfor; ?>

</div>


<div class="form-section">
<h3 style="color:var(--neon-green);margin-top:0">INFORMACIÓN DEL CLIENTE</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
<input type="text" name="nombre" placeholder="NOMBRE COMPLETO" required>
<input type="email" name="email" placeholder="CORREO ELECTRÓNICO" required>
<input type="tel" name="telefono" placeholder="NÚMERO DE TELÉFONO" required>
<input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
<input type="hidden" name="hora" id="hora" required>
<select name="duracion">
<option value="1">1 HORA DE JUEGO</option>
<option value="2">2 HORAS DE JUEGO</option>
</select>
<div id="horas"></div>
<div id="precio">Precio: 50 € / hora</div>
</div>
<button type="submit" name="confirmar" class="btn-reserve">Confirmar Reserva Online</button>
</div>
</form>

<div class="contact-info">
<div class="contact-item"><strong>EMAIL</strong>sofivemanisesvalencia@gmail.es</div>
<div class="contact-item"><strong>TELÉFONO</strong>690 900 200</div>
<div class="contact-item"><strong>UBICACIÓN</strong>Nave aeropuerto Manises, Valencia</div>
<div class="contact-item"><strong>HORARIO</strong>Lunes a Domingo 09:00 a 01:00</div>
</div>
</div>
<script>
const PRECIO=50;


document.querySelectorAll('.campo-card').forEach(c=>{
c.addEventListener('click',()=>{
document.querySelectorAll('.campo-card').forEach(x=>x.classList.remove('selected'));
c.classList.add('selected');
document.querySelector('#precio').innerText='Precio: '+PRECIO+' € / hora';
//generarHoras();
});
});

/document.querySelector('[name="fecha"]').addEventListener('change',generarHoras);/
document.querySelector('[name="duracion"]').addEventListener('change',e=>{
let dur=e.target.value;
document.querySelector('#precio').innerText='Precio: '+(PRECIO*dur)+' €';
});
</script>
</body>
</html>
