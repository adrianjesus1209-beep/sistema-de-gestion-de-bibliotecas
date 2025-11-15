<?php
/**
 * Vista: Listado de Autores.
 *
 * Esta es la pagina principal para la gestion de autores. Muestra una
 * tabla con todos los autores registrados en el sistema y proporciona
 * las opciones para editarlos o eliminarlos. Es una vista para administradores.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar para un documento HTML5 -->
<html lang="es"> <!-- Indica que el idioma de la pagina es espanol -->
<head>
    <!-- La seccion 'head' contiene metadatos sobre la pagina -->
    <meta charset="UTF-8"> <!-- Asegura que tildes y otros caracteres se muestren bien -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que la pagina se adapte a moviles -->
    <title>Gestion de Autores - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- El 'body' contiene todo el contenido visible -->

    <?php 
    // Incluimos el encabezado comun para mantener un diseno consistente.
    include 'views/shared/header.php'; 
    ?>

    <h2>Gestion de Autores</h2>
    <!-- Un enlace visible y claro para que el administrador pueda anadir un nuevo autor -->
    <p><a href="index.php?controller=autor&action=create">+ Nuevo Autor</a></p>

    <?php 
    // Verificacion importante: antes de intentar mostrar la tabla, nos aseguramos
    // de que la variable $autores exista y de que contenga al menos un resultado.
    // Esto evita errores si la base de datos esta vacia.
    if ($autores && $autores->num_rows > 0): 
    ?>
        <!-- Si hay autores, creamos una tabla para mostrarlos de forma ordenada -->
        <table border="1"> <!-- 'border="1"' es un estilo simple para que se vean los bordes de la tabla -->
            <thead> <!-- La cabecera de la tabla -->
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Nacionalidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody> <!-- El cuerpo de la tabla, donde iran los datos -->
                <?php 
                // Iniciamos un bucle 'while'. Este se repetira una vez por cada autor
                // que haya en la variable $autores.
                while ($autor = $autores->fetch_assoc()): 
                ?>
                    <!-- Por cada autor, creamos una nueva fila en la tabla -->
                    <tr>
                        <!-- Y en cada celda ('td'), imprimimos uno de sus datos -->
                        <!-- Usamos 'htmlspecialchars' por seguridad, para evitar que codigo malicioso se ejecute en la pagina -->
                        <td><?php echo htmlspecialchars($autor['id_autor']); ?></td>
                        <td><?php echo htmlspecialchars($autor['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($autor['apellido']); ?></td>
                        <!-- Para la nacionalidad, si no existe, mostramos 'N/A' para que no quede vacio -->
                        <td><?php echo htmlspecialchars($autor['nacionalidad'] ?? 'N/A'); ?></td>
                        <td>
                            <!-- En la ultima celda ponemos los enlaces de accion para este autor en especifico -->
                            <!-- El enlace para editar, que lleva el ID del autor en la URL -->
                            <a href="index.php?controller=autor&action=edit&id=<?php echo htmlspecialchars($autor['id_autor']); ?>">Editar</a> |
                            <!-- El enlace para eliminar, que tambien lleva el ID -->
                            <!-- El 'onclick' ejecuta un pequeno script de JavaScript que pide confirmacion al usuario -->
                            <!-- Si el usuario presiona "Cancelar", 'return false' detiene el enlace y no se borra nada -->
                            <a href="index.php?controller=autor&action=delete&id=<?php echo htmlspecialchars($autor['id_autor']); ?>" onclick="return confirm('Estas seguro de que quieres eliminar este autor? Si tiene libros asociados, la eliminacion fallara.');">Eliminar</a>
                        </td>
                    </tr>
                <?php 
                // Fin del bucle while
                endwhile; 
                ?>
            </tbody>
        </table>
    <?php 
    // Si la condicion del 'if' de arriba no se cumplio (no habia autores)...
    else: 
    ?>
        <!-- ...mostramos un mensaje amigable en su lugar -->
        <p>No hay autores registrados en este momento.</p>
    <?php 
    // Fin del bloque if-else
    endif; 
    ?>

    <?php 
    // Incluimos el pie de pagina
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>