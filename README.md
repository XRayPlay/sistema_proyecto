# 🏢 Sistema de Gestión de Incidencias - MINEC

## 📋 Descripción del Proyecto

Sistema web completo para la gestión de incidencias técnicas del Ministerio de Ecosocialismo y Aguas (MINEC). Permite la administración de incidencias, técnicos, usuarios y generación de reportes estadísticos.

## 🚀 Características Principales

- ✅ **Gestión completa de incidencias** (CRUD)
- ✅ **Gestión de técnicos** con estados de disponibilidad
- ✅ **Gestión de usuarios/analistas**
- ✅ **Dashboard con estadísticas en tiempo real**
- ✅ **Filtros y búsquedas avanzadas**
- ✅ **Exportación de reportes a Excel**
- ✅ **Sistema de roles y permisos**
- ✅ **Interfaz moderna y responsiva**

## 🔐 Credenciales de Acceso

### 👨‍💼 Administrador
- **Usuario:** `admin`
- **Contraseña:** `Admin45*` (desde SQL) o `admin123` (desde PHP)
- **Rol:** Administrador
- **Permisos:** Acceso completo a todas las funcionalidades

### 👨‍💼 Director
- **Usuario:** `director`
- **Contraseña:** `director123`
- **Rol:** Director
- **Permisos:** Gestión de incidencias, técnicos y estadísticas

### 🔧 Técnico
- **Usuario:** `tecnico`
- **Contraseña:** `password` (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
- **Rol:** Técnico
- **Permisos:** Gestión de incidencias asignadas

### 📊 Analista
- **Usuario:** `analista`
- **Contraseña:** `password` (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
- **Rol:** Analista
- **Permisos:** Visualización de reportes y estadísticas

## 🛠️ Instalación y Configuración

### Requisitos del Sistema
- **Servidor Web:** Apache 2.4+
- **PHP:** 7.4 o superior
- **Base de Datos:** MySQL 5.7 o MariaDB 10.3+
- **Extensiones PHP:** mysqli, json, session

### Pasos de Instalación

1. **Clonar/Descargar el proyecto**
   ```bash
   # Colocar en el directorio del servidor web
   # Ejemplo: C:\xampp\htdocs\sistema_proyecto
   ```

2. **Configurar la base de datos**
   ```bash
   # Importar el archivo de base de datos principal
   mysql -u root -p < db/sistema_proyecto.sql
   
   # Importar usuarios y datos de ejemplo (opcional)
   mysql -u root -p < db/crear_usuarios_completos.sql
   ```

3. **Configurar conexión a la base de datos**
   ```php
   // Editar php/conexion_be.php si es necesario
   $host = 'localhost';
   $dbname = 'sistema_proyecto';
   $username = 'root';
   $password = '';
   ```

4. **Acceder al sistema**
   ```
   http://localhost/sistema_proyecto/login.php
   ```

## 🚀 Acceso Rápido

### Credenciales Simplificadas
- **Admin:** `admin` / `Admin45*`
- **Director:** `director` / `director123`
- **Técnico:** `tecnico` / `password`
- **Analista:** `analista` / `password`

### Datos de Ejemplo Incluidos
- ✅ 5 técnicos de ejemplo
- ✅ 7 incidencias de prueba
- ✅ 9 tipos de incidencias predefinidos
- ✅ Estados y prioridades configurados

## 📁 Estructura del Proyecto

```
sistema_proyecto/
├── nuevo_diseno/              # Interfaz principal
│   ├── inicio_completo.php    # Dashboard principal
│   ├── gestionar_incidencias.php
│   ├── gestionar_tecnicos.php
│   ├── panel_usuarios.php
│   └── tecnicos/
│       └── dashboard_tecnico.php
├── php/                       # Lógica backend
│   ├── clases.php            # Clases principales
│   ├── permisos.php          # Control de permisos
│   ├── gestionar_incidencias_crud.php
│   ├── gestionar_tecnicos_crud.php
│   └── panel_usuarios_crud.php
├── db/                        # Base de datos
│   └── sistema_proyecto.sql  # Script de base de datos
├── login.php                  # Punto de entrada
└── index.php                  # Redirección principal
```

## 🎯 Funcionalidades por Rol

### 👨‍💼 Administrador
- ✅ Gestión completa de usuarios
- ✅ Gestión completa de técnicos
- ✅ Gestión completa de incidencias
- ✅ Acceso a todas las estadísticas
- ✅ Configuración del sistema

### 👨‍💼 Director
- ✅ Gestión de incidencias
- ✅ Gestión de técnicos
- ✅ Visualización de estadísticas
- ✅ Reportes y análisis

### 🔧 Técnico
- ✅ Ver incidencias asignadas
- ✅ Actualizar estado de incidencias
- ✅ Agregar comentarios técnicos
- ✅ Exportar reportes de trabajo

### 📊 Analista
- ✅ Visualizar reportes
- ✅ Acceder a estadísticas
- ✅ Exportar datos

## 📊 Tipos de Incidencias

El sistema incluye 9 tipos de incidencias predefinidos:

1. **Hardware** - Problemas con equipos físicos
2. **Software** - Instalación y configuración de programas
3. **Internet/Red** - Problemas de conectividad
4. **Email** - Configuración de correo electrónico
5. **Impresoras** - Instalación y problemas de impresión
6. **Sistema** - Problemas con Windows y actualizaciones
7. **Seguridad** - Antivirus y problemas de seguridad
8. **Configuración de Equipo** - Ajustes de equipos de cómputo
9. **Otros** - Cualquier otro problema no clasificado

## 🔄 Estados de Incidencias

- **Pendiente** - Recién creada, esperando asignación
- **Asignada** - Asignada a un técnico
- **En Proceso** - Técnico trabajando en la solución
- **Resuelta** - Problema solucionado
- **Cerrada** - Incidencia cerrada definitivamente

## 📈 Dashboard y Estadísticas

El dashboard principal incluye:

- **Tarjetas de resumen** con métricas clave
- **Gráfica de incidencias por fecha** (últimos 7 días)
- **Gráfica de incidencias por tipo**
- **Lista de técnicos disponibles**
- **Filtros dinámicos** para análisis
- **Exportación de reportes**

## 🛡️ Seguridad

- **Hash de contraseñas** con `password_hash()`
- **Prepared statements** contra SQL injection
- **Validación de sesiones** en cada página
- **Control de acceso por roles**
- **Sanitización de datos** de entrada

## 🎨 Tecnologías Utilizadas

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos modernos
- **Bootstrap 5** - Framework responsivo
- **JavaScript ES6+** - Interactividad
- **Chart.js** - Gráficas dinámicas
- **Font Awesome** - Iconografía

### Backend
- **PHP 7.4+** - Lógica del servidor
- **MySQL** - Base de datos
- **POO** - Programación orientada a objetos
- **AJAX** - Comunicación asíncrona

## 📞 Soporte Técnico

Para soporte técnico o reportar problemas:

- **Email:** soporte@minec.gob.ve
- **Teléfono:** +58 212-555-0123
- **Horario:** Lunes a Viernes, 8:00 AM - 5:00 PM

## 📝 Notas de Versión

### Versión 1.0.0
- ✅ Sistema completo de gestión de incidencias
- ✅ Dashboard con estadísticas en tiempo real
- ✅ Gestión de usuarios, técnicos e incidencias
- ✅ Sistema de roles y permisos
- ✅ Exportación de reportes
- ✅ Interfaz moderna y responsiva

## 🔄 Actualizaciones Futuras

- [ ] Notificaciones en tiempo real
- [ ] API REST para integraciones
- [ ] App móvil para técnicos
- [ ] Sistema de tickets avanzado
- [ ] Integración con Active Directory

---

**Desarrollado para el Ministerio de Ecosocialismo y Aguas (MINEC)**  
**Versión:** 1.0.0  
**Fecha:** Enero 2025  
**Estado:** ✅ Listo para Producción
