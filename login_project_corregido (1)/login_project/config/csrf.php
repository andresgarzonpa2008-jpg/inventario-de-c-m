<?php
// config/csrf.php
// Helper mínimo de protección CSRF basado en token de sesión.
// Requiere que session_start() ya se haya ejecutado antes de incluir este archivo.

/**
 * Devuelve el token CSRF actual, generándolo si no existe todavía.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Imprime el <input hidden> listo para pegar dentro de un <form>.
 */
function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verifica el token recibido por POST contra el de la sesión.
 * Si no coincide, corta la ejecución con 403.
 */
function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $valid = !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);

    if (!$valid) {
        http_response_code(403);
        die('Solicitud rechazada: token de seguridad inválido o expirado. Recarga la página e inténtalo de nuevo.');
    }
}
