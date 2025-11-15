<?php
/**
 * Clase AuthController.
 *
 * Este controlador se encarga de todo lo relacionado con la autenticacion
 * de usuarios. Gestiona el inicio de sesion, el registro de nuevas
 * cuentas y el cierre de sesion.
 */
class AuthController {
    /**
     * @var mysqli Almacena el objeto de conexion a la base de datos.
     * Es necesario para que el controlador pueda interactuar con la BD.
     */
    private $conn;

    /**
     * @var Usuario Instancia del modelo Usuario.
     * Se usa para realizar operaciones especificas de usuarios,
     * como verificar credenciales o crear nuevos registros.
     */
    private $usuario_model;

    /**
     * Constructor de la clase AuthController.
     *
     * Se ejecuta automaticamente al crear un objeto de este controlador.
     * Su funcion es inicializar la conexion a la base de datos
     * y crear una instancia del modelo de usuario que se usara mas adelante.
     *
     * @param mysqli $db El objeto de conexion a la base de datos ya establecido.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->usuario_model = new Usuario($this->conn);
    }

    /**
     * Gestiona el inicio de sesion.
     *
     * Si la peticion es POST, intenta autenticar al usuario.
     * Si la peticion es GET, simplemente muestra el formulario de login.
     */
    public function login() {
        // Comprueba si el formulario fue enviado (metodo POST).
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge los datos del formulario de forma segura.
            $credencial = $_POST['credencial'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            // Llama al modelo para que verifique las credenciales en la base de datos.
            $usuario = $this->usuario_model->login($credencial, $contrasena);

            // Si el modelo devuelve un usuario, la autenticacion fue exitosa.
            if ($usuario) {
                // Se guardan los datos importantes del usuario en la sesion.
                $_SESSION['user_id'] = $usuario->id_usuario;
                $_SESSION['username'] = $usuario->nombre_usuario;
                $_SESSION['rol_id'] = $usuario->id_rol; // Guardamos el rol para los permisos.
                $_SESSION['mensaje_exito'] = "Bienvenido de nuevo, " . $usuario->nombre_usuario . "!";
                // Redirige al usuario a la pagina principal.
                header("Location: index.php");
                exit();
            } else {
                // Si las credenciales son incorrectas, se guarda un mensaje de error.
                $_SESSION['mensaje_error'] = "Las credenciales no son correctas. Intentalo de nuevo.";
                // Se redirige de vuelta a la pagina de login para que lo intente otra vez.
                header("Location: index.php?controller=auth&action=login");
                exit();
            }
        } else {
            // Si no es una peticion POST, es una peticion GET.
            // Primero, verifica si el usuario ya inicio sesion.
            if (isset($_SESSION['user_id'])) {
                // Si ya hay una sesion activa, lo mandamos al inicio. No necesita volver a loguearse.
                header("Location: index.php");
                exit();
            }
            // Si no hay sesion activa, se muestra la vista con el formulario de login.
            include 'views/auth/login.php';
        }
    }

    /**
     * Gestiona el registro de un nuevo usuario.
     *
     * Si la peticion es POST, procesa los datos del formulario para crear la cuenta.
     * Si la peticion es GET, muestra el formulario de registro.
     */
    public function register() {
        // Comprueba si el formulario de registro fue enviado.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoge los datos del formulario.
            $nombre_usuario = $_POST['nombre_usuario'] ?? '';
            $correo_electronico = $_POST['correo_electronico'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

            // Validacion basica: que ningun campo este vacio.
            if (empty($nombre_usuario) || empty($correo_electronico) || empty($contrasena) || empty($confirmar_contrasena)) {
                $_SESSION['mensaje_error'] = "Por favor, completa todos los campos.";
                header("Location: index.php?controller=auth&action=register");
                exit();
            }

            // Validacion basica: que las contrasenas coincidan.
            if ($contrasena !== $confirmar_contrasena) {
                $_SESSION['mensaje_error'] = "Las contrasenas que ingresaste no coinciden.";
                header("Location: index.php?controller=auth&action=register");
                exit();
            }

            // Intenta crear el usuario a traves del modelo.
            if ($this->usuario_model->crearUsuario($nombre_usuario, $correo_electronico, $contrasena)) {
                // Si se crea con exito, se muestra un mensaje y se le envia al login.
                $_SESSION['mensaje_exito'] = "Tu cuenta ha sido creada. Ahora puedes iniciar sesion.";
                header("Location: index.php?controller=auth&action=login");
                exit();
            } else {
                // Si falla (por ejemplo, usuario o email ya existen), se notifica el error.
                $_SESSION['mensaje_error'] = "Hubo un error al registrarte. Es posible que el usuario o correo ya esten en uso.";
                header("Location: index.php?controller=auth&action=register");
                exit();
            }
        } else {
            // Si es una peticion GET.
            // Si el usuario ya esta logueado, no tiene sentido que se registre de nuevo.
            if (isset($_SESSION['user_id'])) {
                header("Location: index.php");
                exit();
            }
            // Muestra la vista con el formulario de registro.
            include 'views/auth/register.php';
        }
    }

    /**
     * Cierra la sesion del usuario.
     *
     * Destruye toda la informacion de la sesion actual y redirige
     * al usuario a la pagina de inicio de sesion.
     */
    public function logout() {
        // Destruye la sesion actual.
        session_destroy();
        // Prepara un mensaje de exito para mostrar en la pagina de login.
        // Se tiene que iniciar una nueva sesion para que el mensaje se guarde,
        // pero como redirigimos inmediatamente, lo hacemos asi.
        // session_start(); // Esto podria ser necesario segun la configuracion del servidor.
        $_SESSION['mensaje_exito'] = "Has cerrado sesion correctamente.";
        // Redirige a la pagina de login.
        header("Location: index.php?controller=auth&action=login");
        exit();
    }
}
?>