<?php
// Procesar actualización de perfil
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "actualizar_perfil") {
    $nombre = trim($_POST["nombre"] ?? '');
    $apellido = trim($_POST["apellido"] ?? '');
    $documento_id = trim($_POST["documento_id"] ?? '');
    $fecha_nacimiento = trim($_POST["fecha_nacimiento"] ?? '');
    $correo = trim($_POST["correo"] ?? '');
    $id_usuario = $_SESSION["user"]["id"];

    if (!empty($nombre) && !empty($apellido) && !empty($documento_id) && !empty($fecha_nacimiento) && !empty($correo)) {
        try {
            $db = (new Conexion())->conn;
            $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, documento_id = :documento_id, 
                     fecha_nacimiento = :fecha_nacimiento, correo = :correo WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":documento_id", $documento_id);
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":id", $id_usuario);
            
            if ($stmt->execute()) {
                // Actualizar la sesión
                $_SESSION["user"]["nombre"] = $nombre;
                $_SESSION["user"]["apellido"] = $apellido;
                $_SESSION["user"]["documento_id"] = $documento_id;
                $_SESSION["user"]["fecha_nacimiento"] = $fecha_nacimiento;
                $_SESSION["user"]["correo"] = $correo;
                
                $mensaje_exito = "Perfil actualizado correctamente.";
            } else {
                $error_perfil = "Error al actualizar el perfil.";
            }
        } catch (Exception $e) {
            $error_perfil = "Error: " . $e->getMessage();
        }
    } else {
        $error_perfil = "Por favor completa todos los campos.";
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Mi Perfil</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Gestionar información personal</li>
    </ol>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    Información Personal
                </div>
                <div class="card-body">
                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_perfil)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_perfil; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?action=usuario&section=perfil">
                        <input type="hidden" name="action" value="actualizar_perfil">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       value="<?php echo htmlspecialchars($_SESSION["user"]["apellido"] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="documento_id" class="form-label">Documento de Identidad</label>
                                <input type="text" class="form-control" id="documento_id" name="documento_id" 
                                       value="<?php echo htmlspecialchars($_SESSION["user"]["documento_id"] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                       value="<?php echo htmlspecialchars($_SESSION["user"]["fecha_nacimiento"] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" 
                                   value="<?php echo htmlspecialchars($_SESSION["user"]["correo"] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario (No se puede cambiar)</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?php echo htmlspecialchars($_SESSION["user"]["username"] ?? ''); ?>" disabled>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="index.php?action=usuario&section=home" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    Información de Cuenta
                </div>
                <div class="card-body">
                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION["user"]["username"] ?? ''); ?></p>
                    <p><strong>Rol:</strong> 
                        <?php 
                        $rol = $_SESSION["rol"] ?? 'usuario';
                        $rol_display = match($rol) {
                            'admin' => '<span class="badge bg-danger">Administrador</span>',
                            'gerente' => '<span class="badge bg-warning">Gerente</span>',
                            default => '<span class="badge bg-info">Usuario</span>'
                        };
                        echo $rol_display;
                        ?>
                    </p>
                    <p><strong>Fecha de Registro:</strong> <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock"></i>
                    Seguridad
                </div>
                <div class="card-body">
                    <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar contraseña -->
<div class="modal fade" id="cambiarPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cambiarPasswordForm">
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="password_actual" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_nueva" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmar" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar Contraseña</button>
            </div>
        </div>
    </div>
</div>
