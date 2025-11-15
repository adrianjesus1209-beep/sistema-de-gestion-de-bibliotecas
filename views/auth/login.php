<?php
/**
 * Vista: Formulario de Inicio de Sesion.
 *
 * Esta es la pagina que se le muestra al usuario para que pueda
 * ingresar al sistema. Contiene un formulario simple que pide
 * un nombre de usuario (o email) y una contrasena.
 */
?>
<!DOCTYPE html> <!-- Define que este es un documento HTML5 -->
<html lang="es"> <!-- El 'lang' ayuda a los navegadores y buscadores a entender el idioma de la pagina -->
<head>
    <!-- La seccion 'head' contiene metadatos sobre la pagina, no son visibles directamente -->
    <meta charset="UTF-8"> <!-- Especifica la codificacion de caracteres, UTF-8 es estandar para la web -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hace que la pagina se adapte a dispositivos moviles -->
    <title>Iniciar Sesion - Biblioteca</title> <!-- Este es el texto que aparece en la pestana del navegador -->
</head>
<body>
    <!-- El 'body' contiene todo el contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado. Esto nos permite reutilizar el mismo
    // header (con el logo, menu de navegacion, etc.) en todas las paginas.
    include 'views/shared/header.php'; 
    ?>

    <h2>Iniciar Sesion</h2>

    <!-- Este es el formulario de login -->
    <!-- 'action' le dice al navegador a donde enviar los datos cuando se presione el boton. -->
    <!-- En este caso, al controlador 'auth' y a su funcion 'login'. -->
    <!-- 'method="POST"' significa que los datos se enviaran de forma oculta y segura. -->
    <form action="index.php?controller=auth&action=login" method="POST">
        
        <!-- Campo para el nombre de usuario o correo -->
        <label for="credencial">Usuario o Correo Electronico:</label><br>
        <!-- 'name="credencial"' es el nombre con el que PHP recibira este dato en $_POST -->
        <input type="text" id="credencial" name="credencial" required><br><br>

        <!-- Campo para la contrasena -->
        <label for="contrasena">Contrasena:</label><br>
        <!-- 'type="password"' hace que el texto se oculte con puntos o asteriscos -->
        <input type="password" id="contrasena" name="contrasena" required><br><br>

        <!-- Boton para enviar el formulario -->
        <input type="submit" value="Iniciar Sesion">
    </form>
    
    <!-- Enlace para usuarios que aun no tienen una cuenta -->
    <p>No tienes una cuenta? <a href="index.php?controller=auth&action=register">Registrate aqui</a>.</p>

    <?php 
    // Incluimos el pie de pagina. Al igual que el header, esto nos permite
    // tener un footer consistente en todo el sitio web.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>