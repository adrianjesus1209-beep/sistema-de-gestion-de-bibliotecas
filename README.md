#  Sistema de Gestión de Biblioteca

Un sistema web básico para la gestión de una pequeña biblioteca, desarrollado en PHP nativo siguiendo la arquitectura Modelo-Vista-Controlador (MVC). La aplicación permite la administración de libros, autores y préstamos, e incluye un sistema de autenticación de usuarios.

##  Características Principales

*   **Autenticación de Usuarios**: Sistema seguro de registro e inicio de sesión para el personal de la biblioteca.
*   **Gestión de Libros**: Funcionalidad completa para añadir, ver, editar y eliminar libros del catálogo.
*   **Gestión de Autores**: Administración de los autores, permitiendo crear, visualizar, actualizar y eliminar sus registros.
*   **Gestión de Préstamos**: Sistema para registrar, consultar y gestionar los préstamos de libros a los usuarios.
*   **Interfaz Intuitiva**: Vistas claras y organizadas para una fácil navegación y gestión.

##  Estructura del Proyecto

El proyecto está organizado siguiendo el patrón de diseño **Modelo-Vista-Controlador (MVC)** para separar la lógica de negocio, la representación de los datos y la interfaz de usuario.

```
biblioteca_web/
│
├──  config/
│   └──  database.php       # Archivo de configuración para la conexión a la base de datos.
│
├──  controllers/
│   ├──  AuthController.php    # Controla la lógica de registro e inicio de sesión.
│   ├──  AutorController.php   # Gestiona las peticiones CRUD para los autores.
│   ├──  LibroController.php   # Gestiona las peticiones CRUD para los libros.
│   └──  PrestamoController.php# Gestiona las peticiones para los préstamos.
│
├──  models/
│   ├──  Autor.php           # Modelo que representa la tabla 'autores' y su lógica.
│   ├──  Libro.php           # Modelo que representa la tabla 'libros'.
│   ├──  Prestamo.php        # Modelo que representa la tabla 'prestamos'.
│   ├──  Rol.php             # Modelo que representa los roles de usuario.
│   └──  Usuario.php         # Modelo que representa la tabla 'usuarios'.
│
├──  views/
│   ├──  auth/               # Vistas para el login y registro de usuarios.
│   ├──  autores/            # Vistas CRUD (create, edit, index, show) para autores.
│   ├──  libros/             # Vistas CRUD para libros.
│   ├──  prestamos/          # Vistas CRUD para préstamos.
│   └──  shared/             # Vistas parciales reutilizables (header, footer).
│
├──  .htaccess               # Configuración de Apache para reescritura de URL (URL amigables).
└──  index.php                # Punto de entrada de la aplicación (Front Controller).
```

##  Tecnologías Utilizadas

*   **Backend**: PHP
*   **Base de Datos**: MySQL
*   **Frontend**: HTML, CSS, JavaScript
*   **Servidor Web**: Apache

##  Diseño de la Base de Datos

La base de datos del sistema es de tipo relacional y fue diseñada aplicando las **tres primeras formas normales (3NF)**. Este enfoque garantiza la integridad de los datos, minimiza la redundancia y asegura que la estructura sea flexible y escalable para futuras mejoras.

##  Instalación y Puesta en Marcha

Sigue estos pasos para instalar y ejecutar el proyecto en un entorno de desarrollo local:

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/tu-usuario/sistema-gestion-biblioteca.git
    cd nombre-del-repositorio
    ```

2.  **Configurar la Base de Datos:**
    *   Crea una nueva base de datos en tu gestor (por ejemplo, usando `phpMyAdmin`).
    *   Importa el archivo `database.sql` (no olvides crear y añadir este archivo a tu repositorio) para generar la estructura de tablas y los datos iniciales.
    *   Actualiza el archivo `config/database.php` con tus credenciales de conexión (host, usuario, contraseña y nombre de la base de datos).

3.  **Ejecutar el Proyecto:**
    *   Asegúrate de tener un servidor local como XAMPP, WAMP o MAMP en funcionamiento.
    *   Copia la carpeta del proyecto en el directorio raíz de tu servidor (`htdocs` en XAMPP o `www` en WAMP/MAMP).
    *   Abre tu navegador web y accede a la URL correspondiente, por ejemplo: `http://localhost/biblioteca_web`.

---
