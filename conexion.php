<?php
// Primero intenta MySQL de XAMPP.
// Si MySQL no responde, usa la base SQLite de esta carpeta (datos/pizeria.sqlite).
$host = '127.0.0.1';
$usuario = 'root';
$clave = '';
$base = 'pizeria';

mysqli_report(MYSQLI_REPORT_OFF);
$conexion = mysqli_init();
$conectado = false;
$usando_sqlite = false;
$pdo = null;

if ($conexion) {
    $conexion->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1);
    $conectado = @$conexion->real_connect($host, $usuario, $clave, $base, 3306);
}

if ($conectado) {
    $conexion->set_charset('utf8mb4');
    $prueba = @$conexion->query('SELECT 1 FROM menu LIMIT 1');
    if (!$prueba) {
        $conectado = false;
    }
}

if (!$conectado) {
    try {
        $pdo = iniciar_sqlite();
        $usando_sqlite = true;
        $conectado = true;
    } catch (Exception $e) {
        $usando_sqlite = false;
        $conectado = false;
    }
}

function iniciar_sqlite()
{
    $dir = __DIR__ . '/datos';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $pdo = new PDO('sqlite:' . $dir . '/pizeria.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('CREATE TABLE IF NOT EXISTS chefs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        especialidad TEXT NOT NULL,
        foto TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        descripcion TEXT NOT NULL,
        precio REAL NOT NULL,
        foto TEXT NOT NULL,
        del_dia INTEGER NOT NULL DEFAULT 0
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS testimonios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        comentario TEXT NOT NULL,
        estrellas INTEGER NOT NULL DEFAULT 5
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS horario (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        dia TEXT NOT NULL,
        horas TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS mensajes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        correo TEXT NOT NULL,
        mensaje TEXT NOT NULL,
        fecha TEXT DEFAULT CURRENT_TIMESTAMP
    )');

    $cantidad = (int) $pdo->query('SELECT COUNT(*) FROM menu')->fetchColumn();
    if ($cantidad > 0) {
        return $pdo;
    }

    $insertar = $pdo->prepare('INSERT INTO chefs (nombre, especialidad, foto) VALUES (?, ?, ?)');
    foreach (respaldo('chefs') as $fila) {
        $insertar->execute([$fila['nombre'], $fila['especialidad'], $fila['foto']]);
    }

    $insertar = $pdo->prepare('INSERT INTO menu (nombre, descripcion, precio, foto, del_dia) VALUES (?, ?, ?, ?, ?)');
    foreach (respaldo('menu') as $fila) {
        $insertar->execute([$fila['nombre'], $fila['descripcion'], $fila['precio'], $fila['foto'], $fila['del_dia']]);
    }

    $insertar = $pdo->prepare('INSERT INTO testimonios (nombre, comentario, estrellas) VALUES (?, ?, ?)');
    foreach (respaldo('testimonios') as $fila) {
        $insertar->execute([$fila['nombre'], $fila['comentario'], $fila['estrellas']]);
    }

    $insertar = $pdo->prepare('INSERT INTO horario (dia, horas) VALUES (?, ?)');
    foreach (respaldo('horario') as $fila) {
        $insertar->execute([$fila['dia'], $fila['horas']]);
    }

    return $pdo;
}

function obtener($conexion, $conectado, $sql)
{
    global $pdo, $usando_sqlite;

    if ($usando_sqlite && $pdo) {
        $filas = [];
        foreach ($pdo->query($sql) as $fila) {
            $filas[] = $fila;
        }
        return $filas;
    }

    $filas = [];
    if (!$conectado || !$conexion) {
        return $filas;
    }

    $resultado = $conexion->query($sql);
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }
    }

    return $filas;
}

function guardar_mensaje($nombre, $correo, $mensaje)
{
    global $conexion, $conectado, $pdo, $usando_sqlite;

    if ($usando_sqlite && $pdo) {
        $stmt = $pdo->prepare('INSERT INTO mensajes (nombre, correo, mensaje) VALUES (?, ?, ?)');
        return $stmt->execute([$nombre, $correo, $mensaje]);
    }

    if (!$conectado || !$conexion) {
        return false;
    }

    $stmt = $conexion->prepare('INSERT INTO mensajes (nombre, correo, mensaje) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $nombre, $correo, $mensaje);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function respaldo($tabla)
{
    $datos = [
        'chefs' => [
            ['nombre' => 'Mateo Vargas', 'especialidad' => 'Maestro pizzero', 'foto' => 'img/chef1.jpg'],
            ['nombre' => 'Andrés Choque', 'especialidad' => 'Horno de leña', 'foto' => 'img/chef2.jpg'],
            ['nombre' => 'Carlos Mamani', 'especialidad' => 'Masas y salsas', 'foto' => 'img/chef3.jpg'],
        ],
        'menu' => [
            ['nombre' => 'Margherita', 'descripcion' => 'Salsa de tomate, mozzarella y albahaca fresca.', 'precio' => 45, 'foto' => 'img/pizza1.jpg', 'del_dia' => 1],
            ['nombre' => 'Pepperoni', 'descripcion' => 'Mozzarella y pepperoni horneado.', 'precio' => 52, 'foto' => 'img/pizza2.jpg', 'del_dia' => 1],
            ['nombre' => 'Hawaiana', 'descripcion' => 'Pollo, piña, mozzarella y cilantro.', 'precio' => 50, 'foto' => 'img/pizza3.jpg', 'del_dia' => 0],
            ['nombre' => 'Extra queso', 'descripcion' => 'Mucha mozzarella fundida con pepperoni.', 'precio' => 58, 'foto' => 'img/pizza4.jpg', 'del_dia' => 1],
            ['nombre' => 'Rúcula', 'descripcion' => 'Salsa de tomate, rúcula y queso fresco.', 'precio' => 60, 'foto' => 'img/pizza5.jpg', 'del_dia' => 0],
            ['nombre' => 'Pepperoni familiar', 'descripcion' => 'Pizza grande de pepperoni para compartir.', 'precio' => 65, 'foto' => 'img/pizza6.jpg', 'del_dia' => 0],
        ],
        'testimonios' => [
            ['nombre' => 'Ana Gutiérrez', 'comentario' => 'La masa está crujiente y el queso se estira rico. Volvemos cada viernes.', 'estrellas' => 5],
            ['nombre' => 'Luis Choque', 'comentario' => 'Pedimos la del día y llegó caliente. Buena atención.', 'estrellas' => 5],
            ['nombre' => 'Valeria Rojas', 'comentario' => 'La hawaiana nos gustó mucho. El local es cómodo para la familia.', 'estrellas' => 4],
        ],
        'horario' => [
            ['dia' => 'Lunes a jueves', 'horas' => '12:00 - 22:30'],
            ['dia' => 'Viernes', 'horas' => '12:00 - 23:30'],
            ['dia' => 'Sábado', 'horas' => '12:00 - 23:30'],
            ['dia' => 'Domingo', 'horas' => '12:00 - 21:00'],
        ],
    ];

    return $datos[$tabla] ?? [];
}
