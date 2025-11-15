<?php
/**
 * Vista: Formulario para Editar un Libro.
 *
 * Esta pagina muestra un formulario pre-llenado con la informacion de un
 * libro existente, permitiendo a los administradores modificar sus detalles
 * y sus autores asociados.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de HTML5 -->
<html lang="es"> <!-- El contenido principal esta en espanol -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para diseno adaptable -->
    <title>Editar Libro - Biblioteca</title> <!-- Titulo de la pestana -->
</head>
<body>
    <!-- Contenido visible -->

    <?php 
    // Incluimos el encabezado para mantener la consistencia.
    include 'views/shared/header.php'; 
    ?>

    <h2>Editar Libro</h2>

    <?php 
    // Verificamos si el controlador nos paso la informacion del libro.
    // Si no, significa que el libro con el ID solicitado no se encontro.
    if (isset($libro)): 
    ?>
    
    <!-- El formulario de edicion. -->
    <!-- 'action' apunta a la funcion 'edit' del controlador 'libro'. -->
    <form action="index.php?controller=libro&action=edit" method="POST">
        
        <!-- Campo oculto para enviar el ID del libro que se esta editando. -->
        <!-- Es esencial para que el controlador sepa que registro actualizar en la BD. -->
        <input type="hidden" name="id_libro" value="<?php echo htmlspecialchars($libro['id_libro']); ?>">

        <label for="titulo">Titulo:</label><br>
        <!-- El atributo 'value' se llena con el titulo actual del libro. -->
        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>" required><br><br>

        <label for="isbn">ISBN:</label><br>
        <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($libro['isbn']); ?>" required><br><br>

        <label for="anio_publicacion">Anio de Publicacion:</label><br>
        <input type="number" id="anio_publicacion" name="anio_publicacion" min="1000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($libro['anio_publicacion']); ?>" required><br><br>

        <label for="descripcion">Descripcion:</label><br>
        <!-- En un 'textarea', el valor inicial se pone entre las etiquetas de apertura y cierre. -->
        <textarea id="descripcion" name="descripcion" rows="5" cols="40"><?php echo htmlspecialchars($libro['descripcion']); ?></textarea><br><br>

        <label for="autores_ids[]">Autor(es):</label><br>
        <!-- Lista de seleccion multiple para los autores. -->
        <select id="autores_ids[]" name="autores_ids[]" multiple size="5" required>
            <?php 
            // Verificamos que tengamos la lista de todos los autores disponibles.
            if (isset($autores_disponibles) && $autores_disponibles->num_rows > 0): 
                // Recorremos la lista completa de autores.
                while ($autor = $autores_disponibles->fetch_assoc()):
                    // Para cada autor, comprobamos si su ID esta en la lista de autores
                    // que ya estan asociados con este libro.
                    // 'in_array' busca un valor dentro de un array.
                    // Si el autor esta asociado, anadimos el atributo 'selected' a su 'option'.
                    $selected = in_array($autor['id_autor'], $libro['ids_autores'] ?? []) ? 'selected' : '';
            ?>
                    <option value="<?php echo htmlspecialchars($autor['id_autor']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?>
                    </option>
            <?php 
                endwhile; 
            else: 
            ?>
                <!-- Mensaje por si no hay autores en el sistema. -->
                <option value="" disabled>No hay autores disponibles.</option>
            <?php 
            endif; 
            ?>
        </select><br>
        <small>Manten presionada la tecla Ctrl (o Command en Mac) para seleccionar varios autores.</small><br><br>

        <!-- Boton para guardar los cambios. -->
        <input type="submit" value="Actualizar Libro">
    </form>
    
    <?php 
    // Si la variable $libro no existia.
    else: 
    ?>
        <p>Libro no encontrado.</p>
    <?php 
    endif; 
    ?>

    <!-- Enlace para volver a la lista general de libros. -->
    <p><a href="index.php?controller=libro&action=index">Volver a la lista de libros</a></p>

    <?php 
    // Incluimos el pie de pagina.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>