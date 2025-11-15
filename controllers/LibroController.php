<?php
/**
 * Clase LibroController.
 *
 * Se encarga de toda la logica relacionada con los libros: mostrarlos,
 * crearlos, editarlos y eliminarlos. La mayoria de las acciones
 * importantes aqui solo estan permitidas para los administradores.
 */
class LibroController {
    /**
     * @var mysqli Almacena la conexion a la base de datos.
     */
    private $conn;

    /**
     * @var Libro Instancia del modelo Libro. Se usa para todas las
     * operaciones de la base de datos que tienen que ver con libros.
     */
    private $libro_model;

    /**
     * @var Autor Instancia del modelo Autor. Se necesita para
     * obtener la lista de autores al crear o editar un libro.
     */
    private $autor_model;

    /**
     * Constructor de la clase LibroController.
     *
     * Cuando se crea un objeto de este controlador, este metodo se ejecuta
     * para preparar la conexion a la base de datos y los modelos necesarios.
     *
     * @param mysqli $db El objeto de conexion a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->libro_model = new Libro($this->conn);
        $this->autor_model = new Autor($this->conn);
    }

    /**
     * Funcion de seguridad para verificar si el usuario es administrador.
     *
     * Este metodo privado comprueba que el usuario tenga una sesion iniciada
     * y que su rol sea el de administrador (ID de rol 1). Si no lo es,
     * lo expulsa de la pagina y le muestra un error.
     */
    private function verificarAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
            $_SESSION['mensaje_error'] = "Acceso denegado. Esta accion requiere permisos de administrador.";
            header("Location: index.php");
            exit();
        }
    }

    /**
     * Muestra la lista de todos los libros.
     *
     * Cualquiera puede ver la lista de libros. Tambien permite
     * que se pueda buscar un libro por su titulo.
     */
    public function index() {
        // Revisa si hay un filtro de busqueda en la URL.
        $filtro_titulo = $_GET['filtro_titulo'] ?? '';
        // Pide al modelo la lista de libros, aplicando el filtro si existe.
        $libros = $this->libro_model->leerTodos($filtro_titulo);
        // Carga la vista que muestra la lista de libros.
        include 'views/libros/index.php';
    }

    /**
     * Gestiona la creacion de un nuevo libro.
     *
     * Esta funcion esta restringida solo para administradores.
     * Muestra el formulario para anadir un libro o procesa los datos si el formulario ya fue enviado.
     */
    public function create() {
        // Primero, lo primero: verificar que el usuario sea admin.
        $this->verificarAdmin();

        // Si el formulario fue enviado (metodo POST)...
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge todos los datos del formulario.
            $titulo = $_POST['titulo'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $anio_publicacion = $_POST['anio_publicacion'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $autores_ids = $_POST['autores_ids'] ?? []; // Puede ser un array de autores.

            // Validacion basica para los campos mas importantes.
            if (empty($titulo) || empty($isbn) || empty($anio_publicacion) || empty($autores_ids)) {
                $_SESSION['mensaje_error'] = "Debes completar el titulo, ISBN, anio y seleccionar al menos un autor.";
                header("Location: index.php?controller=libro&action=create");
                exit();
            }

            // Intenta crear el libro a traves del modelo.
            if ($this->libro_model->crear($titulo, $isbn, $anio_publicacion, $descripcion, $autores_ids)) {
                $_SESSION['mensaje_exito'] = "El libro se ha anadido correctamente.";
                header("Location: index.php?controller=libro&action=index");
                exit();
            } else {
                // Si falla, es probable que el ISBN ya este en uso.
                $_SESSION['mensaje_error'] = "Error al anadir el libro. Revisa si el ISBN ya existe.";
                header("Location: index.php?controller=libro&action=create");
                exit();
            }
        } else {
            // Si no es POST, significa que el usuario acaba de entrar a la pagina.
            // Se necesita la lista de autores para mostrarla en el formulario.
            $autores = $this->autor_model->leerTodos();
            // Carga la vista con el formulario de creacion.
            include 'views/libros/create.php';
        }
    }

    /**
     * Gestiona la edicion de un libro existente.
     *
     * Solo los administradores pueden editar la informacion de un libro.
     */
    public function edit() {
        // Verifica que el usuario sea administrador.
        $this->verificarAdmin();

        // Obtiene el ID del libro que se quiere editar desde la URL.
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['mensaje_error'] = "No se ha especificado que libro editar.";
            header("Location: index.php?controller=libro&action=index");
            exit();
        }

        // Si el usuario envio el formulario de edicion...
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge los datos del formulario.
            $id_libro = $_POST['id_libro'] ?? '';
            $titulo = $_POST['titulo'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $anio_publicacion = $_POST['anio_publicacion'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $autores_ids = $_POST['autores_ids'] ?? [];

            // Validacion de campos obligatorios.
            if (empty($titulo) || empty($isbn) || empty($anio_publicacion) || empty($autores_ids)) {
                $_SESSION['mensaje_error'] = "El titulo, ISBN, anio y autor son obligatorios.";
                header("Location: index.php?controller=libro&action=edit&id=" . $id_libro);
                exit();
            }

            // Intenta actualizar el libro usando el modelo.
            if ($this->libro_model->actualizar($id_libro, $titulo, $isbn, $anio_publicacion, $descripcion, $autores_ids)) {
                $_SESSION['mensaje_exito'] = "Libro actualizado con exito.";
                header("Location: index.php?controller=libro&action=index");
                exit();
            } else {
                $_SESSION['mensaje_error'] = "Error al actualizar el libro. Revisa los datos.";
                header("Location: index.php?controller=libro&action=edit&id=" . $id_libro);
                exit();
            }
        } else {
            // Si es peticion GET, se busca la informacion actual del libro.
            $libro = $this->libro_model->leerPorId($id);
            if (!$libro) {
                $_SESSION['mensaje_error'] = "No se encontro el libro que intentas editar.";
                header("Location: index.php?controller=libro&action=index");
                exit();
            }
            // Tambien se necesita la lista de todos los autores para el formulario.
            $autores_disponibles = $this->autor_model->leerTodos();
            // Carga la vista de edicion con los datos del libro.
            include 'views/libros/edit.php';
        }
    }

    /**
     * Elimina un libro del sistema.
     *
     * Accion restringida a administradores.
     */
    public function delete() {
        // Verifica que el usuario sea admin.
        $this->verificarAdmin();

        // Obtiene el ID del libro a eliminar.
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['mensaje_error'] = "No se especifico el ID del libro.";
            header("Location: index.php?controller=libro&action=index");
            exit();
        }

        // Intenta eliminar el libro a traves del modelo.
        if ($this->libro_model->eliminar($id)) {
            $_SESSION['mensaje_exito'] = "Libro eliminado correctamente.";
        } else {
            // Si falla, puede ser por restricciones (ej. prestamos activos).
            $_SESSION['mensaje_error'] = "Error al eliminar el libro. Quizas tiene prestamos pendientes.";
        }
        // Redirige siempre a la lista de libros.
        header("Location: index.php?controller=libro&action=index");
        exit();
    }

    /**
     * Muestra la pagina de detalles de un libro especifico.
     *
     * Esta pagina es publica, cualquier usuario puede ver los detalles de un libro.
     */
    public function show() {
        // Obtiene el ID del libro de la URL.
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['mensaje_error'] = "No se especifico que libro mostrar.";
            header("Location: index.php?controller=libro&action=index");
            exit();
        }

        // Busca la informacion del libro en la base de datos.
        $libro = $this->libro_model->leerPorId($id);
        if (!$libro) {
            $_SESSION['mensaje_error'] = "El libro que buscas no existe.";
            header("Location: index.php?controller=libro&action=index");
            exit();
        }
        // Carga la vista que muestra los detalles del libro.
        include 'views/libros/show.php';
    }
}
?>