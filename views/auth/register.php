<?php
/**
 * Vista: Formulario de Registro.
 *
 * Esta pagina le permite a los nuevos visitantes crear una cuenta en el sistema.
 * Solicita la informacion basica necesaria: un nombre de usuario, un correo,
 * y una contrasena (que debe confirmarse).
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- Especifica que el idioma principal de la pagina es espanol -->
<head>
    <!-- La seccion 'head' contiene informacion tecnica sobre la pagina -->
    <meta charset="UTF-8"> <!-- Asegura que los caracteres como tildes se vean correctamente -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que la pagina se vea bien en celulares -->
    <title>Registrarse - Biblioteca</title> <!-- El titulo que aparece en la pestana del navegador -->
</head>
<body>
    <!-- El 'body' es donde va todo el contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun a todas las paginas.
    // Asi, si cambiamos algo en el header, se actualiza en todo el sitio.
    include 'views/shared/header.php'; 
    ?>

    <h2>Crear una Cuenta Nueva</h2>
    
    <!-- Este es el formulario de registro -->
    <!-- 'action' indica que los datos se enviaran al controlador 'auth' y a su funcion 'register'. -->
    <!-- 'method="POST"' envia los datos de forma segura. -->
    <form action="index.php?controller=auth&action=register" method="POST">
        
        <!-- Campo para el nombre de usuario -->
        <label for="nombre_usuario">Nombre de Usuario:</label><br>
        <!-- 'name="nombre_usuario"' es como PHP identificara este campo en $_POST -->
        <input type="text" id="nombre_usuario" name="nombre_usuario" required><br><br>

        <!-- Campo para el correo electronico -->
        <label for="correo_electronico">Correo Electronico:</label><br>
        <!-- 'type="email"' ayuda a los navegadores (especialmente en moviles) a validar que es un email -->
        <input type="email" id="correo_electronico" name="correo_electronico" required><br><br>

        <!-- Campo para la contrasena -->
        <label for="contrasena">Contrasena:</label><br>
        <!-- 'type="password"' oculta los caracteres que se escriben -->
        <input type="password" id="contrasena" name="contrasena" required><br><br>

        <!-- Campo para confirmar la contrasena -->
        <label for="confirmar_contrasena">Confirmar Contrasena:</label><br>
        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required><br><br>

        <!-- Boton que envia todos los datos del formulario -->
        <input type="submit" value="Registrarse">
    </form>
    
    <!-- Enlace para redirigir a los usuarios que ya tienen una cuenta -->
    <p>Ya tienes una cuenta? <a href="index.php?controller=auth&action=login">Inicia sesion aqui</a>.</p>

    <?php 
    // Incluimos el pie de pagina, que es comun para todo el sitio.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>