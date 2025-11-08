<?php
// Script corregido para restaurar usuarios administrador, director y analista
echo "<h2>🔧 Restaurando Usuarios del Sistema (Roles Corregidos)</h2>";

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
        throw new Exception("La tabla 'user' no existe. Primero debe crear la tabla.");
    }
    
    echo "✅ Tabla 'user' encontrada<br><br>";
    
    // Verificar estructura de la tabla
    $estructura = mysqli_query($conexion, "DESCRIBE user");
    $columnas_requeridas = ['id_user', 'name', 'username', 'cedula', 'correo', 'pass', 'id_rol'];
    $columnas_existentes = [];
    
    while ($row = mysqli_fetch_assoc($estructura)) {
        $columnas_existentes[] = $row['Field'];
    }
    
    $columnas_faltantes = array_diff($columnas_requeridas, $columnas_existentes);
    if (!empty($columnas_faltantes)) {
        echo "⚠️ Columnas faltantes: " . implode(', ', $columnas_faltantes) . "<br>";
        echo "Se intentará insertar con las columnas disponibles<br><br>";
    }
    
    echo "<h3>📋 Roles del Sistema:</h3>";
    echo "- <strong>Rol 1:</strong> Administrador (acceso completo)<br>";
    echo "- <strong>Rol 2:</strong> Director (acceso completo)<br>";
    echo "- <strong>Rol 3:</strong> Técnico (dashboard técnico)<br>";
    echo "- <strong>Rol 4:</strong> Analista (panel analista)<br>";
    echo "- <strong>Sin rol:</strong> Usuarios públicos (solo crear incidencias)<br><br>";
    
    // Preparar consulta de inserción
    $query_admin = "INSERT INTO user (name, username, cedula, correo, pass, id_rol) 
                    VALUES ('Administrador del Sistema', 'admin', '12345678', 'admin@sistema.com', 'admin123', 1)
                    ON DUPLICATE KEY UPDATE 
                    name = VALUES(name), 
                    pass = VALUES(pass)";
    
    $query_director = "INSERT INTO user (name, username, cedula, correo, pass, id_rol) 
                       VALUES ('Director General', 'director', '87654321', 'director@sistema.com', 'director123', 2)
                       ON DUPLICATE KEY UPDATE 
                       name = VALUES(name), 
                       pass = VALUES(pass)";
    
    $query_tecnico = "INSERT INTO user (name, username, cedula, correo, pass, id_rol) 
                      VALUES ('Técnico de Prueba', 'tecnico', '11223344', 'tecnico@sistema.com', 'tecnico123', 3)
                      ON DUPLICATE KEY UPDATE 
                      name = VALUES(name), 
                      pass = VALUES(pass)";
    
    $query_analista = "INSERT INTO user (name, username, cedula, correo, pass, id_rol) 
                       VALUES ('Analista del Sistema', 'analista', '55667788', 'analista@sistema.com', 'analista123', 4)
                       ON DUPLICATE KEY UPDATE 
                       name = VALUES(name), 
                       pass = VALUES(pass)";
    
    // Insertar usuarios
    echo "<h3>📝 Insertando usuarios...</h3>";
    
    // Administrador
    if (mysqli_query($conexion, $query_admin)) {
        echo "✅ Usuario Administrador (Rol 1) creado/actualizado<br>";
    } else {
        echo "❌ Error al crear Administrador: " . mysqli_error($conexion) . "<br>";
    }
    
    // Director
    if (mysqli_query($conexion, $query_director)) {
        echo "✅ Usuario Director (Rol 2) creado/actualizado<br>";
    } else {
        echo "❌ Error al crear Director: " . mysqli_error($conexion) . "<br>";
    }
    
    // Técnico
    if (mysqli_query($conexion, $query_tecnico)) {
        echo "✅ Usuario Técnico (Rol 3) creado/actualizado<br>";
    } else {
        echo "❌ Error al crear Técnico: " . mysqli_error($conexion) . "<br>";
    }
    
    // Analista
    if (mysqli_query($conexion, $query_analista)) {
        echo "✅ Usuario Analista (Rol 4) creado/actualizado<br>";
    } else {
        echo "❌ Error al crear Analista: " . mysqli_error($conexion) . "<br>";
    }
    
    echo "<br><h3>👥 Usuarios disponibles:</h3>";
    
    // Mostrar usuarios creados
    $usuarios = mysqli_query($conexion, "SELECT id_user, name, username, cedula, id_rol FROM user WHERE id_rol IN (1, 2, 3, 4) ORDER BY id_rol");
    
    if (mysqli_num_rows($usuarios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Username</th><th>Cédula</th><th>Rol</th></tr>";
        
        while ($row = mysqli_fetch_assoc($usuarios)) {
            $rol_nombre = "";
            switch ($row['id_rol']) {
                case 1: $rol_nombre = "Administrador"; break;
                case 2: $rol_nombre = "Director"; break;
                case 3: $rol_nombre = "Técnico"; break;
                case 4: $rol_nombre = "Analista"; break;
                default: $rol_nombre = "Desconocido"; break;
            }
            
            echo "<tr>";
            echo "<td>" . $row['id_user'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['cedula'] . "</td>";
            echo "<td>" . $rol_nombre . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No se encontraron usuarios<br>";
    }
    
    echo "<br><h3>🔑 Credenciales de Acceso:</h3>";
    echo "<strong>Administrador (Rol 1):</strong><br>";
    echo "- Username: admin<br>";
    echo "- Contraseña: admin123<br>";
    echo "- Cédula: 12345678<br>";
    echo "- Acceso: Panel completo de administración<br><br>";
    
    echo "<strong>Director (Rol 2):</strong><br>";
    echo "- Username: director<br>";
    echo "- Contraseña: director123<br>";
    echo "- Cédula: 87654321<br>";
    echo "- Acceso: Panel completo de administración<br><br>";
    
    echo "<strong>Técnico (Rol 3):</strong><br>";
    echo "- Username: tecnico<br>";
    echo "- Contraseña: tecnico123<br>";
    echo "- Cédula: 11223344<br>";
    echo "- Acceso: Dashboard técnico<br><br>";
    
    echo "<strong>Analista (Rol 4):</strong><br>";
    echo "- Username: analista<br>";
    echo "- Contraseña: analista123<br>";
    echo "- Cédula: 55667788<br>";
    echo "- Acceso: Panel de analista<br><br>";
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "✅ <strong>Usuarios restaurados exitosamente!</strong><br>";
    echo "Ahora puedes hacer login con cualquiera de estas credenciales.<br><br>";
    echo "💡 <strong>Nota:</strong> Los usuarios normales NO necesitan cuenta para crear incidencias.<br>";
    echo "Pueden acceder directamente a la página pública de solicitud de incidencias.";
    echo "</div>";
    
    echo "<a href='../login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>";
    echo "← Volver al Login";
    echo "</a>";
    
    echo "<a href='verificar_roles.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>";
    echo "🔍 Verificar Roles";
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



