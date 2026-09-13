<?php
// config/require_auth.php
//
// Segunda capa de protección (defensa en profundidad).
// La primera capa es el enrutador de index.php + el .htaccess que bloquea
// el acceso HTTP directo a /view, /model, /controller y /config.
// Esta guarda existe por si esa primera capa llegara a fallar (mala
// configuración del servidor, .htaccess deshabilitado, migración a un
// hosting que ignora .htaccess, etc.): cada vista sensible la incluye y
// verifica sesión + rol por sí misma, sin depender de que la haya
// llamado el router.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Varias vistas llaman a csrf_verify() asumiendo que index.php ya incluyó
// config/csrf.php antes de hacer el require_once de la vista. Lo incluimos
// aquí también para que esa función siempre exista, sin depender de eso.
require_once __DIR__ . '/csrf.php';

/**
 * Corta la ejecución si no hay una sesión de usuario válida.
 */
function require_login(): void
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        die('<h1>401 - Acceso no autorizado</h1><p>Debes iniciar sesión para ver esta página. '
            . '<a href="../index.php?action=login">Ir al login</a></p>');
    }
}

/**
 * Corta la ejecución si el usuario no tiene uno de los roles permitidos.
 * Siempre exige login primero.
 *
 * @param string[] $rolesPermitidos p.ej. ['admin'], ['gerente','admin']
 */
function require_role(array $rolesPermitidos): void
{
    require_login();

    $rolActual = $_SESSION['rol'] ?? '';
    if (!in_array($rolActual, $rolesPermitidos, true)) {
        http_response_code(403);
        die('<h1>403 - No autorizado</h1><p>Tu rol no tiene permiso para ver esta sección.</p>');
    }
}
