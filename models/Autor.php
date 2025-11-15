<?php
/**
 * Clase Autor (Modelo).
 *
 * Esta clase representa a un autor en nuestra base de datos.
 * Funciona como un puente entre nuestro codigo PHP y la tabla 'autores'.
 * Contiene todos los metodos necesarios para crear, leer, actualizar
 * y eliminar (CRUD) registros de autores.
 */
class Autor {
    // === PROPIEDADES DEL OBJETO AUTOR ===

    /**
     * @var int El ID unico del autor en la base de datos.
     */
    public $id_autor;

    /**
     * @var string El nombre de pila del autor.
     */
    public $nombre;

    /**
     * @var string El apellido del autor.
     */
    public $apellido;

    /**
     * @var string El pais de origen del autor.
     */
    public $nacionalidad;

    /**
     * @var mysqli Guarda el objeto de conexion a la base de datos.
     * Es privada para que solo los metodos de esta clase puedan usarla.
     */
    private $conn;

    /**
     * Constructor de la clase Autor.
     *
     * Este metodo se ejecuta cada vez que creamos un nuevo objeto 'Autor'.
     * Su unica funcion es recibir la conexion a la base de datos
     * y guardarla en la propiedad $conn para poder usarla mas tarde.
     *
     * @param mysqli $db La conexion activa a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo registro de autor en la base de datos.
     *
     * @param string $nombre El nombre del autor a crear.
     * @param string $apellido El apellido del autor a crear.
     * @param string $nacionalidad La nacionalidad del autor.
     * @return bool Devuelve 'true' si el autor se creo con exito, 'false' si hubo un error.
     */
    public function crear($nombre, $apellido, $nacionalidad) {
        // Limpiamos los datos antes de guardarlos para evitar problemas de seguridad (como XSS).
        $this->nombre = htmlspecialchars(strip_tags($nombre));
        $this->apellido = htmlspecialchars(strip_tags($apellido));
        $this->nacionalidad = htmlspecialchars(strip_tags($nacionalidad));

        // La consulta SQL para insertar un nuevo autor. Los '?' son marcadores de posicion.
        $query = "INSERT INTO autores (nombre, apellido, nacionalidad) VALUES (?, ?, ?)";
        
        // Preparamos la consulta para evitar inyeccion SQL.
        $stmt = $this->conn->prepare($query);
        // Vinculamos los datos a los marcadores de posicion. "sss" significa que son 3 strings.
        $stmt->bind_param("sss", $this->nombre, $this->apellido, $this->nacionalidad);

        // Ejecutamos la consulta. Si funciona, devolvemos true.
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene todos los autores de la base de datos.
     *
     * @return mysqli_result|false Devuelve el resultado de la consulta para poder recorrerlo,
     * o 'false' si algo salio mal.
     */
    public function leerTodos() {
        // Consulta para seleccionar todos los autores, ordenados alfabeticamente por apellido y nombre.
        $query = "SELECT id_autor, nombre, apellido, nacionalidad FROM autores ORDER BY apellido, nombre ASC";
        $result = $this->conn->query($query);
        return $result;
    }

    /**
     * Obtiene los datos de un autor especifico usando su ID.
     *
     * @param int $id El ID del autor que queremos buscar.
     * @return array|false Devuelve un array con los datos del autor si se encuentra,
     * o 'false' si no existe un autor con ese ID.
     */
    public function leerPorId($id) {
        // Consulta para buscar un autor por su id_autor. LIMIT 1 para que sea mas eficiente.
        $query = "SELECT id_autor, nombre, apellido, nacionalidad FROM autores WHERE id_autor = ? LIMIT 0,1";
        
        // Preparamos la consulta de forma segura.
        $stmt = $this->conn->prepare($query);
        // Vinculamos el ID. "i" significa que es un entero (integer).
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Obtenemos el resultado y lo devolvemos como un array asociativo.
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Actualiza los datos de un autor que ya existe.
     *
     * @param int $id_autor El ID del autor que vamos a modificar.
     * @param string $nombre El nuevo nombre para el autor.
     * @param string $apellido El nuevo apellido para el autor.
     * @param string $nacionalidad La nueva nacionalidad para el autor.
     * @return bool Devuelve 'true' si la actualizacion fue exitosa, 'false' si no.
     */
    public function actualizar($id_autor, $nombre, $apellido, $nacionalidad) {
        // Limpiamos todos los datos recibidos por seguridad.
        $this->id_autor = htmlspecialchars(strip_tags($id_autor));
        $this->nombre = htmlspecialchars(strip_tags($nombre));
        $this->apellido = htmlspecialchars(strip_tags($apellido));
        $this->nacionalidad = htmlspecialchars(strip_tags($nacionalidad));

        // La consulta SQL para actualizar.
        $query = "UPDATE autores SET nombre = ?, apellido = ?, nacionalidad = ? WHERE id_autor = ?";
        
        $stmt = $this->conn->prepare($query);
        // Vinculamos los parametros. "sssi" son 3 strings y 1 integer.
        $stmt->bind_param("sssi", $this->nombre, $this->apellido, $this->nacionalidad, $this->id_autor);

        // Si la ejecucion es exitosa, devolvemos true.
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Elimina un autor de la base de datos.
     *
     * Importante: Antes de borrar, comprueba si el autor tiene libros asociados.
     * Si los tiene, no permite el borrado para mantener la integridad de los datos.
     *
     * @param int $id El ID del autor a eliminar.
     * @return bool Devuelve 'true' si se elimina, 'false' si no se puede (porque tiene libros o por un error).
     */
    public function eliminar($id) {
        // PASO 1: Verificar si el autor esta ligado a algun libro.
        // Contamos cuantas veces aparece el ID del autor en la tabla intermedia 'libro_autor'.
        $queryCheck = "SELECT COUNT(*) AS count FROM libro_autor WHERE id_autor = ?";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bind_param("i", $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result()->fetch_assoc();

        // Si el contador es mayor que cero, significa que tiene libros.
        if ($resultCheck['count'] > 0) {
            // En este caso, no lo borramos y devolvemos 'false'.
            return false;
        }

        // PASO 2: Si no tiene libros, procedemos a eliminarlo.
        $query = "DELETE FROM autores WHERE id_autor = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        // Si la eliminacion es exitosa, devolvemos 'true'.
        if ($stmt->execute()) {
            return true;
        }
        return false; // Si hubo algun otro error.
    }
}
?>