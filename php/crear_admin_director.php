<?php
// Script para crear solo admin y director
echo "<h2>Creando Usuarios Admin y Director</h2>";

try {
    // Conectar a la base de datos
    $conexion = mysqli_connect("localhost", "root", "", "sistema_proyecto");
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    echo "✅ Conexión exitosa a la base de datos<br><br>";
    
    // Verificar si la tabla user existe
    $tabla_existe = mysqli_query($conexion, "SHOW TABLES LIKE 'user'");
    if (mysqli_num_rows($tabla_existe) == 0) {
        throw new Exception("La tabla 'user' no existe.");
    }
    
    echo "✅ Tabla 'user' encontrada<br><br>";
    
    // Insertar Administrador (Rol 1)
    $query_admin = "INSERT INTO user (name, username, cedula, correo, pass, id_rol) 
                    VALUES ('Administrador del Sistema', 'admin', '12345678', 'admin@sistema.com', 'admin123', 1)";
    
    if (mysqli_query($conexion, $query_admin)) {
        echo "Usuario Administrador creado exitosamente<br>";
        echo "   - Username: admin<br>";
        echo "   - Contraseña: admin123<br>";
        echo "   - Cédula: 12345678<br>";
        echo "   - Rol: Administrador (1)<br><br>";
    } else {
        echo "Error al crear Administrador: " . mysqli_error($conexion) . "<br><br>";
    }
    
    // Insertar Director (Rol 2)
    $query_director = "INSERT INTO user (name, username, cedula, correo, pass, id_rol) 
                       VALUES ('Director General', 'director', '87654321', 'director@sistema.com', 'director123', 2)";
    
    if (mysqli_query($conexion, $query_director)) {
        echo "✅ Usuario Director creado exitosamente<br>";
        echo "   - Username: director<br>";
        echo "   - Contraseña: director123<br>";
        echo "   - Cédula: 87654321<br>";
        echo "   - Rol: Director (2)<br><br>";
    } else {
        echo "❌ Error al crear Director: " . mysqli_error($conexion) . "<br><br>";
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "✅ <strong>Usuarios creados exitosamente!</strong><br>";
    echo "Ahora puedes hacer login con admin/admin123 o director/director123";
    echo "</div>";
    
    echo "<a href='../login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>";
    echo "← Ir al Login";
    echo "</a>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
} finally {
    if (isset($conexion)) {
        mysqli_close($conexion);
    }
}
?>



