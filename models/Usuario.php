<?php
/**
 * Clase Usuario (Modelo).
 *
 * Esta clase es el corazon de la gestion de usuarios. Representa a un usuario
 * en la base de datos y contiene toda la logica para registrar nuevos usuarios,
 * verificar sus credenciales para iniciar sesion y obtener su informacion.
 */
class Usuario {
    // === PROPIEDADES DEL OBJETO USUARIO ===

    public $id_usuario;          // El ID unico del usuario.
    public $nombre_usuario;      // El nombre de usuario para el login, debe ser unico.
    public $correo_electronico;  // El email del usuario, tambien unico.
    public $id_rol;              // El ID que lo conecta con su rol (admin, usuario, etc.).
    public $fecha_registro;      // La fecha en que se creo la cuenta.

    /**
     * @var string La contrasena encriptada del usuario.
     * Es privada para que no se pueda acceder a ella directamente desde fuera.
     */
    private $contrasena_hash;

    /**
     * @var mysqli Guarda la conexion a la base de datos.
     */
    private $conn;

    /**
     * Constructor de la clase Usuario.
     *
     * Se ejecuta al crear un objeto 'Usuario'. Solo necesita
     * que se le pase la conexion a la base de datos para poder funcionar.
     *
     * @param mysqli $db La conexion a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     *
     * Se encarga de limpiar los datos, encriptar la contrasena de forma segura
     * y verificar que el nombre de usuario o el email no esten ya en uso.
     *
     * @param string $nombre_usuario El nombre de usuario elegido.
     * @param string $correo_electronico El email del nuevo usuario.
     * @param string $contrasena La contrasena sin encriptar.
     * @param int $id_rol El rol que se le asignara (por defecto es 2, 'usuario').
     * @return bool Devuelve 'true' si el registro fue exitoso, 'false' si no.
     */
    public function crearUsuario($nombre_usuario, $correo_electronico, $contrasena, $id_rol = 2) {
        // PASO 1: Limpiar los datos para evitar problemas de seguridad.
        $this->nombre_usuario = htmlspecialchars(strip_tags($nombre_usuario));
        $this->correo_electronico = htmlspecialchars(strip_tags($correo_electronico));
        $this->id_rol = htmlspecialchars(strip_tags($id_rol));
        
        // PASO 2: Hashear la contrasena. Nunca se guarda la contrasena original.
        $this->contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // PASO 3: Verificar que el usuario o el email no existan ya.
        if ($this->existeUsuario($this->nombre_usuario, $this->correo_electronico)) {
            return false; // Si ya existe, no se puede crear.
        }

        // PASO 4: Preparar y ejecutar la insercion en la base de datos.
        $query = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena_hash, id_rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Error al preparar la consulta de crearUsuario: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("sssi", $this->nombre_usuario, $this->correo_electronico, $this->contrasena_hash, $this->id_rol);

        if ($stmt->execute()) {
            return true; // Exito.
        } else {
            error_log("Error al ejecutar la consulta de crearUsuario: " . $stmt->error);
            return false; // Fallo.
        }
    }

    /**
     * Metodo auxiliar para comprobar si un nombre de usuario o email ya estan registrados.
     *
     * @param string $nombre_usuario El nombre de usuario a comprobar.
     * @param string $correo_electronico El email a comprobar.
     * @return bool Devuelve 'true' si ya existe, 'false' si esta disponible.
     */
    public function existeUsuario($nombre_usuario, $correo_electronico) {
        $query = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? OR correo_electronico = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $nombre_usuario, $correo_electronico);
        $stmt->execute();
        $result = $stmt->get_result();
        // Si el numero de filas encontradas es mayor a 0, significa que ya existe.
        return $result->num_rows > 0;
    }

    /**
     * Autentica a un usuario.
     *
     * Busca al usuario por su credencial (que puede ser su nombre de usuario o su email)
     * y luego verifica si la contrasena proporcionada coincide con la que esta
     * guardada (hasheada) en la base de datos.
     *
     * @param string $credencial El nombre de usuario o email.
     * @param string $contrasena La contrasena sin encriptar.
     * @return Usuario|false Devuelve el objeto Usuario si el login es correcto, o 'false' si no lo es.
     */
    public function login($credencial, $contrasena) {
        // Busca un usuario que coincida con la credencial.
        $query = "SELECT id_usuario, nombre_usuario, correo_electronico, contrasena_hash, id_rol FROM usuarios WHERE nombre_usuario = ? OR correo_electronico = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $credencial, $credencial);
        $stmt->execute();
        $result = $stmt->get_result();

        // Si se encontro exactamente un usuario...
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // ...comparamos la contrasena proporcionada con el hash guardado.
            if (password_verify($contrasena, $row['contrasena_hash'])) {
                // Si coinciden, llenamos el objeto actual con los datos del usuario.
                $this->id_usuario = $row['id_usuario'];
                $this->nombre_usuario = $row['nombre_usuario'];
                $this->correo_electronico = $row['correo_electronico'];
                $this->id_rol = $row['id_rol'];
                // Y devolvemos el propio objeto. Login exitoso.
                return $this;
            }
        }
        // Si no se encuentra el usuario o la contrasena no coincide, el login falla.
        return false;
    }

    /**
     * Obtiene los datos publicos de un usuario por su ID.
     *
     * @param int $id El ID del usuario a buscar.
     * @return array|false Un array con los datos del usuario, o 'false' si no se encuentra.
     */
    public function leerPorId($id) {
        // Nota: No seleccionamos la contrasena por seguridad.
        $query = "SELECT id_usuario, nombre_usuario, correo_electronico, id_rol FROM usuarios WHERE id_usuario = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene una lista de todos los usuarios registrados.
     *
     * Une la tabla de usuarios con la de roles para mostrar el nombre del rol
     * en lugar de solo su ID, lo que es mas claro para la visualizacion.
     *
     * @return mysqli_result|false Un objeto con los resultados o 'false' si hay un error.
     */
    public function leerTodos() {
        $query = "SELECT u.id_usuario, u.nombre_usuario, u.correo_electronico, r.nombre_rol, u.fecha_registro
                  FROM usuarios u
                  JOIN roles r ON u.id_rol = r.id_rol
                  ORDER BY u.nombre_usuario ASC";
        $result = $this->conn->query($query);
        return $result;
    }
}
?>