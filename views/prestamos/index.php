<?php
/**
 * Vista: Listado de Prestamos.
 *
 * Esta pagina muestra una tabla con el historial de prestamos.
 * Si el usuario es un administrador, vera todos los prestamos del sistema.
 * Si es un usuario regular, solo vera sus propios prestamos.
 */
?>
<!DOCTYPE html> <!-- Declaracion estandar de un documento HTML5 -->
<html lang="es"> <!-- El idioma principal del contenido es espanol -->
<head>
    <!-- Metadatos de la pagina -->
    <meta charset="UTF-8"> <!-- Codificacion de caracteres para acentos y simbolos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para que se vea bien en moviles -->
    <title>Gestion de Prestamos - Biblioteca</title> <!-- Titulo en la pestana del navegador -->
</head>
<body>
    <!-- Contenido visible de la pagina -->

    <?php 
    // Incluimos el encabezado comun a todo el sitio.
    include 'views/shared/header.php'; 
    ?>

    <h2>Gestion de Prestamos</h2>

    <?php 
    // Verificamos si el usuario ha iniciado sesion y si es un administrador (rol_id = 1).
    if (isset($_SESSION['user_id']) && $_SESSION['rol_id'] == 1): 
    ?>
        <!-- Si es administrador, le mostramos el enlace para registrar un nuevo prestamo. -->
        <p><a href="index.php?controller=prestamo&action=create">+ Nuevo Prestamo</a></p>
    <?php 
    endif; 
    ?>

    <?php 
    // Verificamos si la variable $prestamos existe y si tiene al menos un registro.
    // Esto evita mostrar una tabla vacia o generar un error si no hay prestamos.
    if (isset($prestamos) && $prestamos->num_rows > 0): 
    ?>
        <!-- Si hay prestamos, creamos una tabla para mostrarlos. -->
        <table border="1">
            <thead> <!-- La cabecera de la tabla -->
                <tr>
                    <th>ID Prestamo</th>
                    <th>Libro</th>
                    <th>Usuario</th>
                    <th>Fecha Prestamo</th>
                    <th>Devolucion Esperada</th>
                    <th>Devolucion Real</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody> <!-- El cuerpo de la tabla con los datos -->
                <?php 
                // Iniciamos un bucle para recorrer cada uno de los prestamos.
                while ($prestamo = $prestamos->fetch_assoc()): 
                ?>
                    <!-- Cada prestamo es una fila en la tabla. -->
                    <tr>
                        <!-- Imprimimos cada dato en su celda correspondiente. -->
                        <td><?php echo htmlspecialchars($prestamo['id_prestamo']); ?></td>
                        <td><?php echo htmlspecialchars($prestamo['titulo_libro']); ?></td>
                        <td><?php echo htmlspecialchars($prestamo['nombre_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($prestamo['fecha_prestamo']); ?></td>
                        <td><?php echo htmlspecialchars($prestamo['fecha_devolucion_esperada']); ?></td>
                        <!-- Si la fecha de devolucion real es nula, mostramos 'Pendiente'. -->
                        <td><?php echo htmlspecialchars($prestamo['fecha_devolucion_real'] ?? 'Pendiente'); ?></td>
                        <td><?php echo htmlspecialchars($prestamo['estado']); ?></td>
                        <td>
                            <!-- La accion de 'Ver Detalle' esta disponible para todos. -->
                            <a href="index.php?controller=prestamo&action=show&id=<?php echo htmlspecialchars($prestamo['id_prestamo']); ?>">Ver Detalle</a>
                            
                            <?php 
                            // Verificamos si el usuario es admin y si el libro aun no ha sido devuelto.
                            // Esto es para mostrar la opcion de marcar como devuelto.
                            if (isset($_SESSION['user_id']) && $_SESSION['rol_id'] == 1 && ($prestamo['estado'] == 'prestado' || $prestamo['estado'] == 'atrasado')):
                            ?>
                                <!-- Este enlace solo lo ven los administradores y solo para libros pendientes. -->
                                <!-- El 'onclick' pide confirmacion antes de realizar la accion. -->
                                | <a href="index.php?controller=prestamo&action=devolver&id=<?php echo htmlspecialchars($prestamo['id_prestamo']); ?>" onclick="return confirm('Confirmar devolucion de este libro?');">Marcar como Devuelto</a>
                            <?php 
                            endif; 
                            ?>
                        </td>
                    </tr>
                <?php 
                endwhile; // Fin del bucle 
                ?>
            </tbody>
        </table>
    <?php 
    // Si la condicion inicial no se cumplio (no hay prestamos)...
    else: 
    ?>
        <!-- ...mostramos un mensaje indicandolo. -->
        <p>No tienes prestamos registrados en este momento.</p>
    <?php 
    endif; // Fin del if-else 
    ?>

    <?php 
    // Incluimos el pie de pagina.
    include 'views/shared/footer.php'; 
    ?>
</body>
</html>