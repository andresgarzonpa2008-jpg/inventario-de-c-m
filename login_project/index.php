<?php
// Archivo: index.php
require_once "controller/UsuarioController.php";

session_start();
$controller = new UsuarioController();

$error = "";
$mensaje = "";

// 1. Mensajes informativos por URL
if (isset($_GET["mensaje"]) && $_GET["mensaje"] === "registrado") {
    $mensaje = "¡Usuario registrado con éxito! Ya puedes iniciar sesión.";
}

// 2. Procesar peticiones POST (Registro o Login)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    
    // ACCIÓN DE REGISTRO
    if ($_POST["action"] === "register") {
        $nombre = trim($_POST["nombre"] ?? '');
        $apellido = trim($_POST["apellido"] ?? '');
        $documento_id = trim($_POST["documento_id"] ?? '');
        $fecha_nacimiento = trim($_POST["fecha_nacimiento"] ?? '');
        $correo = trim($_POST["correo"] ?? '');
        $username = trim($_POST["username"] ?? '');
        $password = trim($_POST["password"] ?? '');
        $rol = trim($_POST["rol"] ?? 'usuario'); // Obtiene el rol del formulario

        if (!empty($nombre) && !empty($apellido) && !empty($documento_id) && !empty($fecha_nacimiento) && !empty($correo) && !empty($username) && !empty($password)) {
            if ($controller->registrar($nombre, $apellido, $documento_id, $fecha_nacimiento, $correo, $username, $password, $rol)) {
                header("Location: index.php?action=login&mensaje=registrado");
                exit();
            } else {
                $error = "No se pudo registrar. El usuario '" . htmlspecialchars($username) . "' ya existe o hubo un error en la base de datos.";
                require_once "view/register.php";
                exit();
            }
        } else {
            $error = "Por favor completa todos los campos.";
            require_once "view/register.php";
            exit();
        }
    }

    // ACCIÓN DE LOGIN
    if ($_POST["action"] === "login") {
        $username = trim($_POST["username"] ?? '');
        $password = trim($_POST["password"] ?? '');

        $user = $controller->login($username, $password);

        if ($user) {
            $_SESSION["user"] = $user;
            $_SESSION["rol"] = $user['rol'] ?? 'usuario'; // Asigna rol por defecto si no existe
            
            // Redirige según el rol del usuario
            $rol = $user['rol'] ?? 'usuario';
            if ($rol === 'admin') {
                header("Location: index.php?action=admin");
            } elseif ($rol === 'gerente') {
                header("Location: index.php?action=gerente");
            } else {
                header("Location: index.php?action=usuario&section=home");
            }
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
            require_once "view/login.php";
            exit();
        }
    }
}

// 3. Cierre de sesión
if (isset($_GET["action"]) && $_GET["action"] === "logout") {
    session_destroy();
    header("Location: index.php?action=login");
    exit();
}

// 4. Enrutamiento de Vistas
if (isset($_SESSION["user"])) {
    require_once "config/conexion.php";
    $action = $_GET["action"] ?? 'usuario';
    $section = $_GET["section"] ?? 'home';
    $rol = $_SESSION["rol"] ?? 'usuario';
    
    // Enrutamiento por rol
    if ($action === "admin" && $rol === "admin") {
        require_once "view/admin.php";
    } elseif ($action === "gerente" && $rol === "gerente") {
        require_once "view/gerente.php";
    } else if ($action === "usuario") {
        // Mostrar dashboard con diferentes secciones
        if ($section === "perfil") {
            require_once "view/perfil.php";
        } elseif ($section === "usuarios" && $rol === "admin") {
            require_once "view/usuarios.php";
        } elseif ($section === "editar_usuario" && $rol === "admin") {
            require_once "view/editar_usuario.php";
        } else {
            // Vista por defecto para usuarios (home)
            require_once "view/dashboard.php";
        }
    } else {
        // Vista por defecto para usuarios
        require_once "view/dashboard.php";
    }
} else {
    $action = $_GET["action"] ?? 'login';

    if ($action === "register") {
        require_once "view/register.php";
    } else {
        require_once "view/login.php";
    }
}
?>
                ?>
            </tbody>
        </table>

    </div>
?>