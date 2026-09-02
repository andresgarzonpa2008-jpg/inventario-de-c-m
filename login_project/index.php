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
        $rol = trim($_POST["rol"] ?? 'cliente');
        $roles_validos = ['usuario', 'cliente', 'proveedor', 'inventario', 'gerente', 'admin'];

        if (!in_array($rol, $roles_validos, true)) {
            $error = "El rol seleccionado no es válido.";
            require_once "view/register.php";
            exit();
        }

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

    // ACCIÓN DE LOGIN CON VALIDACIÓN DE ROL EXACTO
    if ($_POST["action"] === "login") {
        $username = trim($_POST["username"] ?? '');
        $password = trim($_POST["password"] ?? '');
        $rol_seleccionado = trim($_POST["rol"] ?? '');

        // Obtener usuario desde el controlador (debe incluir el campo 'rol' de la BD)
        $user = $controller->login($username, $password);

        if ($user) {
            $rol_bd = strtolower(trim($user['rol'] ?? 'cliente'));
            $rol_sel = strtolower(trim($rol_seleccionado));

            // VERIFICACIÓN DE ROL: Si el rol seleccionado NO coincide con el de la Base de Datos
            if ($rol_sel !== $rol_bd) {
                $error = "Acceso denegado: Tu cuenta no tiene permisos para el rol de '" . htmlspecialchars($rol_seleccionado) . "'.";
                require_once "view/login.php";
                exit();
            }

            // Si coincide, guardamos en sesión
            $_SESSION["user"] = $user;
            $_SESSION["rol"] = $rol_bd;

            // Redirección dinámica según el rol validado
            switch ($rol_bd) {
                case 'admin':
                    header("Location: index.php?action=admin");
                    break;
                case 'gerente':
                    header("Location: index.php?action=gerente");
                    break;
                case 'proveedor':
                    header("Location: index.php?action=proveedor");
                    break;
                case 'inventario':
                    header("Location: index.php?action=inventario");
                    break;
                case 'cliente':
                default:
                    header("Location: index.php?action=usuario&section=home");
                    break;
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

// 4. Enrutamiento de Vistas (Usuarios autenticados)
if (isset($_SESSION["user"])) {
    require_once "config/conexion.php";
    $action = $_GET["action"] ?? 'usuario';
    $section = $_GET["section"] ?? 'home';
    $rol = $_SESSION["rol"] ?? 'cliente';
    
    if ($action === "admin" && $rol === "admin") {
        require_once "view/admin.php";
    } elseif ($action === "gerente" && ($rol === "gerente" || $rol === "admin")) {
        require_once "view/gerente.php";
    } elseif ($action === "proveedor" && ($rol === "proveedor" || $rol === "admin")) {
        require_once "view/proveedores.php";
    } elseif ($action === "inventario" && ($rol === "inventario" || $rol === "admin")) {
        require_once "view/dashboard.php";
    } elseif ($action === "usuario") {
        if ($section === "perfil") {
            require_once "view/perfil.php";
        } elseif ($section === "usuarios" && $rol === "admin") {
            require_once "view/usuarios.php";
        } elseif ($section === "editar_usuario" && $rol === "admin") {
            require_once "view/editar_usuario.php";
        } else {
            require_once "view/dashboard.php";
        }
    } else {
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