<?php
/**
 * Clase Prestamo (Modelo).
 *
 * Esta clase es el molde para los prestamos y se encarga de toda la
 * logica de base de datos para la tabla 'prestamos'. Proporciona
 * metodos para crear, devolver y consultar prestamos de libros.
 */
class Prestamo {
    // === PROPIEDADES DEL OBJETO PRESTAMO ===

    public $id_prestamo;                 // El ID unico del prestamo.
    public $id_libro;                    // El ID del libro que fue prestado.
    public $id_usuario;                  // El ID del usuario que tiene el libro.
    public $fecha_prestamo;              // La fecha en que se hizo el prestamo.
    public $fecha_devolucion_esperada;   // La fecha limite para devolver el libro.
    public $fecha_devolucion_real;       // La fecha en que se devolvio de verdad (puede ser nula).
    public $estado;                      // El estado actual: 'prestado', 'devuelto', 'atrasado'.

    /**
     * @var mysqli Guarda la conexion a la base de datos para usarla en los metodos.
     */
    private $conn;

    /**
     * Constructor de la clase Prestamo.
     *
     * Se ejecuta al crear un objeto 'Prestamo'. Su unica tarea es
     * recibir y guardar la conexion a la base de datos.
     *
     * @param mysqli $db La conexion activa a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registra un nuevo prestamo en el sistema.
     *
     * Esta operacion es critica, por lo que usa una transaccion. Esto significa
     * que o se completan los dos pasos (crear el prestamo Y actualizar el libro),
     * o no se hace ninguno. Asi se evita inconsistencia en los datos.
     *
     * @param int $id_libro El ID del libro que se va a prestar.
     * @param int $id_usuario El ID del usuario que se lleva el libro.
     * @param string $fecha_prestamo La fecha de inicio del prestamo.
     * @param string $fecha_devolucion_esperada La fecha en que debe ser devuelto.
     * @return bool 'true' si todo salio bien, 'false' si algo fallo.
     */
    public function crear($id_libro, $id_usuario, $fecha_prestamo, $fecha_devolucion_esperada) {
        // Se inicia la transaccion para agrupar las consultas.
        $this->conn->begin_transaction();

        try {
            // PASO 1: Insertar el nuevo registro en la tabla 'prestamos'.
            $queryPrestamo = "INSERT INTO prestamos (id_libro, id_usuario, fecha_prestamo, fecha_devolucion_esperada, estado) VALUES (?, ?, ?, ?, 'prestado')";
            $stmtPrestamo = $this->conn->prepare($queryPrestamo);
            if (!$stmtPrestamo) throw new Exception("Error preparando la consulta de prestamo.");

            // Limpiamos los datos antes de usarlos.
            $id_libro_clean = htmlspecialchars(strip_tags($id_libro));
            $id_usuario_clean = htmlspecialchars(strip_tags($id_usuario));
            $fecha_prestamo_clean = htmlspecialchars(strip_tags($fecha_prestamo));
            $fecha_devolucion_esperada_clean = htmlspecialchars(strip_tags($fecha_devolucion_esperada));

            $stmtPrestamo->bind_param("iiss", $id_libro_clean, $id_usuario_clean, $fecha_prestamo_clean, $fecha_devolucion_esperada_clean);
            if (!$stmtPrestamo->execute()) throw new Exception("Error al guardar el prestamo.");

            // PASO 2: Actualizar el estado del libro para que ya no este disponible.
            $queryLibro = "UPDATE libros SET disponible = FALSE WHERE id_libro = ?";
            $stmtLibro = $this->conn->prepare($queryLibro);
            if (!$stmtLibro) throw new Exception("Error preparando la actualizacion del libro.");
            $stmtLibro->bind_param("i", $id_libro_clean);
            if (!$stmtLibro->execute()) throw new Exception("Error al cambiar la disponibilidad del libro.");
            
            // Si los dos pasos funcionaron, se confirman los cambios.
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si algo fallo, se deshace cualquier cambio que se haya hecho.
            $this->conn->rollback();
            error_log("Error al crear prestamo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra la devolucion de un libro.
     *
     * Al igual que al crear, usa una transaccion para asegurar que el prestamo
     * se actualice y que el libro vuelva a estar disponible, todo en una
     * sola operacion atomica.
     *
     * @param int $id_prestamo El ID del prestamo que se esta finalizando.
     * @return bool 'true' si la devolucion se proceso correctamente, 'false' si no.
     */
    public function registrarDevolucion($id_prestamo) {
        // Se inicia la transaccion.
        $this->conn->begin_transaction();

        try {
            // PASO 1: Necesitamos saber que libro es. Lo buscamos usando el ID del prestamo.
            $queryGetLibroId = "SELECT id_libro FROM prestamos WHERE id_prestamo = ?";
            $stmtGetLibroId = $this->conn->prepare($queryGetLibroId);
            if (!$stmtGetLibroId) throw new Exception("Error preparando la busqueda del libro.");
            $stmtGetLibroId->bind_param("i", $id_prestamo);
            $stmtGetLibroId->execute();
            $resultGetLibroId = $stmtGetLibroId->get_result();
            $row = $resultGetLibroId->fetch_assoc();
            if (!$row) throw new Exception("No se encontro el prestamo.");
            $id_libro = $row['id_libro'];

            // PASO 2: Actualizar el registro del prestamo con la fecha de hoy y el estado 'devuelto'.
            $fecha_devolucion_real = date('Y-m-d'); // La fecha actual.
            $queryPrestamo = "UPDATE prestamos SET fecha_devolucion_real = ?, estado = 'devuelto' WHERE id_prestamo = ?";
            $stmtPrestamo = $this->conn->prepare($queryPrestamo);
            if (!$stmtPrestamo) throw new Exception("Error preparando la actualizacion del prestamo.");
            $stmtPrestamo->bind_param("si", $fecha_devolucion_real, $id_prestamo);
            if (!$stmtPrestamo->execute()) throw new Exception("Error al actualizar el prestamo.");

            // PASO 3: Poner el libro como disponible de nuevo.
            $queryLibro = "UPDATE libros SET disponible = TRUE WHERE id_libro = ?";
            $stmtLibro = $this->conn->prepare($queryLibro);
            if (!$stmtLibro) throw new Exception("Error preparando la actualizacion de disponibilidad del libro.");
            $stmtLibro->bind_param("i", $id_libro);
            if (!$stmtLibro->execute()) throw new Exception("Error al actualizar la disponibilidad del libro.");

            // Si todo salio bien, confirmamos.
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si algo fallo, lo deshacemos todo.
            $this->conn->rollback();
            error_log("Error al registrar devolucion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una lista de todos los prestamos.
     *
     * Une la informacion de las tablas de libros y usuarios para mostrar
     * nombres en lugar de solo IDs. Permite filtrar los resultados.
     *
     * @param int|null $id_usuario_filtro Para ver solo los prestamos de un usuario especifico.
     * @param string|null $estado_filtro Para ver solo prestamos con un estado ('prestado', 'atrasado', etc.).
     * @return mysqli_result|false Un objeto con los resultados o 'false' si hay un error.
     */
    public function leerTodos($id_usuario_filtro = null, $estado_filtro = null) {
        // Consulta base que une las tres tablas.
        $query = "SELECT p.id_prestamo, l.titulo AS titulo_libro, u.nombre_usuario,
                         p.fecha_prestamo, p.fecha_devolucion_esperada, p.fecha_devolucion_real, p.estado
                  FROM prestamos p
                  JOIN libros l ON p.id_libro = l.id_libro
                  JOIN usuarios u ON p.id_usuario = u.id_usuario";

        $conditions = []; // Aqui guardaremos las condiciones del WHERE.
        $types = "";      // Aqui los tipos de datos para bind_param ('i' o 's').
        $params = [];     // Aqui los valores de los parametros.

        // Si se nos dio un ID de usuario, anadimos una condicion.
        if ($id_usuario_filtro !== null) {
            $conditions[] = "p.id_usuario = ?";
            $types .= "i";
            $params[] = $id_usuario_filtro;
        }
        // Si se nos dio un estado, anadimos otra condicion.
        if ($estado_filtro !== null) {
            $conditions[] = "p.estado = ?";
            $types .= "s";
            $params[] = $estado_filtro;
        }

        // Si hay condiciones, las unimos con 'AND' y las anadimos a la consulta.
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY p.fecha_prestamo DESC"; // Ordenamos por fecha.

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Error al preparar leerTodos (Prestamo): " . $this->conn->error);
            return false;
        }
        // Si hay parametros, los vinculamos a la consulta.
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Obtiene los datos de un unico prestamo por su ID.
     *
     * @param int $id El ID del prestamo a buscar.
     * @return array|false Un array con los datos si se encuentra, o 'false' si no.
     */
    public function leerPorId($id) {
        $query = "SELECT p.id_prestamo, l.titulo AS titulo_libro, u.nombre_usuario,
                         p.fecha_prestamo, p.fecha_devolucion_esperada, p.fecha_devolucion_real, p.estado,
                         p.id_libro, p.id_usuario
                  FROM prestamos p
                  JOIN libros l ON p.id_libro = l.id_libro
                  JOIN usuarios u ON p.id_usuario = u.id_usuario
                  WHERE p.id_prestamo = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Actualiza el estado de los prestamos a 'atrasado'.
     *
     * Esta es una funcion de mantenimiento. Busca todos los prestamos que
     * siguen activos ('prestado') pero cuya fecha de devolucion ya paso,
     * y les cambia el estado a 'atrasado'.
     *
     * @return bool 'true' si la consulta se ejecuto bien, 'false' si hubo error.
     */
    public function actualizarEstadosAtrasados() {
        // La consulta es simple: cambia 'prestado' a 'atrasado' si la fecha_devolucion_esperada es menor que hoy.
        $query = "UPDATE prestamos
                  SET estado = 'atrasado'
                  WHERE estado = 'prestado' AND fecha_devolucion_esperada < CURDATE()";
        
        $result = $this->conn->query($query);
        return $result !== false;
    }
}
?>