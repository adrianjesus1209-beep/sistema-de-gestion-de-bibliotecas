<?php
/**
 * Vista: Formulario para Editar un Autor.
 *
 * Esta pagina le presenta al administrador un formulario con los datos
 * actuales de un autor para que pueda modificarlos. Es la contraparte
- * de la vista de creacion.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- Le dice al navegador que el contenido principal es en espanol -->
<head>
    <!-- La seccion 'head' contiene metadatos y enlaces a recursos (como CSS o JS) -->
    <meta charset="UTF-8"> <!-- Asegura que todos los caracteres se muestren correctamente -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que la pagina se adapte a moviles y tabletas -->
    <title>Editar Autor - Biblioteca</title> <!-- El texto en la pestana del navegador -->
</head>
<body>
    <!-- El 'body' contiene todo lo que se ve en la pagina -->

    <?php 
    // Incluimos el encabezado reutilizable para mantener la consistencia visual.
    include 'views/shared/header.php'; 
    ?>

    <h2>Editar Autor</h2>

    <?php 
    // Antes de intentar mostrar el formulario, verificamos si la variable $autor existe.
    // El controlador es quien debe pasar esta variable a la vista. Si no existe,
    // significa que el autor con el ID solicitado no fue encontrado.
    if (isset($autor)): 
    ?>
    
    <!-- El formulario para editar los datos del autor. -->
    <!-- 'action' apunta a la misma funcion ('edit') del controlador 'autor' que nos trajo aqui. -->
    <form action="index.php?controller=autor&action=edit" method="POST">
        
        <!-- Este campo es crucial pero esta oculto. -->
        <!-- Guarda el ID del autor que estamos editando. Cuando enviemos el formulario, -->
        <!-- el controlador usara este ID para saber a que autor aplicarle los cambios en la BD. -->
        <input type="hidden" name="id_autor" value="<?php echo htmlspecialchars($autor['id_autor']); ?>">

        <!-- Campo para el nombre del autor -->
        <label for="nombre">Nombre:</label><br>
        <!-- Usamos PHP para poner el nombre actual del autor en el atributo 'value'. -->
        <!-- 'htmlspecialchars' es una medida de seguridad para prevenir ataques XSS. -->
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($autor['nombre']); ?>" required><br><br>

        <!-- Campo para el apellido del autor -->
        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($autor['apellido']); ?>" required><br><br>

        <!-- Campo para la nacionalidad del autor -->
        <label for="nacionalidad">Nacionalidad (opcional):</label><br>
        <!-- El '?? '' ' es un atajo para decir: si la nacionalidad existe, usala; si no, pon una cadena vacia. -->
        <input type="text" id="nacionalidad" name="nacionalidad" value="<?php echo htmlspecialchars($autor['nacionalidad'] ?? ''); ?>"><br><br>

        <!-- Boton para enviar los cambios -->
        <input type="submit" value="Actualizar Autor">
    </form>
    
    <?php 
    // Si la variable $autor no fue encontrada, se ejecuta este bloque.
    else: 
    ?>
        <p>No se ha encontrado un autor con el ID especificado.</p>
    <?php 
    // Fin del bloque if-else.
    endif; 
    ?>

    <!-- Enlace para volver a la pagina principal de la seccion de autores -->
    <p><a href="index.php?controller=autor&action=index">Volver a la lista de autores</a></p>

    <?php 
    // Incluimos el pie de pagina comun del sitio.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>