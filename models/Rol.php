<?php
/**
 * Clase Rol (Modelo).
 *
 * Esta clase representa un rol de usuario en la base de datos (por ejemplo,
 * 'administrador' o 'usuario'). Aunque es un modelo simple, es fundamental
 * para la gestion de permisos en la aplicacion y puede ser extendido
 * en el futuro si se necesitan mas niveles de acceso.
 */
class Rol {
    // === PROPIEDADES DEL OBJETO ROL ===

    /**
     * @var int El ID unico del rol (ej. 1 para admin, 2 para usuario).
     */
    public $id_rol;

    /**
     * @var string El nombre descriptivo del rol.
     */
    public $nombre_rol;

    /**
     * @var mysqli Guarda la conexion a la base de datos para hacer consultas.
     */
    private $conn;

    /**
     * Constructor de la clase Rol.
     *
     * Se ejecuta al crear un nuevo objeto 'Rol'. Su unica funcion es
     * recibir y almacenar la conexion a la base de datos.
     *
     * @param mysqli $db La conexion activa a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene una lista de todos los roles disponibles en el sistema.
     *
     * @return mysqli_result|false Devuelve el resultado de la consulta para poder
     *                             recorrerlo, o 'false' si ocurre un error.
     */
    public function leerTodos() {
        // Consulta simple para traer todos los roles, ordenados por nombre.
        $query = "SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol";
        $result = $this->conn->query($query);
        return $result;
    }

    /**
     * Busca un rol especifico por su ID.
     *
     * @param int $id_rol El ID del rol que se quiere encontrar.
     * @return array|false Devuelve un array con los datos del rol si lo encuentra,
     *                     o 'false' si no existe.
     */
    public function leerPorId($id_rol) {
        // Consulta preparada para buscar un rol por su clave primaria.
        $query = "SELECT id_rol, nombre_rol FROM roles WHERE id_rol = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        // Vinculamos el ID. 'i' significa que es un entero.
        $stmt->bind_param("i", $id_rol);
        $stmt->execute();
        
        $result = $stmt->get_result();
        // fetch_assoc() lo devuelve como un array ['id_rol' => 1, 'nombre_rol' => 'administrador'].
        return $result->fetch_assoc();
    }

    /**
     * Busca un rol especifico por su nombre.
     *
     * Esto puede ser util para obtener el ID de un rol a partir de su nombre.
     *
     * @param string $nombre_rol El nombre del rol a buscar.
     * @return array|false Devuelve un array con los datos del rol, o 'false' si no existe.
     */
    public function leerPorNombre($nombre_rol) {
        // Consulta preparada para buscar por el campo 'nombre_rol'.
        $query = "SELECT id_rol, nombre_rol FROM roles WHERE nombre_rol = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        // Vinculamos el nombre. 's' significa que es un string.
        $stmt->bind_param("s", $nombre_rol);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>```