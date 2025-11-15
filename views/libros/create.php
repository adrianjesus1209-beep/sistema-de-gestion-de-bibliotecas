<?php
/**
 * Vista: Formulario para Crear un Libro.
 *
 * Esta pagina proporciona el formulario que necesitan los administradores
 * para anadir un nuevo libro al catalogo de la biblioteca. Incluye campos
 * para la informacion del libro y una lista para seleccionar sus autores.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- Especifica el idioma del contenido -->
<head>
    <!-- La seccion 'head' contiene metadatos sobre el documento -->
    <meta charset="UTF-8"> <!-- Asegura la correcta visualizacion de caracteres especiales -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para un diseno adaptable a moviles -->
    <title>Crear Nuevo Libro - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- El 'body' contiene todo el contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun, que contiene el menu de navegacion.
    include 'views/shared/header.php'; 
    ?>

    <h2>Anadir un Nuevo Libro al Catalogo</h2>
    
    <!-- Este es el formulario para crear el libro. -->
    <!-- 'action' envia los datos al controlador 'libro' y su funcion 'create'. -->
    <!-- 'method="POST"' es el metodo estandar y seguro para enviar formularios. -->
    <form action="index.php?controller=libro&action=create" method="POST">
        
        <label for="titulo">Titulo:</label><br>
        <input type="text" id="titulo" name="titulo" required><br><br>

        <label for="isbn">ISBN:</label><br>
        <input type="text" id="isbn" name="isbn" required><br><br>

        <label for="anio_publicacion">Anio de Publicacion:</label><br>
        <!-- 'type="number"' muestra controles numericos en el navegador. -->
        <!-- 'min' y 'max' establecen un rango valido para el anio. El maximo es el anio actual. -->
        <input type="number" id="anio_publicacion" name="anio_publicacion" min="1000" max="<?php echo date('Y'); ?>" required><br><br>

        <label for="descripcion">Descripcion:</label><br>
        <!-- Un 'textarea' es ideal para textos mas largos como una sinopsis. -->
        <textarea id="descripcion" name="descripcion" rows="5" cols="40"></textarea><br><br>

        <label for="autores_ids[]">Autor(es):</label><br>
        <!-- Este es un campo de seleccion multiple. -->
        <!-- El 'name' termina en '[]' para que PHP lo reciba como un array de los IDs seleccionados. -->
        <!-- 'multiple' permite seleccionar mas de una opcion. -->
        <select id="autores_ids[]" name="autores_ids[]" multiple size="5" required>
            <?php 
            // Verificamos si la variable con los autores existe y tiene contenido.
            // El controlador debe pasar esta variable a la vista.
            if (isset($autores) && $autores->num_rows > 0): 
                // Si hay autores, los recorremos uno por uno.
                while ($autor = $autores->fetch_assoc()): 
            ?>
                    <!-- Por cada autor, creamos una opcion en la lista. -->
                    <!-- El 'value' sera el ID del autor, que es lo que se enviara. -->
                    <option value="<?php echo htmlspecialchars($autor['id_autor']); ?>">
                        <?php 
                        // El texto visible para el usuario sera el nombre completo del autor.
                        echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); 
                        ?>
                    </option>
            <?php 
                endwhile; 
            else: 
            ?>
                <!-- Si no hay autores en la base de datos, mostramos un mensaje util. -->
                <option value="" disabled>No hay autores disponibles. Por favor, crea uno primero.</option>
            <?php 
            endif; 
            ?>
        </select><br>
        <!-- Una pequena ayuda para el usuario sobre como seleccionar multiples opciones. -->
        <small>Manten presionada la tecla Ctrl (Windows/Linux) o Command (Mac) para seleccionar multiples autores.</small><br><br>

        <!-- El boton que envia el formulario completo. -->
        <input type="submit" value="Crear Libro">
    </form>
    
    <!-- Enlace para regresar a la lista de todos los libros. -->
    <p><a href="index.php?controller=libro&action=index">Volver a la lista de libros</a></p>

    <?php 
    // Incluimos el pie de pagina.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>