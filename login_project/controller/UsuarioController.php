<?php
// Carpeta: controller
// Archivo: UsuarioController.php

require_once "model/Usuario.php";

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    // Método para manejar el login
    public function login($username, $password) {
        return $this->usuarioModel->login($username, $password);
    }

    // Método para registrar un usuario con contraseña encriptada
    public function registrar($nombre, $apellido, $documento_id, $fecha_nacimiento, $correo, $username, $password, $rol = 'usuario') {
        return $this->usuarioModel->registrar($nombre, $apellido, $documento_id, $fecha_nacimiento, $correo, $username, $password, $rol);
    }
}
?>