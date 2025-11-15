<?php
/**
 * Clase PrestamoController.
 *
 * Este controlador se encarga de toda la logica relacionada con los prestamos
 * de libros. Gestiona el registro de nuevos prestamos, su devolucion y la
 * visualizacion de los mismos. Tiene reglas claras para diferenciar lo que
 * puede hacer un administrador y un usuario comun.
 */
class PrestamoController {
    /**
     * @var mysqli Guarda la conexion a la base de datos para todas las operaciones.
     */
    private $conn;

    /**
     * @var Prestamo Instancia del modelo Prestamo, para interactuar con la tabla de prestamos.
     */
    private $prestamo_model;

    /**
     * @var Libro Instancia del modelo Libro, necesaria para verificar la disponibilidad de los libros.
     */
    private $libro_model;

    /**
     * @var Usuario Instancia del modelo Usuario, para obtener la lista de usuarios al crear un prestamo.
     */
    private $usuario_model;

    /**
     * Constructor de la clase PrestamoController.
     *
     * Al crear un objeto de este controlador, se inicializan la conexion a la BD
     * y todos los modelos que se van a necesitar. Ademas, ejecuta inmediatamente
     * una verificacion para asegurarse de que el usuario haya iniciado sesion.
     *
     * @param mysqli $db El objeto de conexion a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->prestamo_model = new Prestamo($this->conn);
        $this->libro_model = new Libro($this->conn);
        $this->usuario_model = new Usuario($this->conn);
        // Medida de seguridad: nadie puede acceder a esta seccion sin iniciar sesion.
        $this->verificarLogin();
    }

    /**
     * Verifica que haya una sesion de usuario activa.
     *
     * Es un filtro de seguridad basico. Si una persona intenta acceder a cualquier
     * pagina de prestamos sin estar logueada, se le redirige a la pagina de login.
     */
    private function verificarLogin() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error'] = "Debes iniciar sesion para ver tus prestamos.";
            header("Location: index.php?controller=auth&action=login");
            exit();
        }
    }

    /**
     * Verifica que el usuario tenga rol de administrador.
     *
     * Es un filtro de seguridad mas especifico para acciones delicadas.
     * Si el usuario no tiene el rol '1' (admin), se le deniega el acceso
     * y se le redirige a la pagina principal.
     */
    private function verificarAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
            $_SESSION['mensaje_error'] = "Accion no permitida. Se requieren permisos de administrador.";
            header("Location: index.php");
            exit();
        }
    }

    /**
     * Muestra la lista de prestamos.
     *
     * Antes de mostrar la lista, actualiza el estado de los prestamos que ya
     * deberian haber sido devueltos. Si el usuario es administrador, ve todos
     * los prestamos. Si es un usuario normal, solo ve los suyos.
     */
    public function index() {
        // Esta funcion del modelo pone los prestamos como 'atrasado' si ya paso su fecha.
        $this->prestamo_model->actualizarEstadosAtrasados();

        $prestamos = null;
        if ($_SESSION['rol_id'] == 1) { // Si es admin...
            $prestamos = $this->prestamo_model->leerTodos(); // Ve todos los prestamos.
        } else { // Si es usuario regular...
            $prestamos = $this->prestamo_model->leerTodos($_SESSION['user_id']); // Ve solo los suyos.
        }
        include 'views/prestamos/index.php';
    }

    /**
     * Gestiona la creacion de un nuevo prestamo.
     *
     * Esta accion esta restringida solo para administradores.
     */
    public function create() {
        // Primer paso: confirmar que es un administrador.
        $this->verificarAdmin();

        // Si el formulario de crear prestamo fue enviado...
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge los datos del formulario.
            $id_libro = $_POST['id_libro'] ?? '';
            $id_usuario = $_POST['id_usuario'] ?? '';
            $fecha_prestamo = $_POST['fecha_prestamo'] ?? date('Y-m-d'); // Si no se especifica, es hoy.
            $fecha_devolucion_esperada = $_POST['fecha_devolucion_esperada'] ?? '';

            // Validacion para asegurar que los datos necesarios estan presentes.
            if (empty($id_libro) || empty($id_usuario) || empty($fecha_devolucion_esperada)) {
                $_SESSION['mensaje_error'] = "Se necesita un libro, un usuario y una fecha de devolucion.";
                header("Location: index.php?controller=prestamo&action=create");
                exit();
            }

            // Doble chequeo: se asegura de que el libro realmente este disponible.
            $libro = $this->libro_model->leerPorId($id_libro);
            if (!$libro || !$libro['disponible']) {
                $_SESSION['mensaje_error'] = "El libro que intentas prestar no esta disponible.";
                header("Location: index.php?controller=prestamo&action=create");
                exit();
            }

            // Si todo esta en orden, intenta crear el prestamo.
            if ($this->prestamo_model->crear($id_libro, $id_usuario, $fecha_prestamo, $fecha_devolucion_esperada)) {
                $_SESSION['mensaje_exito'] = "Prestamo registrado correctamente.";
                header("Location: index.php?controller=prestamo&action=index");
                exit();
            } else {
                $_SESSION['mensaje_error'] = "Ocurrio un error al registrar el prestamo.";
                header("Location: index.php?controller=prestamo&action=create");
                exit();
            }
        } else {
            // Si no es POST, se muestran los datos necesarios para el formulario.
            $libros_disponibles = $this->libro_model->obtenerLibrosDisponibles();
            $usuarios = $this->usuario_model->leerTodos();
            include 'views/prestamos/create.php';
        }
    }

    /**
     * Registra la devolucion de un libro prestado.
     *
     * Solo los administradores pueden realizar esta accion.
     */
    public function devolver() {
        // Confirma que es un administrador.
        $this->verificarAdmin();

        // Obtiene el ID del prestamo a devolver desde la URL.
        $id_prestamo = $_GET['id'] ?? null;
        if (!$id_prestamo) {
            $_SESSION['mensaje_error'] = "No se especifico que prestamo se va a devolver.";
            header("Location: index.php?controller=prestamo&action=index");
            exit();
        }

        // Verifica que el prestamo exista y que no haya sido devuelto ya.
        $prestamo = $this->prestamo_model->leerPorId($id_prestamo);
        if (!$prestamo || $prestamo['estado'] == 'devuelto') {
            $_SESSION['mensaje_error'] = "Este prestamo no existe o ya fue marcado como devuelto.";
            header("Location: index.php?controller=prestamo&action=index");
            exit();
        }

        // Intenta registrar la devolucion a traves del modelo.
        if ($this->prestamo_model->registrarDevolucion($id_prestamo)) {
            $_SESSION['mensaje_exito'] = "La devolucion se registro con exito.";
        } else {
            $_SESSION['mensaje_error'] = "Hubo un error al intentar registrar la devolucion.";
        }
        header("Location: index.php?controller=prestamo&action=index");
        exit();
    }

    /**
     * Muestra los detalles de un prestamo especifico.
     *
     * Los administradores pueden ver cualquier prestamo. Los usuarios
     * normales solo pueden ver los prestamos que les pertenecen.
     */
    public function show() {
        // Obtiene el ID del prestamo de la URL.
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['mensaje_error'] = "No se especifico un prestamo para ver.";
            header("Location: index.php?controller=prestamo&action=index");
            exit();
        }

        // Busca los datos del prestamo.
        $prestamo = $this->prestamo_model->leerPorId($id);

        if (!$prestamo) {
            $_SESSION['mensaje_error'] = "El prestamo que buscas no fue encontrado.";
            header("Location: index.php?controller=prestamo&action=index");
            exit();
        }

        // Verificacion de seguridad: si no es admin, ¿este prestamo es tuyo?
        if ($_SESSION['rol_id'] != 1 && $prestamo['id_usuario'] != $_SESSION['user_id']) {
            $_SESSION['mensaje_error'] = "No tienes permiso para ver los detalles de este prestamo.";
            header("Location: index.php?controller=prestamo&action=index");
            exit();
        }
        
        // Si todo esta bien, muestra la vista con los detalles.
        include 'views/prestamos/show.php';
    }
}
?>