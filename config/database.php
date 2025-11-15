<?php
/**
 * Archivo de configuracion para la base de datos.
 *
 * En este archivo se definen todas las constantes necesarias
 * para establecer la conexion con el servidor de la base de datos.
 * Tambien incluye la funcion para realizar la conexion.
 */

// === PARAMETROS DE CONEXION A LA BASE DE DATOS ===
// Estos valores deben ser correctos para que la conexion funcione.

define('DB_HOST', 'localhost');    // Direccion del servidor de la base de datos (usualmente localhost).
define('DB_USER', 'root');         // Nombre de usuario para acceder a la base de datos.
define('DB_PASS', '');             // Contrasena del usuario. Dejar en blanco si no tiene.
define('DB_NAME', 'biblioteca_db'); // Nombre de la base de datos a la que se conectara.

/**
 * Funcion para crear y establecer la conexion con la base de datos.
 *
 * Utiliza las constantes definidas previamente para intentar conectarse.
 * Si la conexion falla, detiene la ejecucion del script para evitar mas errores.
 *
 * @return mysqli Devuelve el objeto de la conexion para poder usarlo en otras partes del codigo.
 */
function conectarDB() {
    // Se crea un nuevo objeto mysqli para intentar la conexion.
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Se comprueba si hubo un error durante el intento de conexion.
    if ($conn->connect_error) {
        // Si hay un error, se detiene todo y se muestra cual fue el problema.
        // Esto es una medida de seguridad para no continuar si la BD no esta disponible.
        die("Error de conexion: " . $conn->connect_error);
    }

    // Se asegura que la comunicacion con la base de datos use UTF-8.
    // Esto es para manejar correctamente tildes y caracteres especiales.
    $conn->set_charset("utf8");

    // Si la conexion fue exitosa, la funcion devuelve el objeto de conexion.
    return $conn;
}
?>