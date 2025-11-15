<?php
/**
 * Clase Libro (Modelo).
 *
 * Esta clase se encarga de todo lo relacionado con los libros en la base de datos.
 * Funciona como el molde para un libro y contiene todos los metodos para
 * crearlos, leerlos, actualizarlos y eliminarlos (CRUD). Tambien maneja la
* conexion entre un libro y sus autores.
 */
class Libro {
    // === PROPIEDADES DEL OBJETO LIBRO ===

    public $id_libro;         // El ID unico del libro.
    public $titulo;           // El titulo del libro.
    public $isbn;             // El codigo ISBN, unico para cada edicion de un libro.
    public $anio_publicacion; // El anio en que se publico el libro.
    public $descripcion;      // Un resumen o sinopsis del libro.
    public $disponible;       // Un indicador (1 o 0) para saber si esta prestado o no.
    public $fecha_creacion;   // La fecha en que se anadio el libro al sistema.

    /**
     * @var mysqli Guarda la conexion a la base de datos.
     * Es privada para que solo se use dentro de esta clase.
     */
    private $conn;

    /**
     * Constructor de la clase Libro.
     *
     * Se ejecuta al crear un nuevo objeto 'Libro'. Su trabajo es
     * guardar la conexion a la base de datos que se le pasa.
     *
     * @param mysqli $db La conexion a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo libro en la base de datos y lo asocia con sus autores.
     *
     * Utiliza una transaccion para asegurarse de que toda la operacion
     * sea un "todo o nada". Si algo falla, se deshace todo.
     *
     * @param string $titulo El titulo del libro.
     * @param string $isbn El ISBN del libro.
     * @param int $anio_publicacion El anio de publicacion.
     * @param string $descripcion Una breve descripcion.
     * @param array $autores_ids Un array con los IDs de los autores de este libro.
     * @return bool Devuelve 'true' si todo salio bien, 'false' si hubo algun error.
     */
    public function crear($titulo, $isbn, $anio_publicacion, $descripcion, $autores_ids) {
        // Limpiamos los datos de entrada para mas seguridad.
        $this->titulo = htmlspecialchars(strip_tags($titulo));
        $this->isbn = htmlspecialchars(strip_tags($isbn));
        $this->anio_publicacion = htmlspecialchars(strip_tags($anio_publicacion));
        $this->descripcion = htmlspecialchars(strip_tags($descripcion));

        // Iniciamos una transaccion. Esto agrupa varias consultas en una sola operacion.
        $this->conn->begin_transaction();

        try {
            // PASO 1: Insertar los datos del libro en la tabla 'libros'.
            $queryLibro = "INSERT INTO libros (titulo, isbn, anio_publicacion, descripcion) VALUES (?, ?, ?, ?)";
            $stmtLibro = $this->conn->prepare($queryLibro);
            if (!$stmtLibro) throw new Exception("Fallo al preparar la consulta del libro.");
            $stmtLibro->bind_param("ssis", $this->titulo, $this->isbn, $this->anio_publicacion, $this->descripcion);
            if (!$stmtLibro->execute()) throw new Exception("Fallo al guardar el libro.");

            // Obtenemos el ID que se le acaba de asignar al libro que insertamos.
            $this->id_libro = $this->conn->insert_id;

            // PASO 2: Asociar el libro con sus autores en la tabla 'libro_autor'.
            if (!empty($autores_ids)) {
                $queryAutor = "INSERT INTO libro_autor (id_libro, id_autor) VALUES (?, ?)";
                $stmtAutor = $this->conn->prepare($queryAutor);
                if (!$stmtAutor) throw new Exception("Fallo al preparar la consulta de autores.");

                // Recorremos el array de IDs de autores y creamos una fila por cada uno.
                foreach ($autores_ids as $id_autor) {
                    $id_autor_clean = htmlspecialchars(strip_tags($id_autor));
                    $stmtAutor->bind_param("ii", $this->id_libro, $id_autor_clean);
                    if (!$stmtAutor->execute()) throw new Exception("Fallo al asociar un autor.");
                }
            }

            // Si llegamos hasta aqui sin errores, confirmamos todos los cambios.
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo salio mal en cualquier punto, deshacemos todos los cambios.
            $this->conn->rollback();
            // Opcional: guardar el error en un log para revision.
            error_log("Error al crear libro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una lista de todos los libros.
     *
     * Tambien une los nombres de los autores en una sola cadena de texto.
     *
     * @param string $filtro_titulo Un texto para buscar libros que coincidan con ese titulo.
     * @return mysqli_result|false El resultado de la consulta o 'false' si hay un error.
     */
    public function leerTodos($filtro_titulo = null) {
        // Esta consulta une 3 tablas para obtener toda la informacion.
        // GROUP_CONCAT junta los nombres de los autores en una sola linea.
        $query = "SELECT l.id_libro, l.titulo, l.isbn, l.anio_publicacion, l.descripcion, l.disponible,
                         GROUP_CONCAT(CONCAT(a.nombre, ' ', a.apellido) SEPARATOR ', ') AS autores
                  FROM libros l
                  LEFT JOIN libro_autor la ON l.id_libro = la.id_libro
                  LEFT JOIN autores a ON la.id_autor = a.id_autor";

        // Si se proporciono un filtro de busqueda, lo anadimos a la consulta.
        if ($filtro_titulo) {
            $query .= " WHERE l.titulo LIKE ?";
        }

        $query .= " GROUP BY l.id_libro ORDER BY l.titulo ASC";

        // Si hay filtro, usamos una consulta preparada para mas seguridad.
        if ($filtro_titulo) {
            $stmt = $this->conn->prepare($query);
            $filtro_con_comodines = "%" . htmlspecialchars(strip_tags($filtro_titulo)) . "%";
            $stmt->bind_param("s", $filtro_con_comodines);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Si no hay filtro, ejecutamos la consulta directamente.
            $result = $this->conn->query($query);
        }

        return $result;
    }

    /**
     * Obtiene los datos de un solo libro por su ID.
     *
     * Incluye una lista con los nombres de sus autores y tambien una lista con sus IDs.
     *
     * @param int $id El ID del libro que se quiere buscar.
     * @return array|false Un array con los datos del libro, o 'false' si no se encuentra.
     */
    public function leerPorId($id) {
        $query = "SELECT l.id_libro, l.titulo, l.isbn, l.anio_publicacion, l.descripcion, l.disponible,
                         GROUP_CONCAT(CONCAT(a.nombre, ' ', a.apellido) SEPARATOR ', ') AS nombres_autores,
                         GROUP_CONCAT(a.id_autor) AS ids_autores
                  FROM libros l
                  LEFT JOIN libro_autor la ON l.id_libro = la.id_libro
                  LEFT JOIN autores a ON la.id_autor = a.id_autor
                  WHERE l.id_libro = ?
                  GROUP BY l.id_libro
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $libro = $result->fetch_assoc();

        // La consulta devuelve los IDs de los autores como "1,2,3".
        // Aqui lo convertimos en un array de verdad: [1, 2, 3].
        if ($libro && !empty($libro['ids_autores'])) {
            $libro['ids_autores'] = explode(',', $libro['ids_autores']);
        } else if ($libro) {
            $libro['ids_autores'] = [];
        }
        return $libro;
    }

    /**
     * Actualiza la informacion de un libro y la de sus autores.
     *
     * Usa una transaccion. La estrategia es: actualizar el libro, borrar
     * todas sus asociaciones de autores anteriores y luego insertar las nuevas.
     *
     * @param int $id_libro El ID del libro a modificar.
     * @param string $titulo El nuevo titulo.
     * @param string $isbn El nuevo ISBN.
     * @param int $anio_publicacion El nuevo anio.
     * @param string $descripcion La nueva descripcion.
     * @param array $autores_ids El nuevo array con los IDs de los autores.
     * @return bool 'true' si la actualizacion fue exitosa, 'false' si no.
     */
    public function actualizar($id_libro, $titulo, $isbn, $anio_publicacion, $descripcion, $autores_ids) {
        // Limpiamos los datos.
        $this->id_libro = htmlspecialchars(strip_tags($id_libro));
        $this->titulo = htmlspecialchars(strip_tags($titulo));
        $this->isbn = htmlspecialchars(strip_tags($isbn));
        $this->anio_publicacion = htmlspecialchars(strip_tags($anio_publicacion));
        $this->descripcion = htmlspecialchars(strip_tags($descripcion));

        // Iniciamos la transaccion.
        $this->conn->begin_transaction();

        try {
            // PASO 1: Actualizar los datos en la tabla 'libros'.
            $queryLibro = "UPDATE libros SET titulo = ?, isbn = ?, anio_publicacion = ?, descripcion = ? WHERE id_libro = ?";
            $stmtLibro = $this->conn->prepare($queryLibro);
            if (!$stmtLibro) throw new Exception("Fallo al preparar la actualizacion del libro.");
            $stmtLibro->bind_param("ssisi", $this->titulo, $this->isbn, $this->anio_publicacion, $this->descripcion, $this->id_libro);
            if (!$stmtLibro->execute()) throw new Exception("Fallo al ejecutar la actualizacion del libro.");

            // PASO 2: Borrar todas las relaciones autor-libro antiguas para este libro.
            $queryDeleteAutores = "DELETE FROM libro_autor WHERE id_libro = ?";
            $stmtDeleteAutores = $this->conn->prepare($queryDeleteAutores);
            if (!$stmtDeleteAutores) throw new Exception("Fallo al preparar la eliminacion de autores.");
            $stmtDeleteAutores->bind_param("i", $this->id_libro);
            if (!$stmtDeleteAutores->execute()) throw new Exception("Fallo al eliminar los autores antiguos.");

            // PASO 3: Insertar las nuevas relaciones autor-libro.
            if (!empty($autores_ids)) {
                $queryInsertAutor = "INSERT INTO libro_autor (id_libro, id_autor) VALUES (?, ?)";
                $stmtInsertAutor = $this->conn->prepare($queryInsertAutor);
                if (!$stmtInsertAutor) throw new Exception("Fallo al preparar la insercion de autores.");

                foreach ($autores_ids as $id_autor) {
                    $id_autor_clean = htmlspecialchars(strip_tags($id_autor));
                    $stmtInsertAutor->bind_param("ii", $this->id_libro, $id_autor_clean);
                    if (!$stmtInsertAutor->execute()) throw new Exception("Fallo al insertar un nuevo autor.");
                }
            }
            
            // Si todo salio bien, confirmamos los cambios.
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo fallo, deshacemos todo.
            $this->conn->rollback();
            error_log("Error al actualizar libro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un libro de la base de datos.
     *
     * Antes de borrar, comprueba si el libro esta actualmente prestado.
     * Si lo esta, no permite la eliminacion.
     *
     * @param int $id El ID del libro a eliminar.
     * @return bool 'true' si se borro, 'false' si no se pudo.
     */
    public function eliminar($id) {
        // PASO 1: Comprobar si el libro tiene prestamos activos.
        $queryCheck = "SELECT COUNT(*) AS count FROM prestamos WHERE id_libro = ? AND estado = 'prestado'";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bind_param("i", $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result()->fetch_assoc();

        // Si el contador es mayor que cero, el libro esta prestado.
        if ($resultCheck['count'] > 0) {
            return false; // No se puede borrar.
        }

        // Si no esta prestado, procedemos a borrarlo con una transaccion.
        $this->conn->begin_transaction();
        try {
            // PASO 2: Eliminar las asociaciones con autores.
            $queryDeleteAutores = "DELETE FROM libro_autor WHERE id_libro = ?";
            $stmtDeleteAutores = $this->conn->prepare($queryDeleteAutores);
            if (!$stmtDeleteAutores) throw new Exception("Fallo al preparar la eliminacion de asociaciones.");
            $stmtDeleteAutores->bind_param("i", $id);
            if (!$stmtDeleteAutores->execute()) throw new Exception("Fallo al eliminar las asociaciones.");

            // PASO 3: Eliminar el libro de la tabla principal.
            $queryLibro = "DELETE FROM libros WHERE id_libro = ?";
            $stmtLibro = $this->conn->prepare($queryLibro);
            if (!$stmtLibro) throw new Exception("Fallo al preparar la eliminacion del libro.");
            $stmtLibro->bind_param("i", $id);
            if (!$stmtLibro->execute()) throw new Exception("Fallo al eliminar el libro.");

            // Si todo fue bien, confirmamos.
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si algo fallo, deshacemos.
            $this->conn->rollback();
            error_log("Error al eliminar libro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza solo el estado de disponibilidad de un libro.
     *
     * Este metodo es util para marcar un libro como 'prestado' o 'disponible'
     * rapidamente sin tener que modificar el resto de su informacion.
     *
     * @param int $id_libro El ID del libro a modificar.
     * @param bool $disponible El nuevo estado (1 para disponible, 0 para no disponible).
     * @return bool 'true' si se actualizo, 'false' si no.
     */
    public function actualizarDisponibilidad($id_libro, $disponible) {
        $query = "UPDATE libros SET disponible = ? WHERE id_libro = ?";
        $stmt = $this->conn->prepare($query);
        // El primer 'i' es por $disponible (que sera 1 o 0, un entero), el segundo por $id_libro.
        $stmt->bind_param("ii", $disponible, $id_libro);
        return $stmt->execute();
    }

    /**
     * Obtiene una lista simple de los libros que estan disponibles.
     *
     * Es util para poblar menus desplegables, por ejemplo, en el formulario
     * para crear un nuevo prestamo.
     *
     * @return mysqli_result|false La lista de libros o 'false' si hay error.
     */
    public function obtenerLibrosDisponibles() {
        $query = "SELECT id_libro, titulo FROM libros WHERE disponible = TRUE ORDER BY titulo ASC";
        $result = $this->conn->query($query);
        return $result;
    }
}
?>