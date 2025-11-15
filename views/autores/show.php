<?php
/**
 * Vista: Detalles de un Autor.
 *
 * Esta pagina muestra la informacion completa de un autor especifico.
 * Dado que toda la seccion de autores es para administradores, esta
 * vista tambien es, por extension, solo para ellos.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- El idioma principal del contenido es espanol -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres para acentos y simbolos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que la pagina se adapte a moviles -->
    <title>Detalles del Autor - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- Contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun a todo el sitio.
    include 'views/shared/header.php'; 
    ?>

    <h2>Detalles del Autor</h2>

    <?php 
    // Verificamos si el controlador nos ha pasado la informacion del autor.
    // Si la variable $autor existe, significa que se encontro en la base de datos.
    if (isset($autor)): 
    ?>
        <!-- Si tenemos los datos, los mostramos en una lista de parrafos. -->
        <p><strong>ID:</strong> <?php echo htmlspecialchars($autor['id_autor']); ?></p>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($autor['nombre']); ?></p>
        <p><strong>Apellido:</strong> <?php echo htmlspecialchars($autor['apellido']); ?></p>
        <!-- Si la nacionalidad es nula, mostramos un texto alternativo. -->
        <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($autor['nacionalidad'] ?? 'No especificada'); ?></p>
        
        <!-- Como esta es una seccion de administracion, anadimos un enlace directo -->
        <!-- para editar este autor, lo cual es muy conveniente. -->
        <p><a href="index.php?controller=autor&action=edit&id=<?php echo htmlspecialchars($autor['id_autor']); ?>">Editar este Autor</a></p>

    <?php 
    // Si la variable $autor no existe...
    else: 
    ?>
        <!-- ...mostramos un mensaje indicando que no se encontro. -->
        <p>Autor no encontrado.</p>
    <?php 
    endif; // Fin del if-else 
    ?>

    <!-- Un enlace para que el administrador pueda volver facilmente a la lista completa. -->
    <p><a href="index.php?controller=autor&action=index">Volver a la lista de autores</a></p>

    <?php 
    // Incluimos el pie de pagina comun.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>