<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>crud php y MysQl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/94c34a3a8a.js" crossorigin="anonymous"></script>

</head>

<body>
    <script>
        function eliminar() {
            var respuesta = confirm("¿Estas seguro de quere eliminar el registro?");
            return respuesta
        }
    </script>
    <h1 class="text-center p-3"> modulo de registro de usuarios</h1>
    <?php
    include "model/conexion.php";
    include "controlador/eliminar_persona.php";
    ?>
    <div class="contairner-fluid row">

    <form class="col-4" p-3 method="POST">
        <h3 class="text-center text-secondary">Registro de personas</h3>
        <?php
        include "controlador/registro_persona.php";
        ?>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Nombre:</label>
            <input type="text" class="form-control" name="nombre">
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Apellido:</label>
            <input type="text" class="form-control" name="apellido">
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Documento:</label>
            <input type="text" class="form-control" name="documento">
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Fecha de nacimineto:</label>
            <input type="date" class="form-control" name="fecha">
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Correo:</label>
            <input type="text" class="form-control" name="correo">
        </div>

        <button type="submit" class="btn btn-primary" name="btnregistrar" value="ok">Registrar</button>
    </form>
    <div class="col-8 p-4">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Fecha de nacimiento</th>
                    <th scope="col">Correo</th>
                     <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    include "model/conexion.php";
                    $sql = $conexion->query("select * from tb_persona");
                    while ($datos = $sql->fetch_object()) {?>
                <tr>
                   
                    <td><?= $datos->id ?></td>
                    <td><?= $datos->nombre ?></td>
                    <td><?= $datos->apellido ?></td>
                    <td><?= $datos->documento ?></td>
                    <td><?= $datos->fecha_nac ?></td>
                    <td><?= $datos->correo ?></td>
                    <td>
                        <a href="modificar_persona.php?id=<?= $datos->id ?>" class="btn btn-small btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a onclick ="return eliminar()" href="index.php?id=<?= $datos->id ?>" class="btn btn-small btn-danger"><i class="fa-solid fa-trash"></i></a>
                </tr>
                <?php }
                ?>
            </tbody>
        </table>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
</body>

</html>