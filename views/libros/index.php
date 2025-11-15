<?php
/**
 * Vista: Listado de Libros.
 *
 * Esta es la pagina principal del catalogo de la biblioteca. Muestra una
 * tabla con todos los libros disponibles. Cualquier usuario puede ver esta
 * lista, pero solo los administradores tienen acceso a las opciones de
 * crear, editar o eliminar libros.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- Indica que el idioma principal es espanol -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para diseno adaptable -->
    <title>Gestion de Libros - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- Contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun a todas las paginas.
    include 'views/shared/header.php'; 
    ?>

    <h2>Gestion de Libros</h2>

    <!-- Formulario de busqueda/filtrado -->
    <!-- 'method="GET"' es adecuado para busquedas, porque los terminos de busqueda se anaden a la URL. -->
    <form action="index.php" method="GET">
        <!-- Estos campos ocultos aseguran que, al buscar, siempre se llame al controlador y accion correctos. -->
        <input type="hidden" name="controller" value="libro">
        <input type="hidden" name="action" value="index">
        
        <label for="filtro_titulo">Filtrar por Titulo:</label>
        <!-- El 'value' se rellena con el filtro actual, para que el usuario vea lo que busco. -->
        <input type="text" id="filtro_titulo" name="filtro_titulo" value="<?php echo htmlspecialchars($filtro_titulo ?? ''); ?>">
        <input type="submit" value="Buscar">
    </form>
    <br>

    <?php 
    // Verificamos si hay un usuario logueado y si su rol es de administrador (rol_id = 1).
    if (isset($_SESSION['user_id']) && $_SESSION['rol_id'] == 1): 
    ?>
        <!-- Si es administrador, le mostramos el enlace para anadir un nuevo libro. -->
        <p><a href="index.php?controller=libro&action=create">+ Nuevo Libro</a></p>
    <?php 
    endif; 
    ?>

    <?php 
    // Comprobamos si la variable $libros existe y si contiene al menos un libro.
    if (isset($libros) && $libros->num_rows > 0): 
    ?>
        <!-- Si hay libros, creamos la tabla para mostrarlos. -->
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulo</th>
                    <th>ISBN</th>
                    <th>Anio Publicacion</th>
                    <th>Descripcion</th>
                    <th>Autor(es)</th>
                    <th>Disponible</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Recorremos cada libro que nos devolvio la consulta a la base de datos.
                while ($libro = $libros->fetch_assoc()): 
                ?>
                    <tr>
                        <!-- Imprimimos cada dato del libro en su celda correspondiente. -->
                        <td><?php echo htmlspecialchars($libro['id_libro']); ?></td>
                        <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($libro['isbn'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($libro['anio_publicacion'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            // Para la descripcion, la acortamos a 50 caracteres para que no ocupe mucho espacio.
                            // Si es mas larga, anadimos '...' al final.
                            echo htmlspecialchars(substr($libro['descripcion'] ?? '', 0, 50)) . (strlen($libro['descripcion'] ?? '') > 50 ? '...' : ''); 
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($libro['autores'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            // Usamos un operador ternario: si 'disponible' es true (o 1), escribimos 'Si', si no, 'No'.
                            echo $libro['disponible'] ? 'Si' : 'No'; 
                            ?>
                        </td>
                        <td>
                            <!-- Acciones disponibles para todos -->
                            <a href="index.php?controller=libro&action=show&id=<?php echo htmlspecialchars($libro['id_libro']); ?>">Ver</a>
                            
                            <?php 
                            // Verificamos de nuevo si es administrador para mostrar las acciones de edicion y borrado.
                            if (isset($_SESSION['user_id']) && $_SESSION['rol_id'] == 1): 
                            ?>
                                | <a href="index.php?controller=libro&action=edit&id=<?php echo htmlspecialchars($libro['id_libro']); ?>">Editar</a>
                                <!-- El 'onclick' pide confirmacion antes de proceder a borrar. -->
                                | <a href="index.php?controller=libro&action=delete&id=<?php echo htmlspecialchars($libro['id_libro']); ?>" onclick="return confirm('Estas seguro de que quieres eliminar este libro? Si esta prestado, la eliminacion fallara.');">Eliminar</a>
                            <?php 
                            endif; 
                            ?>
                        </td>
                    </tr>
                <?php 
                endwhile; 
                ?>
            </tbody>
        </table>
    <?php 
    // Si no se encontraron libros...
    else: 
    ?>
        <!-- ...mostramos un mensaje. El mensaje cambia si se estaba aplicando un filtro de busqueda. -->
        <p>No hay libros registrados<?php echo (isset($filtro_titulo) && $filtro_titulo ? " que coincidan con el filtro '<strong>" . htmlspecialchars($filtro_titulo) . "</strong>'." : "."); ?></p>
    <?php 
    endif; 
    ?>

    <?php 
    // Incluimos el pie de pagina.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>