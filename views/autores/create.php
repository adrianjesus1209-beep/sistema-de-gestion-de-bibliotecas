<?php
/**
 * Vista: Formulario para Crear un Autor.
 *
 * Esta pagina contiene el formulario que los administradores usan para
 * anadir un nuevo autor a la base de datos de la biblioteca.
 * Es una seccion restringida del sistema.
 */
?>
<!DOCTYPE html> <!-- Define el tipo de documento, es un estandar de HTML5 -->
<html lang="es"> <!-- Ayuda a los navegadores a saber que la pagina esta en espanol -->
<head>
    <!-- La seccion 'head' contiene informacion tecnica y metadatos -->
    <meta charset="UTF-8"> <!-- Asegura la correcta visualizacion de tildes y caracteres especiales -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hace que la pagina se adapte a diferentes tamanos de pantalla -->
    <title>Crear Nuevo Autor - Biblioteca</title> <!-- El titulo que se muestra en la pestana del navegador -->
</head>
<body>
    <!-- El 'body' contiene todo el contenido que el usuario ve en la pagina -->

    <?php 
    // Incluimos el encabezado. Esto nos permite tener un diseno consistente
    // y no repetir el codigo del menu en cada archivo.
    include 'views/shared/header.php'; 
    ?>

    <h2>Anadir un Nuevo Autor</h2>

    <!-- Este es el formulario para crear el autor -->
    <!-- 'action' le dice al navegador a que URL enviar los datos del formulario. -->
    <!-- En este caso, al controlador 'autor' y su funcion 'create'. -->
    <!-- 'method="POST"' envia la informacion de forma segura. -->
    <form action="index.php?controller=autor&action=create" method="POST">
        
        <!-- Campo para el nombre del autor -->
        <label for="nombre">Nombre:</label><br>
        <!-- El atributo 'name' es crucial, es como PHP identificara este dato. -->
        <!-- 'required' hace que el navegador no permita enviar el formulario si este campo esta vacio. -->
        <input type="text" id="nombre" name="nombre" required><br><br>

        <!-- Campo para el apellido del autor -->
        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <!-- Campo para la nacionalidad -->
        <label for="nacionalidad">Nacionalidad (opcional):</label><br>
        <!-- Este campo no es obligatorio, por lo que no tiene el atributo 'required'. -->
        <input type="text" id="nacionalidad" name="nacionalidad"><br><br>

        <!-- Boton para enviar el formulario y guardar el autor -->
        <input type="submit" value="Crear Autor">
    </form>
    
    <!-- Un enlace para que el administrador pueda regresar facilmente a la lista de autores -->
    <p><a href="index.php?controller=autor&action=index">Volver a la lista de autores</a></p>

    <?php 
    // Incluimos el pie de pagina comun para todo el sitio web.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>