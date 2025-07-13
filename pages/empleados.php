<div class="table-responsive">
    <table class="table table-hover" id="table_empleados">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Edad</th>
                <th scope="col">Cedula</th>
                <th scope="col">Cargo</th>
                <th scope="col">Avatar</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($empleados as $empleado) { ?>
                <tr id="empleado_<?php echo $empleado['id_tecnico']; ?>">
                    <th scope='row'><?php echo $empleado['id_tecnico']; ?></th>
                    <td><?php echo $empleado['nombre']; ?></td>

                    <td> <?php
                    $birthday = $empleado['fecha_nacimiento'];
                    $calc = new usuario();
                    $calculo = $calc->calcularEdad($birthday);
                    echo $calculo; ?></td>

                    <td><?php echo $empleado['cedula']; ?></td>
                    <td><?php echo $empleado['descripcion']; ?></td>
                    <td>
                        <?php
                        $avatar = $empleado['avatar'];
                        if ($avatar == '') {
                            $avatar = '../resources/image/usuario.png';
                        
                        }
                        ?>
                        <img class="rounded-circle" src="<?php echo $avatar; ?>" alt="<?php echo $empleado['nombre']; ?>" width="50" height="50">
                    </td>
                    <td>
                        <a title="Ver detalles del empleado" href="#" onclick="verDetallesEmpleado(<?php echo $empleado['id_tecnico']; ?>)" class="btn btn-success">
                            <i class="bi bi-binoculars"></i>
                        </a>
                        <a title="Editar datos del empleado" href="#" onclick="editarEmpleado(<?php echo $empleado['id_tecnico']; ?>)" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a title="Eliminar datos del empleado" href="#" onclick="eliminarEmpleado(<?php echo $empleado['id_tecnico']; ?>, '<?php echo $empleado['avatar']; ?>')" class="btn btn-danger">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>