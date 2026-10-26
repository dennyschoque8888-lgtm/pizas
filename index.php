<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $nombre = mb_substr($nombre, 0, 80);
    $correo = mb_substr($correo, 0, 120);
    $mensaje = mb_substr($mensaje, 0, 1000);

    if (!$conectado) {
        $error_form = 'No se pudo guardar: la base de datos no está conectada.';
    } elseif ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $mensaje === '') {
        $error_form = 'Revisa el nombre, el correo y el mensaje.';
    } else {
        if (guardar_mensaje($nombre, $correo, $mensaje)) {
            header('Location: index.php?enviado=1#contactos');
            exit;
        }
        $error_form = 'No se pudo guardar el mensaje. Inténtalo otra vez.';
    }
}

$chefs = obtener($conexion, $conectado, 'SELECT nombre, especialidad, foto FROM chefs ORDER BY id');
$pizzas = obtener($conexion, $conectado, 'SELECT nombre, descripcion, precio, foto, del_dia FROM menu ORDER BY del_dia DESC, id');
$testimonios = obtener($conexion, $conectado, 'SELECT nombre, comentario, estrellas FROM testimonios ORDER BY id');
$horario = obtener($conexion, $conectado, 'SELECT dia, horas FROM horario ORDER BY id');
$con_datos = $conectado && $chefs && $pizzas && $testimonios && $horario;
if (!$chefs) {
    $chefs = respaldo('chefs');
}
if (!$pizzas) {
    $pizzas = respaldo('menu');
}
if (!$testimonios) {
    $testimonios = respaldo('testimonios');
}
if (!$horario) {
    $horario = respaldo('horario');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pizzería El Sabor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="estado">
        <?php if ($con_datos): ?>
            conexion establecida
        <?php else: ?>
            <span class="text-danger">sin conexion a la base de datos</span>
        <?php endif; ?>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#inicio">
                <i class="fa-solid fa-pizza-slice"></i>
                Pizzería El Sabor
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav"
                aria-controls="menuNav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#menu">Menú del día</a></li>
                    <li class="nav-item"><a class="nav-link" href="#chefs">Chefs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonios">Testimonios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contactos">Contactos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#horario">Horario</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="inicio" class="hero">
        <div class="velo"></div>
        <div class="hero-texto">
            <h1>PIZZERÍA EL SABOR</h1>
            <p>pizzas al horno de leña de nuestra casa</p>
            <a href="#menu" class="btn btn-primary">Ver menú</a>
        </div>
    </header>

    <section class="bienvenida">
        <div class="velo"></div>
        <div class="contenido">
            <h2>¡Bienvenidos a la Pizzería El Sabor!</h2>
            <p>Descubre una experiencia culinaria única.</p>
        </div>
    </section>

    <section id="chefs" class="seccion">
        <div class="container">
            <h2>Nuestros Chefs</h2>
            <div class="row g-4">
                <?php foreach ($chefs as $chef): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <img src="<?php echo e($chef['foto']); ?>" class="foto-chef" alt="<?php echo e($chef['nombre']); ?>">
                            <div class="card-body text-center">
                                <h3 class="h5 card-title mb-1"><?php echo e($chef['nombre']); ?></h3>
                                <p class="card-text text-secondary mb-0"><?php echo e($chef['especialidad']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="menu" class="seccion">
        <div class="container">
            <h2>Menú del día</h2>
            <div class="row g-4">
                <?php foreach ($pizzas as $pizza): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 w-100">
                            <img src="<?php echo e($pizza['foto']); ?>" class="foto-pizza" alt="<?php echo e($pizza['nombre']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h3 class="h5 card-title">
                                    <?php echo e($pizza['nombre']); ?>
                                    <?php if ((int) $pizza['del_dia'] === 1): ?>
                                        <span class="badge bg-warning text-dark">Del día</span>
                                    <?php endif; ?>
                                </h3>
                                <p class="card-text text-secondary flex-grow-1"><?php echo e($pizza['descripcion']); ?></p>
                                <p class="precio">Bs. <?php echo number_format((float) $pizza['precio'], 0); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="testimonios" class="seccion bg-light pb-4">
        <div class="container">
            <h2>Testimonios</h2>
            <div class="row g-4">
                <?php foreach ($testimonios as $item): ?>
                    <div class="col-md-4 d-flex">
                        <div class="card h-100 w-100">
                            <div class="card-body">
                                <div class="estrellas">
                                    <?php
                                    $n = max(1, min(5, (int) $item['estrellas']));
                                    echo str_repeat('★', $n) . str_repeat('☆', 5 - $n);
                                    ?>
                                </div>
                                <p class="card-text">"<?php echo e($item['comentario']); ?>"</p>
                            </div>
                            <div class="card-footer text-secondary"><?php echo e($item['nombre']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="contactos" class="seccion">
        <div class="container">
            <h2>Contactos</h2>
            <p class="text-center text-secondary mb-4">Estamos en Oruro. Escríbenos y te respondemos.</p>

            <?php if (isset($_GET['enviado'])): ?>
                <div class="alert alert-success">Tu mensaje se guardó. Gracias por escribirnos.</div>
            <?php endif; ?>
            <?php if (!empty($error_form)): ?>
                <div class="alert alert-danger"><?php echo e($error_form); ?></div>
            <?php endif; ?>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body datos-local">
                            <h3 class="h5">Pizzería El Sabor</h3>
                            <p><i class="fa-solid fa-location-dot me-2"></i>Calle Sucre N° 320, Oruro</p>
                            <p><i class="fa-solid fa-phone me-2"></i>252-4580</p>
                            <p><i class="fa-brands fa-whatsapp me-2"></i>712-34567</p>
                            <p class="mb-0"><i class="fa-solid fa-envelope me-2"></i>pedidos@pizzeriaelsabor.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body p-4">
                            <form action="index.php#contactos" method="post">
                                <div class="mb-3">
                                    <label class="form-label" for="nombre">Nombre</label>
                                    <input class="form-control" type="text" id="nombre" name="nombre" maxlength="80" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="correo">Correo</label>
                                    <input class="form-control" type="email" id="correo" name="correo" maxlength="120" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="mensaje">Mensaje</label>
                                    <textarea class="form-control" id="mensaje" name="mensaje" rows="4" maxlength="1000" required></textarea>
                                </div>
                                <button class="btn btn-primary" type="submit">Enviar mensaje</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="horario" class="seccion bg-light pb-5">
        <div class="container">
            <div class="horario-caja">
                <h3><i class="fa-regular fa-clock me-2"></i>Horario de atención</h3>
                <div class="row">
                    <?php foreach ($horario as $turno): ?>
                        <div class="col-6 col-md-3">
                            <div class="bloque-hora">
                                <strong><?php echo e($turno['dia']); ?></strong>
                                <span><?php echo e($turno['horas']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p class="mb-0">&copy; 2026 Pizzería El Sabor, Oruro. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
