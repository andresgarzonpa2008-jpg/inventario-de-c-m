<?php
if (!empty($_POST["btnregistrar"])) {

    if (!empty($_POST["nombre"]) && !empty($_POST["apellido"]) && !empty($_POST["documento"]) && !empty($_POST["fecha"]) && !empty($_POST["correo"])) {

        $id = $_POST["id"];
        $nombre = $_POST["nombre"];
        $apellido = $_POST["apellido"];
        $documento = $_POST["documento"];
        $fecha = $_POST["fecha"];
        $correo = $_POST["correo"];
        $password = $_POST["password"];

        $sql = $conexion->query("UPDATE tb_persona SET nombre='$nombre', apellido='$apellido', documento='$documento', fecha_nac='$fecha', correo='$correo', password='$password' WHERE id=$id");

        if ($sql == 1) {
            header("location:index.php");
        } else {
            echo '<div class="alert alert-danger">Error al modificar Usuario</div>';
        }

    } else {
        echo '<div class="alert alert-warning">Campos vacíos</div>';
    }
}