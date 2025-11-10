<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

require_once "../php/permisos.php";
require_once "../php/clases.php";

// Solo Admin puede ver esta página (según solicitud)
if (!esAdmin()) {
    header("location: ../login.php?error=acceso_denegado");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/inicio_completo.css">
</head>
<body>

<?php 
$menu = 'perfil';
include('../page/header.php');
include('../page/menu.php');
?>

<main class="main-content container py-4">
    <div class="card">
        <div class="card-header">
            <h5>Mi Perfil</h5>
            <p class="text-muted">Actualiza tus datos personales</p>
        </div>
        <div class="card-body">
            <form id="perfilForm" enctype="multipart/form-data" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required minlength="3" maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" required minlength="3" maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula</label>
                            <input type="text" id="cedula" name="cedula" class="form-control" required pattern="[0-9]{7,8}">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control" required pattern="[0-9]{10,11}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="birthday" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" id="birthday" name="birthday" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <input type="text" id="address" name="address" class="form-control" required minlength="5" maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Avatar (opcional)</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cambiar contraseña (opcional)</label>
                            <input type="password" id="password" name="password" class="form-control" minlength="7" maxlength="15" placeholder="Nueva contraseña">
                            <input type="password" id="confirmar_password" name="confirmar_password" class="form-control mt-2" minlength="7" maxlength="15" placeholder="Confirmar nueva contraseña">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar cambios</button>
                    <span id="msgResult" class="ms-3"></span>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('perfilForm');
    const msg = document.getElementById('msgResult');

    // Cargar datos actuales
    fetch('../php/actualizar_mi_cuenta.php')
    .then(r => r.json())
    .then(data => {
        if (data.success && data.user) {
            const u = data.user;
            document.getElementById('nombre').value = u.name || '';
            document.getElementById('apellido').value = u.apellido || '';
            document.getElementById('email').value = u.email || '';
            document.getElementById('cedula').value = u.cedula || '';
            document.getElementById('telefono').value = u.telefono || '';
            document.getElementById('birthday').value = u.birthday || '';
            document.getElementById('address').value = u.address || '';
        } else {
            msg.innerText = data.message || 'No se pudo obtener los datos del perfil';
            msg.className = 'text-danger';
        }
    }).catch(err => { console.error(err); msg.innerText = 'Error al cargar datos'; msg.className = 'text-danger'; });

    form.addEventListener('submit', function(e){
        e.preventDefault();
        msg.innerText = '';
        const formData = new FormData(form);

        // Enviar por fetch
        fetch('../php/actualizar_mi_cuenta.php', {
            method: 'POST',
            body: formData
        }).then(r => r.json())
        .then(data => {
            if (data.success) {
                msg.innerText = data.message || 'Perfil actualizado';
                msg.className = 'text-success';
            } else {
                msg.innerText = data.message || 'Error al actualizar';
                msg.className = 'text-danger';
            }
        }).catch(err => { console.error(err); msg.innerText = 'Error al enviar datos'; msg.className = 'text-danger'; });
    });
});
</script>
    <?php include_once('../page/footer.php'); ?>
</body>
</html>