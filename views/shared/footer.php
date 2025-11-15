<?php
/**
 * Vista Parcial: Pie de Pagina (Footer).
 *
 * Este es un fragmento de codigo reutilizable que se incluye al final
 * de todas las paginas del sitio. Contiene el pie de pagina con
 * la informacion de copyright y cierra las etiquetas HTML principales.
 */
?>
<!-- Una linea horizontal para separar visualmente el contenido principal del pie de pagina. -->
<hr>

<!-- La etiqueta 'footer' es el contenedor estandar para el pie de pagina de un documento. -->
<footer>
    <!-- Un parrafo con el texto de derechos de autor. -->
    <!-- '&copy;' es el codigo HTML para el simbolo de copyright (©). -->
    <p>&copy; 
        <?php 
        // Este codigo PHP se ejecuta en el servidor para obtener el anio actual.
        // Asi, el anio en el copyright se actualiza solo cada 1 de enero.
        echo date("Y"); 
        ?> 
        Sistema de Gestion de Biblioteca. Todos los derechos reservados.
    </p>
</footer>

<!-- Estas son las etiquetas de cierre para el 'body' y el 'html'. -->
<!-- Se abrieron en el archivo 'header.php' y se cierran aqui, completando la estructura de la pagina. -->
</body>
</html>