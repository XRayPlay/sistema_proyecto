<?php include '../page/head.php';
// AGREGAR NUEVOS INCLUDE AQUI, ANTES DE CUALQUIER FUNCION
include '../php/clases.php';
$conn = new conectar();
$conexion = $conn->conexion();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['usuario']['id_user'];
$person_id = $_SESSION['usuario']['id_person'];

// Obtener datos del usuario
$sql = "SELECT p.name, p.apellido, p.cedula, p.email, CONCAT(p.phone_code, '-', p.phone) as telefono, u.username, u.avatar
        FROM user u
        JOIN person p ON u.id_person = p.id_person
        WHERE u.id_user = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Manejar actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $phone_code = $_POST['phone_code'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];

    // Actualizar person
    $sql_person = "UPDATE person SET name=?, apellido=?, email=?, phone_code=?, phone=? WHERE id_person=?";
    $stmt_person = mysqli_prepare($conexion, $sql_person);
    mysqli_stmt_bind_param($stmt_person, 'sssssi', $name, $apellido, $email, $phone_code, $phone, $person_id);
    mysqli_stmt_execute($stmt_person);
    mysqli_stmt_close($stmt_person);

    // Actualizar user
    $sql_user = "UPDATE user SET username=? WHERE id_user=?";
    $stmt_user = mysqli_prepare($conexion, $sql_user);
    mysqli_stmt_bind_param($stmt_user, 'si', $username, $user_id);
    mysqli_stmt_execute($stmt_user);
    mysqli_stmt_close($stmt_user);

    // Manejar avatar si se sube
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar_name = time() . '_' . $_FILES['avatar']['name'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], '../resources/image/' . $avatar_name);
        $sql_avatar = "UPDATE user SET avatar=? WHERE id_user=?";
        $stmt_avatar = mysqli_prepare($conexion, $sql_avatar);
        mysqli_stmt_bind_param($stmt_avatar, 'si', $avatar_name, $user_id);
        mysqli_stmt_execute($stmt_avatar);
        mysqli_stmt_close($stmt_avatar);
    }

    header("Location: perfil.php?updated=1");
    exit;
}
?>


<!-------	AGREGAR NUEVOS ESTILOS CSS AQUI  ----------->
<?php include '../page/menu.php' ?>

<h1>Mi Perfil</h1>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">Perfil actualizado correctamente.</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="profile-form">
    <input type="hidden" name="update_profile" value="1">
    
    <div class="mb-3">
        <label>Nombre</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required class="form-control">
    </div>
    <div class="mb-3">
        <label>Apellido</label>
        <input type="text" name="apellido" value="<?php echo htmlspecialchars($user_data['apellido']); ?>" required class="form-control">
    </div>
    <div class="mb-3">
        <label>Cédula</label>
        <input type="text" name="cedula" value="<?php echo htmlspecialchars($user_data['cedula']); ?>" readonly class="form-control">
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required class="form-control">
    </div>
    <div class="mb-3">
        <label>Código Teléfono</label>
        <select name="phone_code" required class="form-control">
            <option value="412" <?php if (strpos($user_data['telefono'], '412') === 0) echo 'selected'; ?>>0412</option>
            <option value="414" <?php if (strpos($user_data['telefono'], '414') === 0) echo 'selected'; ?>>0414</option>
            <option value="416" <?php if (strpos($user_data['telefono'], '416') === 0) echo 'selected'; ?>>0416</option>
            <option value="424" <?php if (strpos($user_data['telefono'], '424') === 0) echo 'selected'; ?>>0424</option>
            <option value="426" <?php if (strpos($user_data['telefono'], '426') === 0) echo 'selected'; ?>>0426</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Teléfono</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars(substr($user_data['telefono'], strpos($user_data['telefono'], '-') + 1)); ?>" required class="form-control">
    </div>
    <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required class="form-control">
    </div>
    <div class="mb-3">
        <label>Avatar</label>
        <input type="file" name="avatar" accept="image/*" class="form-control">
        <?php if ($user_data['avatar']): ?>
            <img src="../resources/image/<?php echo htmlspecialchars($user_data['avatar']); ?>" width="100" alt="Avatar actual">
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
</form>




<?php include '../page/footer.php' ?>

<!-- INSERTAR NUEVOS JS AQUI-->

<?php include '../page/end.php' ?>