<?php
/**
 * Vista Parcial: Encabezado (Header).
 *
 * Este archivo es un fragmento de codigo que se incluye al principio de
 * todas las paginas del sitio. Se encarga de iniciar la sesion, mostrar
 * el titulo principal y la barra de navegacion. La navegacion es dinamica,
 * lo que significa que cambia segun si el usuario ha iniciado sesion y su rol.
 * Tambien muestra mensajes de exito o error.
 */

// Se asegura de que la sesion este iniciada.
// Es importante para poder acceder a variables como $_SESSION['user_id'].
// Si la sesion no esta activa, la inicia.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Una linea horizontal para separar visualmente el contenido. -->
<hr>

<!-- Titulo principal del sitio, visible en todas las paginas. -->
<h1>Sistema de Gestion Biblioteca</h1>

<!-- La etiqueta 'nav' contiene el menu de navegacion principal. -->
<nav>
    <?php 
    // Comprobamos si el usuario NO ha iniciado sesion (si no existe 'user_id' en la sesion).
    if (!isset($_SESSION['user_id'])): 
    ?>
        <!-- Si no ha iniciado sesion, le mostramos los enlaces para entrar o registrarse. -->
        [ <a href="index.php?controller=auth&action=login">Iniciar Sesion</a> ]
        [ <a href="index.php?controller=auth&action=register">Registrarse</a> ]
    <?php 
    // Si la condicion de arriba no se cumple, significa que el usuario SI ha iniciado sesion.
    else: 
    ?>
        <!-- Enlaces para usuarios que ya han iniciado sesion. -->
        [ <a href="index.php?controller=libro&action=index">Libros</a> ]
        
        <?php 
        // Ahora, comprobamos el rol del usuario para mostrar enlaces especificos.
        if ($_SESSION['rol_id'] == 1): // El rol 1 es para Administradores.
        ?>
            <!-- Enlaces que solo los administradores pueden ver. -->
            [ <a href="index.php?controller=autor&action=index">Autores</a> ]
            [ <a href="index.php?controller=prestamo&action=index">Prestamos</a> ]
        <?php 
        else: // Si no es rol 1, es un usuario regular.
        ?>
            <!-- Enlace para que los usuarios vean solo sus prestamos. -->
            [ <a href="index.php?controller=prestamo&action=index">Mis Prestamos</a> ]
        <?php 
        endif; // Fin de la comprobacion del rol.
        ?>
        
        <!-- El enlace para cerrar la sesion es visible para todos los que han entrado. -->
        [ <a href="index.php?controller=auth&action=logout">Cerrar Sesion</a> ]
        
        <!-- Un mensaje de bienvenida personalizado. -->
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?> (Rol: <?php echo ($_SESSION['rol_id'] == 1 ? 'Administrador' : 'Usuario'); ?>)</p>
    <?php 
    endif; // Fin de la comprobacion de si ha iniciado sesion.
    ?>
</nav>
<hr>

<?php
// === GESTION DE MENSAJES FLOTANTES (FLASH MESSAGES) ===
// Estos mensajes se muestran una sola vez y luego se borran.

// Comprueba si existe un mensaje de error guardado en la sesion.
if (isset($_SESSION['mensaje_error'])) {
    // Si existe, lo muestra dentro de un parrafo de color rojo.
    echo "<p style='color:red;'>" . htmlspecialchars($_SESSION['mensaje_error']) . "</p>";
    // Inmediatamente despues de mostrarlo, lo elimina de la sesion para que no vuelva a aparecer.
    unset($_SESSION['mensaje_error']);
}

// Hace lo mismo para los mensajes de exito.
if (isset($_SESSION['mensaje_exito'])) {
    // Si existe, lo muestra en color verde.
    echo "<p style='color:green;'>" . htmlspecialchars($_SESSION['mensaje_exito']) . "</p>";
    // Y lo elimina.
    unset($_SESSION['mensaje_exito']);
}
?>