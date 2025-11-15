<?php
/**
 * index.php (Controlador Frontal).
 *
 * Este archivo es el UNICO punto de entrada a toda la aplicacion.
 * Su trabajo es como el de un recepcionista: recibe todas las peticiones
 * de los usuarios, prepara todo lo necesario (configuracion, base de datos,
 * modelos, controladores) y luego dirige la peticion al controlador y
 * a la funcion correcta para que la manejen.
 */

// === INICIALIZACION DE LA APLICACION ===

// PASO 1: Iniciar la sesion.
// Esto debe ser lo primero para poder usar variables de sesion ($_SESSION)
// en cualquier parte del codigo.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// PASO 2: Cargar la configuracion y conectar a la base de datos.
require_once 'config/database.php';
// La funcion conectarDB() nos devuelve el objeto de conexion.
$conn = conectarDB();

// PASO 3: Incluir todas las clases de los Modelos.
// Los modelos son los que hablan directamente con la base de datos.
// Los cargamos todos aqui para que esten disponibles para los controladores.
require_once 'models/Rol.php';
require_once 'models/Usuario.php';
require_once 'models/Autor.php';
require_once 'models/Libro.php';
require_once 'models/Prestamo.php';

// PASO 4: Incluir todas las clases de los Controladores.
// Los controladores contienen la logica principal de la aplicacion.
require_once 'controllers/AuthController.php';
require_once 'controllers/AutorController.php';
require_once 'controllers/LibroController.php';
require_once 'controllers/PrestamoController.php';


// === ENRUTAMIENTO (ROUTING) ===
// Esta seccion decide que codigo ejecutar basado en la URL.

// PASO 5: Obtener el controlador y la accion de la URL.
// Por ejemplo, en "index.php?controller=libro&action=create":
// $controller_name seria 'libro'
// $action_name seria 'create'
// El '??' es un operador que asigna un valor por defecto si no se encuentra en la URL.
// Si alguien visita solo "index.php", se usaran 'libro' e 'index' por defecto.
$controller_name = $_GET['controller'] ?? 'libro';
$action_name = $_GET['action'] ?? 'index';

// PASO 6: Construir el nombre de la clase del controlador.
// Seguimos una convencion: si el controlador es 'libro', la clase se llama 'LibroController'.
// 'ucfirst' convierte la primera letra en mayuscula.
$controller_class = ucfirst($controller_name) . 'Controller';


// === EJECUCION DE LA ACCION ===

// PASO 7: Verificar si el controlador existe y ejecutar la accion.
if (class_exists($controller_class)) {
    // Si la clase existe, creamos un nuevo objeto de ese controlador.
    // Le pasamos la conexion a la base de datos ($conn) a su constructor.
    $controller = new $controller_class($conn);

    // Ahora, verificamos si el metodo (la accion) existe dentro de ese controlador.
    if (method_exists($controller, $action_name)) {
        // Si existe, lo llamamos. Aqui es donde la magia ocurre.
        $controller->$action_name();
    } else {
        // Si la accion no existe, es un error.
        $_SESSION['mensaje_error'] = "La pagina '{$action_name}' no existe en la seccion '{$controller_name}'.";
        // Redirigimos al usuario a la pagina de inicio.
        header("Location: index.php");
        exit();
    }
} else {
    // Si la clase del controlador no existe, es un error mas grave.
    $_SESSION['mensaje_error'] = "La seccion '{$controller_name}' no existe.";
    header("Location: index.php");
    exit();
}

// === FINALIZACION ===

// PASO 8: Cerrar la conexion a la base de datos.
// Es una buena practica liberar los recursos cuando el script ha terminado su trabajo.
$conn->close();
?>