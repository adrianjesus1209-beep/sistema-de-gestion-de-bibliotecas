<?php
/**
 * Vista: Detalles de un Libro.
 *
 * Esta pagina muestra toda la informacion disponible de un libro especifico.
 * Es una vista publica, accesible para cualquier visitante o usuario del sistema.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de HTML5 -->
<html lang="es"> <!-- Idioma principal del contenido -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para diseno adaptable -->
    <title>Detalles del Libro - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- Contenido visible -->

    <?php 
    // Incluimos el encabezado comun para mantener la consistencia del sitio.
    include 'views/shared/header.php'; 
    ?>

    <h2>Detalles del Libro</h2>

    <?php 
    // Verificamos si el controlador nos ha pasado la informacion del libro.
    // Si la variable $libro existe, significa que se encontro el libro en la BD.
    if (isset($libro)): 
    ?>
        <!-- Si el libro existe, mostramos sus detalles usando parrafos. -->
        <p><strong>ID:</strong> <?php echo htmlspecialchars($libro['id_libro']); ?></p>
        <p><strong>Titulo:</strong> <?php echo htmlspecialchars($libro['titulo']); ?></p>
        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($libro['isbn'] ?? 'N/A'); ?></p>
        <p><strong>Anio de Publicacion:</strong> <?php echo htmlspecialchars($libro['anio_publicacion'] ?? 'N/A'); ?></p>
        <p><strong>Descripcion:</strong> 
            <?php 
            // 'nl2br' es una funcion de PHP que convierte los saltos de linea (\n)
            // en etiquetas HTML <br>, para que el formato del texto se respete.
            echo nl2br(htmlspecialchars($libro['descripcion'] ?? 'Sin descripcion.')); 
            ?>
        </p>
        <p><strong>Autor(es):</strong> <?php echo htmlspecialchars($libro['nombres_autores'] ?? 'N/A'); ?></p>
        <p><strong>Disponible:</strong> 
            <?php 
            // Operador ternario para mostrar 'Si' o 'No' basado en el valor booleano.
            echo $libro['disponible'] ? 'Si' : 'No'; 
            ?>
        </p>

        <?php 
        // Verificamos si el usuario actual es un administrador.
        if (isset($_SESSION['user_id']) && $_SESSION['rol_id'] == 1): 
        ?>
            <!-- Si es admin, le mostramos un enlace directo para editar este libro. -->
            <p><a href="index.php?controller=libro&action=edit&id=<?php echo htmlspecialchars($libro['id_libro']); ?>">Editar Libro</a></p>
        <?php 
        endif; 
        ?>
    
    <?php 
    // Si la variable $libro no existe...
    else: 
    ?>
        <!-- ...mostramos un mensaje indicando que no se encontro. -->
        <p>Libro no encontrado.</p>
    <?php 
    endif; 
    ?>

    <!-- Un enlace para que el usuario pueda volver facilmente al catalogo completo. -->
    <p><a href="index.php?controller=libro&action=index">Volver a la lista de libros</a></p>

    <?php 
    // Incluimos el pie de pagina comun.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>