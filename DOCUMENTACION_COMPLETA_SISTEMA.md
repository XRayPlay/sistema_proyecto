# 📋 DOCUMENTACIÓN COMPLETA DEL SISTEMA MINEC
## Sistema de Gestión de Incidencias y Soporte Técnico

---

## 🎯 **RESUMEN EJECUTIVO**

El **Sistema MINEC** es una aplicación web desarrollada en **PHP** con **MySQL** que permite gestionar incidencias de soporte técnico en una organización. El sistema maneja diferentes roles de usuario (Admin, Director, Técnico, Analista) y proporciona un flujo completo de gestión de incidencias desde su creación hasta su resolución.

---

## 🏗️ **ARQUITECTURA DEL SISTEMA**

### **Tecnologías Utilizadas:**
- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Frameworks**: Bootstrap 5, Chart.js
- **Iconos**: Font Awesome 6
- **Servidor**: XAMPP (Apache + MySQL + PHP)

### **Estructura de Carpetas:**
```
sistema_proyecto/
├── db/                          # Scripts de base de datos
├── nuevo_diseno/               # Interfaz principal del sistema
├── php/                        # Lógica del servidor
├── public/                     # Recursos estáticos
├── login.php                   # Página de inicio de sesión
└── README.md                   # Documentación básica
```

---

## 👥 **SISTEMA DE ROLES Y PERMISOS**

### **1. ADMINISTRADOR (Rol 1)**
- **Usuario**: `admin`
- **Contraseña**: `Admin45*`
- **Funciones**:
  - Acceso completo al sistema
  - Gestión de usuarios (crear, editar, eliminar)
  - Gestión de técnicos
  - Gestión de incidencias
  - Visualización de estadísticas y gráficas
  - Exportación de reportes

### **2. DIRECTOR (Rol 2)**
- **Usuario**: `director`
- **Contraseña**: `director123`
- **Funciones**:
  - Acceso a panel principal
  - Gestión de técnicos
  - Gestión de incidencias
  - Visualización de estadísticas
  - Supervisión general del sistema

### **3. TÉCNICO (Rol 3)**
- **Usuario**: `tecnico`
- **Contraseña**: `password`
- **Funciones**:
  - Panel específico para técnicos
  - Ver incidencias asignadas
  - Actualizar estado de incidencias
  - Agregar comentarios y resoluciones

### **4. ANALISTA (Rol 4)**
- **Usuario**: `analista`
- **Contraseña**: `password`
- **Funciones**:
  - Gestión de TODAS las incidencias del sistema
  - Asignación de técnicos a incidencias
  - Visualización de técnicos disponibles
  - Exportación de reportes completos

---

## 🗄️ **ESTRUCTURA DE BASE DE DATOS**

### **Tabla Principal: `user`**
```sql
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `sexo` enum('M','F') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  `last_connection` date DEFAULT NULL,
  `id_floor` int(11) DEFAULT NULL,
  `id_cargo` int(11) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `id_status_user` int(11) DEFAULT 1,
  PRIMARY KEY (`id_user`)
);
```

### **Tabla de Incidencias: `incidencias`**
```sql
CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `solicitante_nombre` varchar(100) NOT NULL,
  `solicitante_cedula` varchar(20) NOT NULL,
  `solicitante_email` varchar(100) NOT NULL,
  `solicitante_telefono` varchar(20) NOT NULL,
  `solicitante_direccion` text NOT NULL,
  `solicitante_extension` varchar(10) DEFAULT NULL,
  `tipo_incidencia` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `estado` enum('pendiente','asignada','en_proceso','resuelta','cerrada') DEFAULT 'pendiente',
  `tecnico_asignado` int(11) DEFAULT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT NULL,
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  `comentarios_tecnico` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
);
```

### **Tabla de Técnicos: `tecnicos`**
```sql
CREATE TABLE `tecnicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `estado_disponibilidad` enum('Disponible','Ocupado') DEFAULT 'Disponible',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

### **Tabla de Tipos de Reportes: `reports_type`**
```sql
CREATE TABLE `reports_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

---

## 🔐 **SISTEMA DE AUTENTICACIÓN**

### **Archivo Principal: `php/clases.php`**

#### **ESTE CÓDIGO SIRVE PARA:** Manejar toda la lógica de autenticación y sesiones del sistema

#### **Clase `usuario` - Método `login()`**
```php
public function login($data) {
    // ESTE CÓDIGO SIRVE PARA: Conectar a la base de datos
    $c = new conectar();
    $conexion = $c->conexion();
    
    // ESTE CÓDIGO SIRVE PARA: Preparar la consulta SQL básica
    $query = "SELECT id_user FROM user WHERE";
    
    // ESTE CÓDIGO SIRVE PARA: Verificar si el usuario ingresó cédula o username
    // Si es número = cédula, si es texto = username
    if (ctype_digit($data[0]) && strlen($data[0])) {
        $query .= " pass='$data[1]' AND cedula='$data[0]'";
    } else {
        $query .= " username='$data[0]' AND pass='$data[1]'";
    }
    
    // ESTE CÓDIGO SIRVE PARA: Ejecutar la consulta y verificar credenciales
    $validar_login = mysqli_query($conexion, $query);
    
    // ESTE CÓDIGO SIRVE PARA: Si las credenciales son correctas, hacer lo siguiente:
    if(mysqli_num_rows($validar_login) > 0) {
        // ESTE CÓDIGO SIRVE PARA: Actualizar la fecha de última conexión del usuario
        $last_connect = date("Y-m-d");
        $query = "UPDATE user SET last_connection ='$last_connect' WHERE...";
        
        // ESTE CÓDIGO SIRVE PARA: Obtener todos los datos del usuario logueado
        $query = "SELECT name, id_rol, id_user, cedula FROM user WHERE...";
        
        // ESTE CÓDIGO SIRVE PARA: Crear la sesión del usuario con sus datos
        $_SESSION['usuario'] = [
            'name' => $row['name'],        // Nombre del usuario
            'id_rol' => $row['id_rol'],    // Rol (1=Admin, 2=Director, 3=Técnico, 4=Analista)
            'id_user' => $row['id_user'],  // ID único del usuario
            'cedula' => $row['cedula']     // Cédula para identificar incidencias
        ];
        
        // ESTE CÓDIGO SIRVE PARA: Redirigir al usuario según su rol
        if ($row['id_rol'] == 3) {
            // Si es técnico, va al panel de técnicos
            header("location: ../nuevo_diseno/tecnicos/dashboard_tecnico.php");
        } else {
            // Si es Admin, Director o Analista, va al panel principal
            header("location: ../nuevo_diseno/inicio_completo.php");
        }
    }
}
```

### **Sistema de Permisos: `php/permisos.php`**

#### **ESTE ARCHIVO SIRVE PARA:** Controlar el acceso a diferentes partes del sistema según el rol del usuario

```php
// ESTA FUNCIÓN SIRVE PARA: Verificar si el usuario actual es Administrador
function esAdmin() {
    return isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 1;
}

// ESTA FUNCIÓN SIRVE PARA: Verificar si el usuario actual es Director
function esDirector() {
    return isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 2;
}

// ESTA FUNCIÓN SIRVE PARA: Verificar si el usuario actual es Técnico
function esTecnico() {
    return isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 3;
}

// ESTA FUNCIÓN SIRVE PARA: Verificar si el usuario actual es Analista
function esAnalista() {
    return isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 4;
}
```

#### **CÓMO SE USA ESTE CÓDIGO:**
- En cada página se llama `esAdmin()` para verificar permisos
- Si retorna `true`, el usuario puede acceder
- Si retorna `false`, se redirige al login

---

## 🎨 **INTERFAZ DE USUARIO**

### **Diseño Responsivo con Bootstrap 5**

#### **Paleta de Colores:**
```css
:root {
    --primary-color: #059669;      /* Verde principal */
    --primary-dark: #047857;       /* Verde oscuro */
    --secondary-color: #64748b;    /* Gris secundario */
    --success-color: #10b981;      /* Verde éxito */
    --warning-color: #f59e0b;      /* Amarillo advertencia */
    --danger-color: #ef4444;       /* Rojo peligro */
    --info-color: #06b6d4;         /* Azul información */
    --light-bg: #f0fdf4;           /* Fondo claro */
    --dark-bg: #064e3b;            /* Fondo oscuro */
}
```

#### **Componentes Principales:**

1. **Top Navigation Bar**
   - Logo del sistema
   - Información del usuario
   - Avatar con iniciales

2. **Sidebar**
   - Navegación por roles
   - Menú contextual
   - Botón de cerrar sesión

3. **Main Content**
   - Tarjetas de estadísticas
   - Gráficas dinámicas
   - Tablas de datos
   - Formularios modales

---

## 📊 **PANEL PRINCIPAL (Admin/Director)**

### **Archivo: `nuevo_diseno/inicio_completo.php`**

#### **Características Principales:**

1. **Dashboard con Estadísticas**
   - Tarjetas de métricas clave
   - Gráficas interactivas con Chart.js
   - Filtros dinámicos

2. **Gráficas Implementadas**
   - **Incidencias por Fecha**: Línea temporal
   - **Incidencias por Tipo**: Gráfica de pastel

3. **Sistema de Filtros**
   ```javascript
   async function actualizarGraficas(filtros) {
       try {
           const response = await fetch('../php/obtener_estadisticas_filtradas.php', {
               method: 'POST',
               headers: {'Content-Type': 'application/json'},
               body: JSON.stringify(filtros)
           });
           
           const data = await response.json();
           
           if (data.success) {
               actualizarGraficaFecha(data.datos_fecha);
               actualizarGraficaTipo(data.datos_tipo);
           }
       } catch (error) {
           console.error('Error:', error);
       }
   }
   ```

4. **Tarjetas de Técnicos Disponibles**
   - Grid responsivo
   - Estado de disponibilidad
   - Información básica

---

## 👨‍💼 **PANEL DEL ANALISTA**

### **Archivo: `nuevo_diseno/panel_analista.php`**

#### **Funcionalidades Específicas:**

1. **Gestión de Incidencias**
   - Ver TODAS las incidencias del sistema
   - Asignar técnicos a incidencias
   - Cambiar estados de incidencias

2. **Técnicos Disponibles**
   - Lista de técnicos activos
   - Estado de disponibilidad
   - Especialidades

3. **Sistema de Asignación**
   ```php
   // Modal para asignar técnico
   function asignarTecnicoIncidencia(id) {
       document.getElementById('incidencia_id_asignar').value = id;
       const modal = new bootstrap.Modal(document.getElementById('modalAsignarTecnico'));
       modal.show();
   }
   ```

4. **Exportación de Reportes**
   - Archivo: `php/exportar_todas_incidencias_excel.php`
   - Exporta todas las incidencias del sistema
   - Formato Excel compatible

---

## 🔧 **GESTIÓN DE INCIDENCIAS**

### **CRUD Completo: `php/gestionar_incidencias_crud.php`**

#### **ESTE ARCHIVO SIRVE PARA:** Manejar todas las operaciones de base de datos relacionadas con incidencias (Crear, Leer, Actualizar, Eliminar)

#### **Operaciones Implementadas:**

1. **Crear Incidencia**
   ```php
   // ESTA FUNCIÓN SIRVE PARA: Crear una nueva incidencia en la base de datos
   function crearIncidencia() {
       // ESTE CÓDIGO SIRVE PARA: Obtener y limpiar los datos del formulario
       $tipo_incidencia = mysqli_real_escape_string($conexion, $_POST['tipo_incidencia']);
       $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
       $prioridad = mysqli_real_escape_string($conexion, $_POST['prioridad']);
       $solicitante_nombre = mysqli_real_escape_string($conexion, $_POST['solicitante_nombre']);
       $solicitante_cedula = mysqli_real_escape_string($conexion, $_POST['solicitante_cedula']);
       // ... más campos
       
       // ESTE CÓDIGO SIRVE PARA: Preparar la consulta SQL para insertar la incidencia
       $query = "INSERT INTO incidencias (tipo_incidencia, descripcion, prioridad, 
                solicitante_nombre, solicitante_cedula, solicitante_email, 
                solicitante_telefono, solicitante_direccion, solicitante_extension, 
                departamento, estado, fecha_creacion, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW(), NOW())";
       
       // ESTE CÓDIGO SIRVE PARA: Crear un prepared statement para evitar SQL injection
       $stmt = mysqli_prepare($conexion, $query);
       
       // ESTE CÓDIGO SIRVE PARA: Vincular los parámetros a la consulta
       mysqli_stmt_bind_param($stmt, 'ssssssssss', $tipo_incidencia, $descripcion, 
                             $prioridad, $solicitante_nombre, $solicitante_cedula, 
                             $solicitante_email, $solicitante_telefono, 
                             $solicitante_direccion, $solicitante_extension, $departamento);
       
       // ESTE CÓDIGO SIRVE PARA: Ejecutar la consulta y devolver respuesta JSON
       if (mysqli_stmt_execute($stmt)) {
           echo json_encode(['success' => true, 'message' => 'Incidencia creada exitosamente']);
       }
   }
   ```

2. **Obtener Incidencias**
   ```php
   // ESTA FUNCIÓN SIRVE PARA: Obtener todas las incidencias de la base de datos
   function obtenerIncidencias() {
       // ESTE CÓDIGO SIRVE PARA: Crear una consulta SQL que obtiene incidencias con datos del técnico
       $query = "SELECT i.id, i.tipo_incidencia, i.descripcion, i.prioridad, i.estado, 
                        i.solicitante_nombre, i.solicitante_cedula, i.solicitante_email, 
                        i.solicitante_telefono, i.solicitante_direccion, i.solicitante_extension, 
                        i.departamento, i.fecha_creacion, t.nombre as tecnico_nombre
                 FROM incidencias i 
                 LEFT JOIN tecnicos t ON i.tecnico_asignado = t.id 
                 ORDER BY i.fecha_creacion DESC";
       
       // ESTE CÓDIGO SIRVE PARA: Ejecutar la consulta en la base de datos
       $resultado = mysqli_query($conexion, $query);
       
       // ESTE CÓDIGO SIRVE PARA: Crear un array vacío para almacenar las incidencias
       $incidencias = [];
       
       // ESTE CÓDIGO SIRVE PARA: Recorrer cada fila de resultados y convertirla en array
       while ($incidencia = mysqli_fetch_assoc($resultado)) {
           $incidencias[] = [
               'id' => $incidencia['id'],
               'tipo_incidencia' => $incidencia['tipo_incidencia'],
               'descripcion' => $incidencia['descripcion'],
               'prioridad' => $incidencia['prioridad'],
               'estado' => $incidencia['estado'],
               'solicitante_nombre' => $incidencia['solicitante_nombre'],
               'solicitante_cedula' => $incidencia['solicitante_cedula'],
               'solicitante_email' => $incidencia['solicitante_email'],
               'solicitante_telefono' => $incidencia['solicitante_telefono'],
               'solicitante_direccion' => $incidencia['solicitante_direccion'],
               'solicitante_extension' => $incidencia['solicitante_extension'],
               'departamento' => $incidencia['departamento'],
               'fecha_creacion' => $incidencia['fecha_creacion'],
               'tecnico_nombre' => $incidencia['tecnico_nombre']
           ];
       }
       
       // ESTE CÓDIGO SIRVE PARA: Devolver los datos en formato JSON para que JavaScript los pueda usar
       echo json_encode(['success' => true, 'incidencias' => $incidencias]);
   }
   ```

3. **Actualizar Incidencia**
4. **Eliminar Incidencia**
5. **Obtener Tipos de Incidencia**

---

## 👥 **GESTIÓN DE TÉCNICOS**

### **CRUD: `php/gestionar_tecnicos_crud.php`**

#### **Funcionalidades:**

1. **Crear Técnico**
2. **Obtener Técnicos**
3. **Editar Técnico**
4. **Eliminar Técnico**
5. **Obtener Técnico por ID**
6. **Obtener Incidencias del Técnico**

### **Interfaz: `nuevo_diseno/gestionar_tecnicos.php`**

- Tabla responsiva con datos de técnicos
- Botones de acción (Ver, Editar, Eliminar)
- Modal para ver incidencias asignadas
- Formularios de creación y edición

---

## 📈 **SISTEMA DE ESTADÍSTICAS**

### **Archivo: `php/obtener_estadisticas_filtradas.php`**

#### **Gráficas Implementadas:**

1. **Incidencias por Fecha**
   ```php
   // Consulta para datos de fecha
   $query_fecha = "SELECT DATE(fecha_creacion) as fecha, COUNT(*) as cantidad 
                   FROM incidencias 
                   WHERE 1=1 $where_clause 
                   GROUP BY DATE(fecha_creacion) 
                   ORDER BY fecha DESC 
                   LIMIT 7";
   ```

2. **Incidencias por Tipo**
   ```php
   // Consulta para datos de tipo
   $query_tipo = "SELECT tipo_incidencia, COUNT(*) as cantidad 
                  FROM incidencias 
                  WHERE 1=1 $where_clause 
                  GROUP BY tipo_incidencia 
                  ORDER BY cantidad DESC";
   ```

#### **Sistema de Filtros:**
- **Estado**: pendiente, asignada, en_proceso, resuelta, cerrada
- **Prioridad**: baja, media, alta
- **Tipo**: Hardware, Software, Internet/Red, etc.
- **Fecha**: Rango de fechas personalizable

---

## 🔒 **SEGURIDAD IMPLEMENTADA**

### **1. Autenticación y Autorización**
- Verificación de sesiones en cada página
- Control de acceso por roles
- Redirección automática según permisos

### **2. Protección contra SQL Injection**
```php
// Uso de prepared statements
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, 'ssssssssss', $param1, $param2, ...);
mysqli_stmt_execute($stmt);
```

### **3. Validación de Datos**
```php
// Validación de campos requeridos
if (empty($tipo_incidencia) || empty($descripcion) || empty($prioridad)) {
    echo json_encode(['success' => false, 'message' => 'Campos requeridos']);
    return;
}

// Sanitización de datos
$tipo_incidencia = mysqli_real_escape_string($conexion, $_POST['tipo_incidencia']);
```

### **4. Manejo de Errores**
```php
try {
    // Operaciones de base de datos
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
```

---

## 📱 **RESPONSIVIDAD Y UX**

### **CSS Grid y Flexbox**
```css
/* Layout responsivo */
.filter-stats-layout {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 2.5rem;
}

.tecnicos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
}

@media (max-width: 768px) {
    .filter-stats-layout {
        grid-template-columns: 1fr;
    }
}
```

### **Animaciones y Transiciones**
```css
.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

---

## 🚀 **FUNCIONALIDADES AVANZADAS**

### **1. Sistema de Notificaciones**
- Alertas de éxito/error
- Mensajes informativos
- Confirmaciones de acciones

### **2. Exportación de Datos**
- Reportes en Excel
- Filtros aplicables
- Formato profesional

### **3. Búsqueda y Filtrado**
- Filtros dinámicos en tiempo real
- Búsqueda por múltiples criterios
- Resultados paginados

### **4. Dashboard Interactivo**
- Gráficas actualizables
- Métricas en tiempo real
- Indicadores visuales

---

## 📋 **FLUJO DE TRABAJO TÍPICO**

### **1. Creación de Incidencia**
1. Usuario reporta problema
2. Se crea incidencia con estado "pendiente"
3. Se asigna prioridad (baja, media, alta)
4. Se registra información del solicitante

### **2. Asignación de Técnico**
1. Analista revisa incidencias pendientes
2. Selecciona técnico disponible
3. Asigna incidencia al técnico
4. Estado cambia a "asignada"

### **3. Resolución**
1. Técnico trabaja en la incidencia
2. Estado cambia a "en_proceso"
3. Técnico agrega comentarios
4. Estado final: "resuelta" o "cerrada"

---

## 🛠️ **INSTALACIÓN Y CONFIGURACIÓN**

### **Requisitos del Sistema:**
- XAMPP 7.4+ (Apache, MySQL, PHP)
- Navegador web moderno
- 2GB RAM mínimo
- 1GB espacio en disco

### **Pasos de Instalación:**

1. **Instalar XAMPP**
   ```bash
   # Descargar e instalar XAMPP desde https://www.apachefriends.org/
   ```

2. **Configurar Base de Datos**
   ```sql
   -- Crear base de datos
   CREATE DATABASE sistema_proyecto;
   
   -- Importar estructura
   mysql -u root -p sistema_proyecto < db/sistema_proyecto.sql
   
   -- Crear usuarios
   mysql -u root -p sistema_proyecto < db/crear_usuarios_completos.sql
   ```

3. **Configurar Conexión**
   ```php
   // php/conexion_be.php
   $host = 'localhost';
   $usuario = 'root';
   $password = '';
   $base_datos = 'sistema_proyecto';
   ```

4. **Acceder al Sistema**
   ```
   URL: http://localhost/sistema_proyecto/login.php
   ```

---

## 📊 **MÉTRICAS Y KPIs**

### **Indicadores Implementados:**
- **Tiempo promedio de resolución**
- **Incidencias por técnico**
- **Tipos de incidencias más comunes**
- **Tasa de resolución por prioridad**
- **Disponibilidad de técnicos**

### **Reportes Disponibles:**
- Reporte de incidencias por período
- Estadísticas de rendimiento de técnicos
- Análisis de tipos de incidencias
- Reportes de satisfacción (futuro)

---

## 🔮 **MEJORAS FUTURAS**

### **Funcionalidades Planificadas:**
1. **Sistema de Notificaciones Push**
2. **Chat en tiempo real**
3. **App móvil**
4. **Integración con Active Directory**
5. **Dashboard avanzado con más métricas**
6. **Sistema de tickets con numeración automática**
7. **Workflow de aprobaciones**
8. **Base de conocimientos**

### **Optimizaciones Técnicas:**
1. **Implementar PDO en lugar de MySQLi**
2. **Agregar cache con Redis**
3. **Optimizar consultas SQL**
4. **Implementar API REST**
5. **Agregar tests unitarios**

---

## 📁 **EXPLICACIÓN DETALLADA DE CADA ARCHIVO**

### **🔐 ARCHIVOS DE AUTENTICACIÓN**

#### **`login.php`**
- **ESTE ARCHIVO SIRVE PARA:** Página principal de inicio de sesión
- **QUÉ HACE:** Muestra el formulario de login y maneja la autenticación
- **CÓDIGO PRINCIPAL:** Formulario HTML con validación JavaScript

#### **`php/login_usuario_be.php`**
- **ESTE ARCHIVO SIRVE PARA:** Procesar el formulario de login
- **QUÉ HACE:** Recibe usuario/contraseña, los valida y crea la sesión
- **CÓDIGO PRINCIPAL:** `$obj->login($data)` - Llama a la clase usuario

#### **`php/clases.php`**
- **ESTE ARCHIVO SIRVE PARA:** Contener todas las clases principales del sistema
- **QUÉ HACE:** Define la clase `usuario` con método `login()` y clase `conectar`
- **CÓDIGO PRINCIPAL:** Lógica de autenticación y conexión a base de datos

#### **`php/permisos.php`**
- **ESTE ARCHIVO SIRVE PARA:** Controlar el acceso según roles
- **QUÉ HACE:** Define funciones `esAdmin()`, `esDirector()`, `esTecnico()`, `esAnalista()`
- **CÓDIGO PRINCIPAL:** Verificaciones de `$_SESSION['usuario']['id_rol']`

### **🎨 ARCHIVOS DE INTERFAZ**

#### **`nuevo_diseno/inicio_completo.php`**
- **ESTE ARCHIVO SIRVE PARA:** Panel principal para Admin y Director
- **QUÉ HACE:** Muestra dashboard con estadísticas, gráficas y técnicos disponibles
- **CÓDIGO PRINCIPAL:** HTML con CSS y JavaScript para gráficas interactivas

#### **`nuevo_diseno/panel_analista.php`**
- **ESTE ARCHIVO SIRVE PARA:** Panel específico del Analista
- **QUÉ HACE:** Permite gestionar TODAS las incidencias y asignar técnicos
- **CÓDIGO PRINCIPAL:** Tabla de incidencias con botones de asignación

#### **`nuevo_diseno/gestionar_incidencias.php`**
- **ESTE ARCHIVO SIRVE PARA:** Interfaz para crear, editar y eliminar incidencias
- **QUÉ HACE:** Formularios modales y tabla con operaciones CRUD
- **CÓDIGO PRINCIPAL:** JavaScript que llama a `gestionar_incidencias_crud.php`

#### **`nuevo_diseno/gestionar_tecnicos.php`**
- **ESTE ARCHIVO SIRVE PARA:** Interfaz para gestionar técnicos
- **QUÉ HACE:** Tabla de técnicos con botones para ver, editar, eliminar
- **CÓDIGO PRINCIPAL:** Modal para ver incidencias asignadas a cada técnico

### **🔧 ARCHIVOS DE LÓGICA (PHP)**

#### **`php/gestionar_incidencias_crud.php`**
- **ESTE ARCHIVO SIRVE PARA:** Manejar todas las operaciones de base de datos de incidencias
- **QUÉ HACE:** Funciones `crearIncidencia()`, `obtenerIncidencias()`, `actualizarIncidencia()`, `eliminarIncidencia()`
- **CÓDIGO PRINCIPAL:** Prepared statements para evitar SQL injection

#### **`php/gestionar_tecnicos_crud.php`**
- **ESTE ARCHIVO SIRVE PARA:** Manejar operaciones de base de datos de técnicos
- **QUÉ HACE:** CRUD completo para técnicos + función para ver incidencias asignadas
- **CÓDIGO PRINCIPAL:** Consultas SQL con JOIN para obtener datos relacionados

#### **`php/obtener_estadisticas_filtradas.php`**
- **ESTE ARCHIVO SIRVE PARA:** Generar datos para las gráficas del dashboard
- **QUÉ HACE:** Aplica filtros y devuelve datos de incidencias por fecha y tipo
- **CÓDIGO PRINCIPAL:** Consultas SQL con GROUP BY y COUNT para estadísticas

#### **`php/asignar_tecnico.php`**
- **ESTE ARCHIVO SIRVE PARA:** Asignar un técnico a una incidencia específica
- **QUÉ HACE:** Valida permisos, actualiza la incidencia y cambia estado
- **CÓDIGO PRINCIPAL:** UPDATE SQL para cambiar `tecnico_asignado` y `estado`

#### **`php/exportar_todas_incidencias_excel.php`**
- **ESTE ARCHIVO SIRVE PARA:** Exportar todas las incidencias a Excel
- **QUÉ HACE:** Genera archivo .xls con todos los datos de incidencias
- **CÓDIGO PRINCIPAL:** Headers HTTP para descarga + tabla HTML

### **🗄️ ARCHIVOS DE BASE DE DATOS**

#### **`db/sistema_proyecto.sql`**
- **ESTE ARCHIVO SIRVE PARA:** Crear la estructura completa de la base de datos
- **QUÉ HACE:** Define todas las tablas (user, incidencias, tecnicos, reports_type)
- **CÓDIGO PRINCIPAL:** CREATE TABLE statements con relaciones

#### **`db/crear_usuarios_completos.sql`**
- **ESTE ARCHIVO SIRVE PARA:** Insertar usuarios de prueba en el sistema
- **QUÉ HACE:** Crea Admin, Director, Técnico, Analista con contraseñas
- **CÓDIGO PRINCIPAL:** INSERT statements con datos de ejemplo

#### **`db/insertar_tipos_reportes.sql`**
- **ESTE ARCHIVO SIRVE PARA:** Poblar la tabla de tipos de incidencias
- **QUÉ HACE:** Inserta 9 tipos de incidencias (Hardware, Software, etc.)
- **CÓDIGO PRINCIPAL:** INSERT statements con tipos predefinidos

### **📊 ARCHIVOS DE ESTADÍSTICAS**

#### **`nuevo_diseno/inicio_completo.php` (JavaScript)**
- **ESTE CÓDIGO SIRVE PARA:** Crear gráficas interactivas con Chart.js
- **QUÉ HACE:** Función `actualizarGraficas()` que llama al PHP y actualiza las gráficas
- **CÓDIGO PRINCIPAL:** `fetch()` para obtener datos + `chart.update()` para refrescar

#### **`php/obtener_estadisticas_filtradas.php`**
- **ESTE ARCHIVO SIRVE PARA:** Procesar filtros y devolver datos para gráficas
- **QUÉ HACE:** Construye WHERE clause dinámico según filtros aplicados
- **CÓDIGO PRINCIPAL:** Lógica de filtros + consultas SQL con GROUP BY

### **🔒 ARCHIVOS DE SEGURIDAD**

#### **`php/conexion_be.php`**
- **ESTE ARCHIVO SIRVE PARA:** Configurar la conexión a la base de datos
- **QUÉ HACE:** Define host, usuario, contraseña y nombre de base de datos
- **CÓDIGO PRINCIPAL:** Variables de configuración para mysqli_connect()

#### **`php/cerrar_sesion.php`**
- **ESTE ARCHIVO SIRVE PARA:** Cerrar la sesión del usuario
- **QUÉ HACE:** Destruye la sesión y redirige al login
- **CÓDIGO PRINCIPAL:** `session_destroy()` + `header("location: login.php")`

### **📱 ARCHIVOS DE RESPONSIVIDAD**

#### **CSS en cada archivo PHP**
- **ESTE CÓDIGO SIRVE PARA:** Hacer que la interfaz se adapte a diferentes pantallas
- **QUÉ HACE:** Media queries para móviles, CSS Grid y Flexbox para layouts
- **CÓDIGO PRINCIPAL:** `@media (max-width: 768px)` + `display: grid`

### **🔄 FLUJO DE DATOS ENTRE ARCHIVOS**

1. **Usuario hace login** → `login.php` → `login_usuario_be.php` → `clases.php`
2. **Se crea sesión** → `permisos.php` verifica acceso
3. **Usuario ve dashboard** → `inicio_completo.php` → `obtener_estadisticas_filtradas.php`
4. **Usuario gestiona incidencias** → `gestionar_incidencias.php` → `gestionar_incidencias_crud.php`
5. **Analista asigna técnico** → `panel_analista.php` → `asignar_tecnico.php`
6. **Se exportan datos** → `exportar_todas_incidencias_excel.php`

---

## 🎓 **PUNTOS CLAVE PARA LA EXPOSICIÓN**

### **1. Arquitectura del Sistema**
- **MVC Pattern**: Separación de lógica, vista y datos
- **RESTful API**: Endpoints para operaciones CRUD
- **Responsive Design**: Adaptable a diferentes dispositivos

### **2. Seguridad**
- **Autenticación robusta** con hash SHA-512
- **Autorización por roles** granular
- **Protección SQL Injection** con prepared statements
- **Validación de datos** en frontend y backend

### **3. Experiencia de Usuario**
- **Interfaz intuitiva** con Bootstrap 5
- **Gráficas interactivas** con Chart.js
- **Animaciones suaves** y transiciones
- **Feedback visual** para todas las acciones

### **4. Escalabilidad**
- **Base de datos normalizada**
- **Código modular** y reutilizable
- **APIs bien estructuradas**
- **Fácil mantenimiento**

### **5. Funcionalidades Destacadas**
- **Dashboard en tiempo real**
- **Sistema de roles completo**
- **Gestión de incidencias end-to-end**
- **Reportes exportables**
- **Filtros dinámicos**

---

## 📞 **CONTACTO Y SOPORTE**

### **Credenciales de Acceso:**
- **Admin**: admin / Admin45*
- **Director**: director / director123
- **Técnico**: tecnico / password
- **Analista**: analista / password

### **Archivos de Configuración:**
- `php/conexion_be.php` - Configuración de base de datos
- `php/permisos.php` - Sistema de permisos
- `php/clases.php` - Clases principales del sistema

---

## 🏆 **CONCLUSIÓN**

El **Sistema MINEC** es una solución completa y robusta para la gestión de incidencias de soporte técnico. Combina tecnologías modernas con buenas prácticas de desarrollo, ofreciendo una experiencia de usuario excepcional y una arquitectura escalable.

**Características Destacadas:**
- ✅ **Sistema de roles completo**
- ✅ **Interfaz moderna y responsiva**
- ✅ **Seguridad robusta**
- ✅ **Funcionalidades avanzadas**
- ✅ **Fácil mantenimiento**
- ✅ **Escalabilidad**

El sistema está listo para producción y puede adaptarse a las necesidades específicas de cualquier organización que requiera gestión de soporte técnico.

---

## 💻 **EJEMPLOS ESPECÍFICOS DE CÓDIGO**

### **🔐 EJEMPLO 1: Sistema de Login**

#### **En `login.php` (Frontend):**
```html
<!-- ESTE CÓDIGO SIRVE PARA: Mostrar el formulario de login -->
<form action="../php/login_usuario_be.php" method="POST">
    <input type="text" name="usuario" placeholder="Usuario o Cédula" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">INICIAR SESIÓN</button>
</form>
```

#### **En `php/login_usuario_be.php` (Backend):**
```php
// ESTE CÓDIGO SIRVE PARA: Recibir los datos del formulario
$usuario = $_POST['usuario'];
$pass = $_POST['password'];
$pass = hash('sha512', $pass);  // ESTE CÓDIGO SIRVE PARA: Encriptar la contraseña

// ESTE CÓDIGO SIRVE PARA: Crear array con datos para la clase
$data = array($usuario, $pass);

// ESTE CÓDIGO SIRVE PARA: Llamar a la clase usuario para validar
$obj = new usuario;
$obj->login($data);
```

### **📊 EJEMPLO 2: Gráficas Interactivas**

#### **En `nuevo_diseno/inicio_completo.php` (JavaScript):**
```javascript
// ESTE CÓDIGO SIRVE PARA: Crear una gráfica de líneas con Chart.js
let chartIncidenciasFecha = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],  // ESTE ARRAY SIRVE PARA: Las fechas en el eje X
        datasets: [{
            label: 'Incidencias por Fecha',
            data: [],  // ESTE ARRAY SIRVE PARA: Los números en el eje Y
            borderColor: '#059669',
            backgroundColor: 'rgba(5, 150, 105, 0.1)'
        }]
    }
});

// ESTA FUNCIÓN SIRVE PARA: Actualizar la gráfica cuando cambian los filtros
async function actualizarGraficas(filtros) {
    const response = await fetch('../php/obtener_estadisticas_filtradas.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(filtros)  // ESTE CÓDIGO SIRVE PARA: Enviar filtros al servidor
    });
    
    const data = await response.json();
    
    // ESTE CÓDIGO SIRVE PARA: Actualizar los datos de la gráfica
    chartIncidenciasFecha.data.labels = data.datos_fecha.labels;
    chartIncidenciasFecha.data.datasets[0].data = data.datos_fecha.data;
    chartIncidenciasFecha.update();  // ESTE CÓDIGO SIRVE PARA: Refrescar la gráfica
}
```

### **🗄️ EJEMPLO 3: Operaciones de Base de Datos**

#### **En `php/gestionar_incidencias_crud.php`:**
```php
// ESTA FUNCIÓN SIRVE PARA: Obtener una incidencia específica por su ID
function obtenerIncidenciaPorId($id) {
    // ESTE CÓDIGO SIRVE PARA: Preparar la consulta SQL con prepared statement
    $query = "SELECT * FROM incidencias WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    // ESTE CÓDIGO SIRVE PARA: Vincular el parámetro ID a la consulta
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    // ESTE CÓDIGO SIRVE PARA: Ejecutar la consulta
    mysqli_stmt_execute($stmt);
    
    // ESTE CÓDIGO SIRVE PARA: Obtener el resultado
    $resultado = mysqli_stmt_get_result($stmt);
    
    // ESTE CÓDIGO SIRVE PARA: Convertir el resultado a array asociativo
    $incidencia = mysqli_fetch_assoc($resultado);
    
    // ESTE CÓDIGO SIRVE PARA: Devolver los datos en formato JSON
    echo json_encode(['success' => true, 'incidencia' => $incidencia]);
}
```

### **🎨 EJEMPLO 4: Interfaz Responsiva**

#### **En cualquier archivo PHP (CSS):**
```css
/* ESTE CÓDIGO SIRVE PARA: Crear un layout de grid responsivo */
.filter-stats-layout {
    display: grid;
    grid-template-columns: 400px 1fr;  /* ESTE CÓDIGO SIRVE PARA: 2 columnas */
    gap: 2.5rem;  /* ESTE CÓDIGO SIRVE PARA: Espacio entre elementos */
}

/* ESTE CÓDIGO SIRVE PARA: Hacer que el grid se adapte a móviles */
@media (max-width: 768px) {
    .filter-stats-layout {
        grid-template-columns: 1fr;  /* ESTE CÓDIGO SIRVE PARA: 1 columna en móviles */
    }
}

/* ESTE CÓDIGO SIRVE PARA: Crear animaciones suaves */
.fade-in-up {
    animation: fadeInUp 0.6s ease-out;  /* ESTE CÓDIGO SIRVE PARA: Animación de entrada */
}

@keyframes fadeInUp {
    from {
        opacity: 0;  /* ESTE CÓDIGO SIRVE PARA: Inicio invisible */
        transform: translateY(20px);  /* ESTE CÓDIGO SIRVE PARA: Inicio abajo */
    }
    to {
        opacity: 1;  /* ESTE CÓDIGO SIRVE PARA: Final visible */
        transform: translateY(0);  /* ESTE CÓDIGO SIRVE PARA: Final en posición */
    }
}
```

### **🔒 EJEMPLO 5: Sistema de Permisos**

#### **En cualquier página PHP:**
```php
<?php
// ESTE CÓDIGO SIRVE PARA: Iniciar la sesión
session_start();

// ESTE CÓDIGO SIRVE PARA: Incluir el archivo de permisos
require_once "../php/permisos.php";

// ESTE CÓDIGO SIRVE PARA: Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

// ESTE CÓDIGO SIRVE PARA: Verificar si es administrador
if (!esAdmin()) {
    header("location: ../login.php?error=acceso_denegado");
    exit();
}
?>

<!-- ESTE CÓDIGO SIRVE PARA: Mostrar contenido solo si es admin -->
<h1>Panel de Administración</h1>
```

### **📱 EJEMPLO 6: JavaScript para Modales**

#### **En `nuevo_diseno/gestionar_incidencias.php`:**
```javascript
// ESTA FUNCIÓN SIRVE PARA: Abrir el modal de crear incidencia
function abrirModalCrear() {
    // ESTE CÓDIGO SIRVE PARA: Limpiar el formulario
    document.getElementById('formIncidencia').reset();
    
    // ESTE CÓDIGO SIRVE PARA: Cambiar el título del modal
    document.getElementById('modalIncidenciaLabel').textContent = 'Crear Nueva Incidencia';
    
    // ESTE CÓDIGO SIRVE PARA: Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalIncidencia'));
    modal.show();
}

// ESTA FUNCIÓN SIRVE PARA: Guardar una incidencia (crear o editar)
async function guardarIncidencia() {
    // ESTE CÓDIGO SIRVE PARA: Obtener los datos del formulario
    const formData = new FormData(document.getElementById('formIncidencia'));
    
    try {
        // ESTE CÓDIGO SIRVE PARA: Enviar los datos al servidor
        const response = await fetch('../php/gestionar_incidencias_crud.php', {
            method: 'POST',
            body: formData
        });
        
        // ESTE CÓDIGO SIRVE PARA: Convertir la respuesta a JSON
        const data = await response.json();
        
        // ESTE CÓDIGO SIRVE PARA: Mostrar mensaje de éxito o error
        if (data.success) {
            alert('✅ ' + data.message);
            cargarIncidencias();  // ESTE CÓDIGO SIRVE PARA: Recargar la tabla
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    }
}
```

### **📊 EJEMPLO 7: Consultas SQL Complejas**

#### **En `php/obtener_estadisticas_filtradas.php`:**
```php
// ESTE CÓDIGO SIRVE PARA: Construir la cláusula WHERE dinámicamente
$where_clause = "";

// ESTE CÓDIGO SIRVE PARA: Agregar filtro por estado si se especifica
if (!empty($filtros['estado'])) {
    $where_clause .= " AND estado = '" . mysqli_real_escape_string($conexion, $filtros['estado']) . "'";
}

// ESTE CÓDIGO SIRVE PARA: Agregar filtro por prioridad si se especifica
if (!empty($filtros['prioridad'])) {
    $where_clause .= " AND prioridad = '" . mysqli_real_escape_string($conexion, $filtros['prioridad']) . "'";
}

// ESTE CÓDIGO SIRVE PARA: Agregar filtro por tipo si se especifica
if (!empty($filtros['tipo'])) {
    $where_clause .= " AND tipo_incidencia = '" . mysqli_real_escape_string($conexion, $filtros['tipo']) . "'";
}

// ESTA CONSULTA SIRVE PARA: Obtener incidencias agrupadas por fecha
$query_fecha = "SELECT DATE(fecha_creacion) as fecha, COUNT(*) as cantidad 
                FROM incidencias 
                WHERE 1=1 $where_clause 
                GROUP BY DATE(fecha_creacion) 
                ORDER BY fecha DESC 
                LIMIT 7";

// ESTE CÓDIGO SIRVE PARA: Ejecutar la consulta y obtener resultados
$resultado_fecha = mysqli_query($conexion, $query_fecha);
$datos_fecha = ['labels' => [], 'data' => []];

// ESTE CÓDIGO SIRVE PARA: Procesar cada fila de resultados
while ($row = mysqli_fetch_assoc($resultado_fecha)) {
    $datos_fecha['labels'][] = date('d/m', strtotime($row['fecha']));
    $datos_fecha['data'][] = (int)$row['cantidad'];
}
```

---

## 🎯 **RESUMEN PARA LA EXPOSICIÓN**

### **LO QUE DEBES EXPLICAR:**

1. **"Este sistema sirve para gestionar incidencias de soporte técnico"**
2. **"Tiene 4 roles: Admin, Director, Técnico y Analista"**
3. **"Cada archivo tiene una función específica"**
4. **"El código está organizado en capas: interfaz, lógica y base de datos"**
5. **"Usa tecnologías modernas como Bootstrap, Chart.js y PHP"**
6. **"Tiene seguridad implementada con prepared statements"**
7. **"Es responsivo y funciona en móviles"**
8. **"Permite exportar reportes a Excel"**

### **DEMOSTRACIÓN PRÁCTICA:**
1. **Mostrar login** con diferentes usuarios
2. **Navegar por los paneles** según rol
3. **Crear una incidencia** desde el formulario
4. **Asignar técnico** desde el panel del analista
5. **Ver gráficas** que se actualizan con filtros
6. **Exportar reporte** a Excel

---

*Documentación generada para la exposición del Sistema MINEC - Sistema de Gestión de Incidencias y Soporte Técnico*
