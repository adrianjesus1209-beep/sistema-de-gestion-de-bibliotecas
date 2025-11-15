<?php
/**
 * Clase AutorController.
 *
 * Este controlador se encarga de toda la logica para administrar los autores
 * de la biblioteca. Permite crear, ver, editar y eliminar autores.
 * Es una seccion restringida solo para administradores.
 */
class AutorController {
    /**
     * @var mysqli Guarda la conexion a la base de datos para poder hacer consultas.
     */
    private $conn;

    /**
     * @var Autor Es una instancia del modelo 'Autor'.
     * Se utiliza para acceder a todos los metodos relacionados con los autores
     * en la base de datos (leer, crear, actualizar, etc.).
     */
    private $autor_model;

    /**
     * Constructor de la clase AutorController.
     *
     * Al crear un objeto de este tipo, se inicializa la conexion a la BD,
     * se crea una instancia del modelo 'Autor' y, muy importante, se verifica
     * inmediatamente si el usuario es un administrador.
     *
     * @param mysqli $db Objeto de conexion a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->autor_model = new Autor($this->conn);
        // Esta linea es clave: asegura que solo los admins puedan usar este controlador.
        $this->verificarAdmin();
    }

    /**
     * Metodo de seguridad interno para verificar el rol del usuario.
     *
     * Comprueba si hay una sesion activa y si el rol del usuario es '1' (administrador).
     * Si no cumple con los requisitos, lo saca de esta seccion y lo redirige
     * a la pagina principal con un mensaje de acceso denegado.
     */
    private function verificarAdmin() {
        // Si no hay un usuario logueado o si su rol no es 1...
        if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
            // Prepara un mensaje de error para el usuario.
            $_SESSION['mensaje_error'] = "Acceso denegado. Necesitas ser administrador.";
            // Lo redirige a la pagina de inicio.
            header("Location: index.php");
            exit(); // Detiene la ejecucion para que no continue.
        }
    }

    /**
     * Muestra la pagina principal de la seccion de autores.
     *
     * Obtiene una lista de todos los autores desde el modelo y luego
     * carga la vista que se encarga de mostrarlos en una tabla.
     */
    public function index() {
        $autores = $this->autor_model->leerTodos();
        include 'views/autores/index.php';
    }

    /**
     * Gestiona la creacion de un nuevo autor.
     *
     * Si la peticion es GET, muestra el formulario para anadir un autor.
     * Si la peticion es POST, procesa los datos enviados desde ese formulario.
     */
    public function create() {
        // Comprueba si el usuario envio el formulario.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge los datos del formulario.
            $nombre = $_POST['nombre'] ?? '';
            $apellido = $_POST['apellido'] ?? '';
            $nacionalidad = $_POST['nacionalidad'] ?? '';

            // Validacion simple: el nombre y el apellido no pueden estar vacios.
            if (empty($nombre) || empty($apellido)) {
                $_SESSION['mensaje_error'] = "El nombre y el apellido son campos obligatorios.";
                header("Location: index.php?controller=autor&action=create");
                exit();
            }

            // Intenta crear el autor usando el modelo.
            if ($this->autor_model->crear($nombre, $apellido, $nacionalidad)) {
                $_SESSION['mensaje_exito'] = "El autor se ha creado correctamente.";
                header("Location: index.php?controller=autor&action=index");
                exit();
            } else {
                // Si falla, es probable que el autor ya exista.
                $_SESSION['mensaje_error'] = "Error al crear el autor. Puede que ya exista uno con el mismo nombre.";
                header("Location: index.php?controller=autor&action=create");
                exit();
            }
        } else {
            // Si no es POST, simplemente muestra la pagina con el formulario de creacion.
            include 'views/autores/create.php';
        }
    }

    /**
     * Gestiona la edicion de un autor existente.
     *
     * Si es GET, busca al autor por su ID y muestra el formulario con sus datos.
     * Si es POST, actualiza los datos del autor en la base de datos.
     */
    public function edit() {
        // Obtiene el ID del autor desde la URL (ej: ...&action=edit&id=5).
        $id = $_GET['id'] ?? null;
        if (!$id) {
            // Si no se proporciona un ID, no se puede editar nada.
            $_SESSION['mensaje_error'] = "No se especifico el ID del autor.";
            header("Location: index.php?controller=autor&action=index");
            exit();
        }

        // Si el usuario envio el formulario de edicion...
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge los datos del formulario.
            $id_autor = $_POST['id_autor'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellido = $_POST['apellido'] ?? '';
            $nacionalidad = $_POST['nacionalidad'] ?? '';

            // Validacion basica.
            if (empty($nombre) || empty($apellido)) {
                $_SESSION['mensaje_error'] = "El nombre y el apellido no pueden estar vacios.";
                header("Location: index.php?controller=autor&action=edit&id=" . $id_autor);
                exit();
            }

            // Intenta actualizar usando el modelo.
            if ($this->autor_model->actualizar($id_autor, $nombre, $apellido, $nacionalidad)) {
                $_SESSION['mensaje_exito'] = "Autor actualizado correctamente.";
                header("Location: index.php?controller=autor&action=index");
                exit();
            } else {
                $_SESSION['mensaje_error'] = "No se pudo actualizar el autor.";
                header("Location: index.php?controller=autor&action=edit&id=" . $id_autor);
                exit();
            }
        } else {
            // Si es una peticion GET, busca los datos actuales del autor.
            $autor = $this->autor_model->leerPorId($id);
            if (!$autor) {
                // Si el autor con ese ID no existe, notifica y redirige.
                $_SESSION['mensaje_error'] = "No se encontro el autor.";
                header("Location: index.php?controller=autor&action=index");
                exit();
            }
            // Muestra la vista de edicion, pasando los datos del autor.
            include 'views/autores/edit.php';
        }
    }

    /**
     * Procesa la eliminacion de un autor.
     *
     * Recibe el ID del autor que se quiere eliminar, llama al modelo
     * para que lo borre y redirige a la lista de autores.
     */
    public function delete() {
        // Obtiene el ID del autor desde la URL.
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['mensaje_error'] = "No se especifico el ID del autor a eliminar.";
            header("Location: index.php?controller=autor&action=index");
            exit();
        }

        // Intenta eliminar el autor a traves del modelo.
        if ($this->autor_model->eliminar($id)) {
            $_SESSION['mensaje_exito'] = "Autor eliminado correctamente.";
        } else {
            // Un error comun es intentar borrar un autor que ya tiene libros asociados.
            $_SESSION['mensaje_error'] = "Error al eliminar. Asegurate de que el autor no tenga libros asociados.";
        }
        // Siempre redirige de vuelta a la lista de autores.
        header("Location: index.php?controller=autor&action=index");
        exit();
    }
}
?>