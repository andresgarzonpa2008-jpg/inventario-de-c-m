<?php
// Procesar eliminación de usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "eliminar_usuario") {
    $id_usuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : 0;
    
    if ($id_usuario > 0) {
        try {
            $db = (new Conexion())->conn;
            
            // Verificar que no se pueda eliminar a sí mismo
            if ($id_usuario === $_SESSION["user"]["id"]) {
                $error_eliminar = "No puedes eliminar tu propia cuenta desde aquí.";
            } else {
                $query = "DELETE FROM usuarios WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":id", $id_usuario, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $mensaje_exito = "Usuario eliminado correctamente.";
                } else {
                    $error_eliminar = "Error al eliminar el usuario.";
                }
            }
        } catch (Exception $e) {
            $error_eliminar = "Error: " . $e->getMessage();
        }
    }
}

// Obtener lista de usuarios (solo para administradores)
$usuarios = [];
if ($_SESSION["rol"] === "admin") {
    try {
        $db = (new Conexion())->conn;
        $query = "SELECT id, nombre, apellido, username, correo, rol, fecha_nacimiento FROM usuarios ORDER BY id DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_lista = "Error al cargar usuarios: " . $e->getMessage();
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Usuarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Administrar usuarios del sistema</li>
    </ol>

    <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_eliminar)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_eliminar; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users"></i>
            Lista de Usuarios
            <a href="index.php?action=usuario&section=nuevo_usuario" class="btn btn-sm btn-primary float-end">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Fecha Nacimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay usuarios registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($usuario["id"]); ?></strong></td>
                                    <td><?php echo htmlspecialchars($usuario["nombre"] . " " . $usuario["apellido"]); ?></td>
                                    <td><?php echo htmlspecialchars($usuario["username"]); ?></td>
                                    <td><?php echo htmlspecialchars($usuario["correo"]); ?></td>
                                    <td>
                                        <?php 
                                        $rol = $usuario["rol"];
                                        if ($rol === "admin") {
                                            echo '<span class="badge bg-danger">Administrador</span>';
                                        } elseif ($rol === "gerente") {
                                            echo '<span class="badge bg-warning">Gerente</span>';
                                        } else {
                                            echo '<span class="badge bg-info">Usuario</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario["fecha_nacimiento"] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="index.php?action=usuario&section=editar_usuario&id=<?php echo $usuario["id"]; ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <?php if ($usuario["id"] !== $_SESSION["user"]["id"]): ?>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#modalEliminar<?php echo $usuario["id"]; ?>" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>

                                            <!-- Modal de Confirmación -->
                                            <div class="modal fade" id="modalEliminar<?php echo $usuario["id"]; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-sm">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de que deseas eliminar al usuario?</p>
                                                            <p><strong><?php echo htmlspecialchars($usuario["username"]); ?></strong></p>
                                                            <p class="text-muted"><small>Esta acción no se puede deshacer.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                            <form method="POST" action="index.php?action=usuario&section=usuarios" style="display: inline;">
                                                                <input type="hidden" name="action" value="eliminar_usuario">
                                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario["id"]; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i> Sí, Eliminar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary" title="No se puede eliminar tu propia cuenta">Tu Cuenta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <div class="card-title">Total de Usuarios</div>
                    <div class="fs-3 fw-bold"><?php echo count($usuarios); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <div class="card-title">Administradores</div>
                    <div class="fs-3 fw-bold">
                        <?php echo count(array_filter($usuarios, fn($u) => $u["rol"] === "admin")); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <div class="card-title">Gerentes</div>
                    <div class="fs-3 fw-bold">
                        <?php echo count(array_filter($usuarios, fn($u) => $u["rol"] === "gerente")); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <div class="card-title">Usuarios Estándar</div>
                    <div class="fs-3 fw-bold">
                        <?php echo count(array_filter($usuarios, fn($u) => $u["rol"] === "usuario")); ?>
                    </div>
                </div>
            </div>
        </div>
</div>
