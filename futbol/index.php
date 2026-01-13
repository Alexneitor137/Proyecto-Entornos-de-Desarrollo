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
    $hora_fin = date("H:i", strtotime("$hora_inicio + $duracion hours"));

    // EVITAR SOLAPAMIENTOS
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM reservas
        WHERE campo_id = ?
        AND fecha = ?
        AND (? < hora_fin AND ? > hora_inicio)
    ");
    $check->execute([$campo_id, $fecha, $hora_inicio, $hora_fin]);

    if ($check->fetchColumn() > 0) {
        $error = "❌ HORARIO OCUPADO. SELECCIONA OTRA HORA.";
    } else {
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

        $mensaje_exito = "✅ RESERVA CONFIRMADA";
    }
}

/* =========================
   ESTADO CAMPOS
========================= */
function obtener_estado($campo_id, $pdo) {
    $fecha = date('Y-m-d');
    $hora = date('H:i');

    $stmt = $pdo->prepare("
        SELECT hora_fin FROM reservas
        WHERE campo_id = ?
        AND fecha = ?
        AND ? BETWEEN hora_inicio AND hora_fin
        LIMIT 1
    ");
    $stmt->execute([$campo_id, $fecha, $hora]);

    if ($r = $stmt->fetch()) {
        $restante = round((strtotime($r['hora_fin']) - strtotime($hora)) / 60);
        return ['ocupado'=>true, 'libre_en'=>$restante];
    }
    return ['ocupado'=>false];
}

/* =========================
   RESERVAS PARA JS
========================= */
$stmt = $pdo->query("
    SELECT campo_id AS campo, fecha, hora_inicio AS hora, hora_fin AS fin
    FROM reservas
");
$reservas_js = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reservas Fútbol</title>
<style>
body{background:#0b0c24;color:white;font-family:Arial;padding:20px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:15px}
.campo{border:1px solid #333;padding:15px}
.ocupado{opacity:.4}
button{padding:10px;margin:5px}
</style>
</head>
<body>

<h1>Reservas de Fútbol</h1>

<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<?php if(isset($mensaje_exito)) echo "<p style='color:lime'>$mensaje_exito</p>"; ?>

<form method="POST">
<div class="grid">
<?php for($i=1;$i<=6;$i++):
$estado = obtener_estado($i,$pdo);
?>
<label class="campo <?= $estado['ocupado']?'ocupado':'' ?>">
<input type="radio" name="campo" value="<?= $i ?>" <?= $estado['ocupado']?'disabled':'' ?> required>
Campo <?= $i ?><br>
<?= $estado['ocupado'] ? "Ocupado (".$estado['libre_en']." min)" : "Disponible" ?>
</label>
<?php endfor; ?>
</div>

<br>
<input type="text" name="nombre" placeholder="Nombre" required>
<input type="email" name="email" placeholder="Email" required>
<input type="tel" name="telefono" placeholder="Teléfono" required>
<input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
<input type="hidden" name="hora" id="hora" value="09:00">
<select name="duracion">
<option value="1">1 hora</option>
<option value="2">2 horas</option>
</select>

<button name="confirmar">Reservar</button>
</form>

<script>
let reservas = <?= json_encode($reservas_js) ?>;
</script>

</body>
</html>
