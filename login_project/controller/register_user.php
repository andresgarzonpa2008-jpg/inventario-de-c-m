<?php
// Archivo: register_user.php

require_once "controller/UsuarioController.php";

$controller = new UsuarioController();

// Datos del nuevo usuario
$nombre = "nombre_usuario";
$apellido = "apellido_usuario";
$docuemnto_id = "123456789";
$fecha_nacimiento = "1990-01-01";
$correo = "correo@example.com";
$username = "nuevo_usuario";
$password = "clave_secreta";
$rol = "usuario";

// Registrar el usuario
if ($controller->registrar($nombre, $apellido, $docuemnto_id, $fecha_nacimiento, $correo, $username, $password, $rol)) {
    echo "Usuario registrado correctamente.";
} else {
    echo "Error al registrar el usuario.";
}
?>