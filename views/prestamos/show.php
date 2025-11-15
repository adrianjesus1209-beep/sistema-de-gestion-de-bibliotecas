<?php
/**
 * Vista: Detalles de un Prestamo.
 *
 * Esta pagina muestra la informacion completa y detallada de un solo
 * prestamo. Es util tanto para que los usuarios vean el estado de sus
 * libros como para que los administradores consulten un registro especifico.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- El idioma principal del contenido es espanol -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres para acentos y simbolos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que la pagina se adapte a moviles -->
    <title>Detalles del Prestamo - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- Contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun a todo el sitio.
    include 'views/shared/header.php'; 
    ?>

    <h2>Detalles del Prestamo</h2>

    <?php 
    // Verificamos si el controlador encontro y nos paso los datos del prestamo.
    // El controlador ya se ha encargado de verificar si el usuario tiene permiso para ver esto.
    if (isset($prestamo)): 
    ?>
        <!-- Si tenemos los datos, los mostramos en una lista de parrafos. -->
        <p><strong>ID Prestamo:</strong> <?php echo htmlspecialchars($prestamo['id_prestamo']); ?></p>
        <p><strong>Libro:</strong> <?php echo htmlspecialchars($prestamo['titulo_libro']); ?></p>
        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($prestamo['nombre_usuario']); ?></p>
        <p><strong>Fecha de Prestamo:</strong> <?php echo htmlspecialchars($prestamo['fecha_prestamo']); ?></p>
        <p><strong>Fecha de Devolucion Esperada:</strong> <?php echo htmlspecialchars($prestamo['fecha_devolucion_esperada']); ?></p>
        <!-- Si la fecha de devolucion real es nula, mostramos 'Pendiente' en su lugar. -->
        <p><strong>Fecha de Devolucion Real:</strong> <?php echo htmlspecialchars($prestamo['fecha_devolucion_real'] ?? 'Pendiente'); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($prestamo['estado']); ?></p>

        <?php 
        // Verificamos si el usuario es un administrador y si el prestamo aun esta activo.
        if (isset($_SESSION['user_id']) && $_SESSION['rol_id'] == 1 && ($prestamo['estado'] == 'prestado' || $prestamo['estado'] == 'atrasado')):
        ?>
            <!-- Si se cumplen las condiciones, mostramos un boton de accion para registrar la devolucion. -->
            <!-- El 'onclick' pide confirmacion al administrador antes de proceder. -->
            <p><a href="index.php?controller=prestamo&action=devolver&id=<?php echo htmlspecialchars($prestamo['id_prestamo']); ?>" onclick="return confirm('Confirmar devolucion de este libro?');">Marcar como Devuelto</a></p>
        <?php 
        endif; 
        ?>

    <?php 
    // Si la variable $prestamo no existe...
    else: 
    ?>
        <!-- ...mostramos un mensaje generico de error. -->
        <p>Prestamo no encontrado o no tienes permiso para verlo.</p>
    <?php 
    endif; // Fin del if-else 
    ?>

    <!-- Un enlace para que el usuario pueda volver facilmente a la lista de prestamos. -->
    <p><a href="index.php?controller=prestamo&action=index">Volver a la lista de prestamos</a></p>

    <?php 
    // Incluimos el pie de pagina comun.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>