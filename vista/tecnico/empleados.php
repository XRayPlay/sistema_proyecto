<div class="table-responsive">
    <table class="table table-hover" id="table_empleados">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Edad</th>
                <th scope="col">Cédula</th>
                <th scope="col">Cargo</th>
                <th scope="col">Avatar</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($empleados as $empleado) { 
                $id_user = $empleado['id_user'] - 1;
                $birthday = $empleado['birthday'];
                $calculo = new usuario();
                $edad = $calculo->calcularEdad($birthday);?>
                

                <tr id="empleado_<?php echo $id_user; ?>">
                    <th scope='row'><?php echo $id_user; ?></th>
                    <td><?php echo $empleado['name']; ?></td>
                    <td> <?php echo $edad; ?></td>
                    <td><?php echo $empleado['cedula']; ?></td>

                    <td><?php 
                    $idcargo= $empleado['id_cargo'];
                    $sql="SELECT name FROM cargo WHERE id_cargo=$idcargo"; 
                    $result=$conexion->query($sql);
                    foreach($result AS $row){
                        echo $row['name'];
                    }?></td>
                    <td>

                        <?php
                        $avatar = $empleado['avatar'];
                        if ($avatar == '') {
                            $avatar = 'assets/imgs/sin-foto.jpg';
                        } else {
                            $avatar = "acciones/fotos_empleados/" . $avatar;
                        }
                        ?>
                        <img class="rounded-circle" src="<?php echo $avatar; ?>" alt="<?php echo $empleado['name']; ?>" width="50" height="50">
                    </td>
                    <td>
                        <a title="Ver detalles del empleado" href="#" onclick="verDetallesEmpleado(<?php echo $empleado['id_user']; ?>)" class="btn btn-success">
                            <i class="bi bi-binoculars"></i>
                        </a>
                        <a title="Editar datos del empleado" href="#" onclick="editarEmpleado(<?php echo $empleado['id_user']; ?>)" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a title="Eliminar datos del empleado" href="#" onclick="eliminarEmpleado(<?php echo $empleado['id_user']; ?>, '<?php echo $empleado['avatar']; ?>')" class="btn btn-danger">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>