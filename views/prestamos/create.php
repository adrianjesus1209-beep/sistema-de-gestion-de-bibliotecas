<?php
/**
 * Vista: Formulario para Crear un Prestamo.
 *
 * Esta es la pagina que utilizan los administradores para registrar la
 * salida de un libro. El formulario permite seleccionar un libro disponible,
 * un usuario registrado y establecer las fechas del prestamo.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- El idioma principal del contenido es espanol -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres para mostrar tildes y otros simbolos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que la pagina se vea bien en moviles -->
    <title>Registrar Nuevo Prestamo - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- Contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun para mantener la consistencia en el diseno.
    include 'views/shared/header.php'; 
    ?>

    <h2>Registrar Nuevo Prestamo</h2>
    
    <!-- El formulario para crear el prestamo. -->
    <!-- 'action' envia los datos al controlador 'prestamo' y a su funcion 'create'. -->
    <form action="index.php?controller=prestamo&action=create" method="POST">
        
        <label for="id_libro">Libro:</label><br>
        <!-- Un menu desplegable ('select') para elegir el libro a prestar. -->
        <select id="id_libro" name="id_libro" required>
            <option value="">Seleccione un libro</option>
            <?php 
            // Verificamos si el controlador nos paso una lista de libros disponibles.
            if (isset($libros_disponibles) && $libros_disponibles->num_rows > 0): 
                // Si hay, los recorremos uno por uno.
                while ($libro = $libros_disponibles->fetch_assoc()): 
            ?>
                    <!-- Cada libro disponible se convierte en una opcion del menu. -->
                    <!-- El 'value' es el ID del libro, que es lo que se envia al servidor. -->
                    <option value="<?php echo htmlspecialchars($libro['id_libro']); ?>">
                        <?php echo htmlspecialchars($libro['titulo']); // El texto visible es el titulo del libro. ?>
                    </option>
            <?php 
                endwhile; 
            else: 
            ?>
                <!-- Si no hay libros disponibles, mostramos un mensaje util. -->
                <option value="" disabled>No hay libros disponibles para prestar.</option>
            <?php 
            endif; 
            ?>
        </select><br><br>

        <label for="id_usuario">Usuario:</label><br>
        <!-- Menu desplegable para seleccionar al usuario que se llevara el libro. -->
        <select id="id_usuario" name="id_usuario" required>
            <option value="">Seleccione un usuario</option>
            <?php 
            // Verificamos si tenemos la lista de usuarios.
            if (isset($usuarios) && $usuarios->num_rows > 0): 
                // Recorremos la lista de usuarios.
                while ($usuario = $usuarios->fetch_assoc()):
            ?>
                    <!-- Cada usuario es una opcion en el menu. -->
                    <option value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">
                        <?php 
                        // Mostramos el nombre de usuario y su rol para identificarlo facilmente.
                        echo htmlspecialchars($usuario['nombre_usuario']) . ' (' . htmlspecialchars($usuario['nombre_rol']) . ')'; 
                        ?>
                    </option>
            <?php 
                endwhile; 
            else: 
            ?>
                <!-- Mensaje por si no hay usuarios registrados. -->
                <option value="" disabled>No hay usuarios registrados.</option>
            <?php 
            endif; 
            ?>
        </select><br><br>

        <label for="fecha_prestamo">Fecha de Prestamo:</label><br>
        <!-- 'type="date"' muestra un calendario en los navegadores modernos. -->
        <!-- El 'value' se pre-llena con la fecha de hoy por defecto. -->
        <input type="date" id="fecha_prestamo" name="fecha_prestamo" value="<?php echo date('Y-m-d'); ?>" required><br><br>

        <label for="fecha_devolucion_esperada">Fecha de Devolucion Esperada:</label><br>
        <input type="date" id="fecha_devolucion_esperada" name="fecha_devolucion_esperada" required><br><br>

        <!-- Boton para guardar el prestamo en la base de datos. -->
        <input type="submit" value="Registrar Prestamo">
    </form>
    
    <!-- Enlace para volver a la lista principal de prestamos. -->
    <p><a href="index.php?controller=prestamo&action=index">Volver a la lista de prestamos</a></p>

    <?php 
    // Incluimos el pie de pagina.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>