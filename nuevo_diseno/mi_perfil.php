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
                            <label class="form-label">Cambiar contraseña (opcional)</label>
                            <input type="password" id="password" name="password" class="form-control" minlength="7" maxlength="15" placeholder="Nueva contraseña">
                            <input type="password" id="confirmar_password" name="confirmar_password" class="form-control mt-2" minlength="7" maxlength="15" placeholder="Confirmar nueva contraseña">
                        </div>
                    </div>
                    <div class="col-md-6">
                        
                        <div class="mb-3 d-none">
                            <input type="date" id="birthday" name="birthday" class="form-control" value="<?php echo (date('Y')-20) . '-01-01'; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="piso" class="form-label">Piso</label>
                            <select class="form-select" id="piso" name="piso" required>
                                <option value="">Seleccionar piso</option>
                                <?php
                                try {
                                    $conexion = new conectar();
                                    $conexion = $conexion->conexion();
                                    $query = "SELECT id_floors, name FROM floors ORDER BY id_floors ASC";
                                    $result = $conexion->query($query);
                                    
                                    if ($result) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($row['id_floors']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                        }
                                        $result->free();
                                    }
                                    $conexion->close();
                                } catch (Exception $e) {
                                    error_log("Error al obtener pisos: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="code_phone" class="form-label">Código de Teléfono</label>
                            <select class="form-select" id="code_phone" name="code_phone" required>
                                <option value="">Seleccionar código</option>
                                <option value="412">412</option>
                                <option value="414">414</option>
                                <option value="416">416</option>
                                <option value="422">422</option>
                                <option value="424">424</option>
                                <option value="426">426</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control" required pattern="[0-9]{7}">
                        </div>
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula</label>
                            <input type="text" id="cedula" name="cedula" class="form-control" required pattern="[0-9]{7,8}">
                        </div>
                        <div class="mb-3 d-none">
                            <label for="avatar" class="form-label">Avatar (opcional)</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*" class="form-control">
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

<!-- Modal de confirmación personalizada -->
<div class="modal fade" id="modalConfirmacionPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionPerfilTitulo">Confirmar acción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="confirmacionPerfilMensaje" class="mb-0">¿Deseas continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnPerfilCancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnPerfilConfirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../public/js/login.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.addPasswordToggle) {
            try {
                addPasswordToggle('#password');
                addPasswordToggle('#confirmar_password');
            } catch (e) {
                console.warn('No se pudo inicializar toggle de contraseña en mi_perfil:', e);
            }
        }
    });
</script>
<script>
function mostrarModalConfirmacionPerfil({ titulo, mensaje, textoConfirmar = 'Confirmar', textoCancelar = 'Cancelar' }) {
    return new Promise((resolve) => {
        const modalEl = document.getElementById('modalConfirmacionPerfil');
        const tituloEl = document.getElementById('confirmacionPerfilTitulo');
        const mensajeEl = document.getElementById('confirmacionPerfilMensaje');
        const btnConfirmar = document.getElementById('btnPerfilConfirmar');
        const btnCancelar = document.getElementById('btnPerfilCancelar');

        tituloEl.textContent = titulo || 'Confirmar acción';
        mensajeEl.textContent = mensaje || '¿Deseas continuar?';
        btnConfirmar.textContent = textoConfirmar;
        btnCancelar.textContent = textoCancelar;

        const confirmModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const handleConfirm = () => {
            cleanup();
            confirmModal.hide();
            resolve(true);
        };

        const handleCancel = () => {
            cleanup();
            confirmModal.hide();
            resolve(false);
        };

        const handleHidden = () => {
            cleanup();
            resolve(false);
        };

        function cleanup() {
            btnConfirmar.removeEventListener('click', handleConfirm);
            btnCancelar.removeEventListener('click', handleCancel);
            modalEl.removeEventListener('hidden.bs.modal', handleHidden);
        }

        btnConfirmar.addEventListener('click', handleConfirm, { once: true });
        btnCancelar.addEventListener('click', handleCancel, { once: true });
        modalEl.addEventListener('hidden.bs.modal', handleHidden, { once: true });

        confirmModal.show();
    });
}

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
            document.getElementById('code_phone').value = u.code_phone || '';
            document.getElementById('telefono').value = u.telefono || '';
            document.getElementById('birthday').value = u.birthday || '';
            // Set the floor value if it exists in the user data
            if (u.id_piso) {
                document.getElementById('piso').value = u.id_piso;
            }
        } else {
            msg.innerText = data.message || 'No se pudo obtener los datos del perfil';
            msg.className = 'text-danger';
        }
    }).catch(err => { console.error(err); msg.innerText = 'Error al cargar datos'; msg.className = 'text-danger'; });

    form.addEventListener('submit', async function(e){
        e.preventDefault();
        msg.innerText = '';

        const confirmado = await mostrarModalConfirmacionPerfil({
            titulo: 'Confirmar actualización',
            mensaje: '¿Deseas actualizar tu perfil con la información ingresada?',
            textoConfirmar: 'Sí, actualizar',
            textoCancelar: 'No, cancelar'
        });

        if (!confirmado) {
            return;
        }

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