<?php
// Archivo: index.php
require_once "controller/UsuarioController.php";

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
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
        $roles_validos = ['usuario', 'cliente', 'gerente'];

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

            // Si coincide, guardamos en sesión y renovamos el identificador.
            session_regenerate_id(true);
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
                    header("Location: index.php?action=cliente");
                    break;
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
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header("Location: index.php?action=login");
    exit();
}

if (isset($_GET["action"]) && $_GET["action"] === "dashboard_data" && isset($_SESSION["user"])) {
    require_once "config/conexion.php";
    header('Content-Type: application/json; charset=utf-8');
    try {
        $db = (new Conexion())->conn;
        $productStats = $db->query('SELECT COUNT(*) AS products, COALESCE(SUM(PRO_stock_actual), 0) AS units, COALESCE(SUM(PRO_stock_actual <= PRO_stock_minimo), 0) AS low FROM productos')->fetch(PDO::FETCH_ASSOC);
        $data = [
            'todaySales' => 0,
            'weekSales' => 0,
            'monthSales' => 0,
            'pending' => 0,
            'products' => (int) ($productStats['products'] ?? 0),
            'stockUnits' => (int) ($productStats['units'] ?? 0),
            'low' => (int) ($productStats['low'] ?? 0),
            'weekly' => array_fill(0, 7, 0),
            'monthly' => array_fill(0, 6, 0),
        ];
        $hasSales = (bool) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'ventas'")->fetchColumn();
        if ($hasSales) {
            $salesSummary = $db->query("SELECT COALESCE(SUM(CASE WHEN DATE(fecha_venta)=CURRENT_DATE() AND estado <> 'Cancelada' THEN total ELSE 0 END),0) AS today_sales, COALESCE(SUM(CASE WHEN YEARWEEK(fecha_venta, 1)=YEARWEEK(CURRENT_DATE(), 1) AND estado <> 'Cancelada' THEN total ELSE 0 END),0) AS week_sales, COALESCE(SUM(CASE WHEN MONTH(fecha_venta)=MONTH(CURRENT_DATE()) AND YEAR(fecha_venta)=YEAR(CURRENT_DATE()) AND estado <> 'Cancelada' THEN total ELSE 0 END),0) AS month_sales FROM ventas")->fetch(PDO::FETCH_ASSOC);
            $data['todaySales'] = (float) ($salesSummary['today_sales'] ?? 0);
            $data['weekSales'] = (float) ($salesSummary['week_sales'] ?? 0);
            $data['monthSales'] = (float) ($salesSummary['month_sales'] ?? 0);
            $data['pending'] = (int) $db->query("SELECT COALESCE(SUM(estado='Pendiente'),0) FROM ventas")->fetchColumn();
            $stmt = $db->query("SELECT WEEKDAY(fecha_venta) AS day_index, SUM(total) AS amount FROM ventas WHERE fecha_venta >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY) AND estado <> 'Cancelada' GROUP BY WEEKDAY(fecha_venta)");
            foreach ($stmt as $row) { $index = (int) $row['day_index']; if ($index >= 0 && $index < 7) $data['weekly'][$index] = (float) $row['amount']; }
            $stmt = $db->query("SELECT PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM CURRENT_DATE()), EXTRACT(YEAR_MONTH FROM fecha_venta)) AS month_index, SUM(total) AS amount FROM ventas WHERE fecha_venta >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 MONTH) AND estado <> 'Cancelada' GROUP BY month_index");
            foreach ($stmt as $row) { $index = (int) $row['month_index']; if ($index >= 0 && $index < 6) $data['monthly'][5 - $index] = (float) $row['amount']; }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } catch (Throwable $exception) {
        http_response_code(500);
        echo json_encode(['error' => 'No fue posible actualizar los indicadores.']);
    }
    exit();
}

// 4. Enrutamiento de Vistas (Usuarios autenticados)
if (isset($_SESSION["user"])) {
    require_once "config/conexion.php";
    $action = $_GET["action"] ?? 'usuario';
    $section = $_GET["section"] ?? 'home';
    $rol = $_SESSION["rol"] ?? 'cliente';
    
    if ($action === "admin" && $rol === "admin") {
        require_once "view/gerente_sbadm.php";
    } elseif ($action === "cliente" && ($rol === "cliente" || $rol === "admin")) {
        require_once "view/clientes_dashboard.php";
    } elseif ($action === "gerente" && ($rol === "gerente" || $rol === "admin")) {
        require_once "view/gerente_sbadm.php";
    } elseif ($action === "proveedor" && ($rol === "proveedor" || $rol === "admin")) {
        require_once "view/proveedores_dashboard.php";
    } elseif ($action === "inventario" && in_array($rol, ["inventario", "gerente", "admin"], true)) {
        require_once "view/inventario.php";
    } elseif ($action === "reportes" && in_array($rol, ["gerente", "admin"], true)) {
        require_once "view/reportes.php";
    } elseif ($action === "usuario") {
        if ($section === "perfil") {
            require_once "view/perfil.php";
        } elseif ($section === "usuarios" && $rol === "admin") {
            require_once "view/usuarios.php";
        } elseif ($section === "editar_usuario" && $rol === "admin") {
            require_once "view/editar_usuario.php";
        } elseif ($rol === "cliente") {
            require_once "view/clientes_dashboard.php";
        } elseif ($rol === "proveedor") {
            require_once "view/proveedores_dashboard.php";
        } elseif ($rol === "gerente" || $rol === "admin" || $rol === "inventario") {
            require_once "view/gerente_sbadm.php";
        } else {
            require_once "view/clientes_dashboard.php";
        }
    } else {
            require_once "view/gerente_sbadm.php";
    }
} else {
    $action = $_GET["action"] ?? 'login';

    if ($action === "register") {
        require_once "view/register.php";
    } else {
        require_once "view/login.php";
    }
}