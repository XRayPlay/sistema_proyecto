# Sistema de Gestión de Incidencias (MINEC)

Proyecto PHP procedural + MySQL para la gestión de incidencias (CAU). Interfaz sencilla con distintos roles (Admin, Director, Técnico, Analista, Usuario) y CRUD para incidencias, técnicos y analistas.

## Contenido
- `login.php` — Página de inicio de sesión y modal para crear incidencias.
- `nuevo_diseno/` — Interfaces modernas (gestión, paneles, vistas por rol).
- `php/` — Endpoints y lógica del servidor (CRUD, login, export, utilidades).
- `public/` — CSS y JS públicos.
- `assets/`, `resources/` — Librerías y recursos (FontAwesome, imágenes).
- `sistema_proyecto.sql` — Volcado de la base de datos.

## Requisitos
- Windows / Linux / macOS con servidor web (XAMPP, WAMP, LAMP) con PHP >= 7.4 (recomendado PHP 8.x).
- Extensiones PHP: mysqli, mbstring, fileinfo, zip (necesaria para PhpSpreadsheet), xml.
- MySQL/MariaDB.
- Composer (opcional, para habilitar exportación .xlsx con PhpSpreadsheet).

## Instalación rápida (local con XAMPP)
1. Clona o copia el proyecto dentro de la carpeta pública de tu servidor (ej. `C:\xampp\htdocs\sistema_proyecto`).
2. Importa la base de datos desde `sistema_proyecto.sql` usando phpMyAdmin o MySQL CLI.
3. Ajusta la configuración de conexión en `php/conexion_be.php` o `php/config.php` según el proyecto (host, usuario, password, nombre BD).
4. Asegúrate de que `session.save_path` y permisos de carpetas `public/uploads/avatars` permitan escritura.
5. Reinicia Apache desde el panel de XAMPP.

## Endpoints principales
- `php/login_usuario_be.php` — Procesa el login (acepta `usuario` o cédula + `password`).
- `php/panel_usuarios_crud.php` — CRUD para analistas (crear/editar/eliminar/obtener).
- `php/gestionar_incidencias_crud.php` — CRUD para incidencias (crear/obtener/actualizar/eliminar) con controles por rol.
- `php/actualizar_mi_cuenta.php` — Endpoint para que el usuario logueado vea y actualice su perfil (GET/POST).
- `php/exportar_incidencias_excel.php`, `php/exportar_todas_incidencias_excel.php` — Exportación: intentan usar PhpSpreadsheet para `.xlsx`, si no existe hacen fallback a CSV UTF-8 con BOM.

## Roles y comportamiento importante
- id_rol = 1 : Admin — acceso completo, puede editar su propio perfil desde `nuevo_diseno/mi_perfil.php`.
- id_rol = 2 : Director.
- id_rol = 3 : Técnico.
- id_rol = 4 : Analista — al iniciar sesión se redirige a `nuevo_diseno/gestionar_incidencias.php`; menú limitado a "Gestión de Incidencias".

## Validaciones implementadas
- Frontend: validaciones en `public/js/login.js` y formularios (longitudes, patrones). Login username ahora acepta 3–50 caracteres, sin espacios.
- Backend: validaciones server-side en los endpoints (ej. creación/edición de usuarios, validaciones de campos obligatorios y rangos).

## Habilitar exportación a .xlsx (opcional)
Si quieres que el sistema genere archivos .xlsx nativos de Excel en lugar de CSV:

1. Instala PHP CLI y añade la ruta de `php.exe` al PATH (Windows): normalmente `C:\xampp\php`.
2. Instala Composer (Windows: Composer-Setup) y asegúrate de que `composer` funcione en PowerShell/terminal.
3. En la raíz del proyecto (donde está `composer.json` o donde quieres crear uno) ejecuta:

```powershell
cd C:\xampp\htdocs\sistema_proyecto
composer require phpoffice/phpspreadsheet
```

4. Habilita extensiones requeridas en `php.ini` (por ejemplo `extension=zip`, `extension=xml`) y reinicia Apache.
5. El código detecta el autoloader `vendor/autoload.php` y usará PhpSpreadsheet si está presente.

Si no instalas PhpSpreadsheet, el sistema seguirá exportando en CSV con BOM (UTF-8) para evitar problemas de acentos.

## Configuración de correo (si aplica)
Actualmente el sistema tiene endpoints que pueden enviar correos (recuperar contraseña). Configura `php.ini` (sendmail_path) o usa un servicio SMTP desde código si quieres correo funcional.

## Seguridad y notas importantes
- Contraseñas heredadas usan hashes antiguos por compatibilidad; se recomienda plan de migración a `password_hash`/`password_verify`.
- Asegúrate de usar HTTPS en producción y revisar `display_errors` (debe estar OFF) y `log_errors` activado.
- Revisa permisos en carpetas públicas (evitar subir ejecutables como `.php`).

## Problemas comunes y soluciones
- Error `composer not recognized` → instala Composer y añade a PATH.
- Error `php not recognized` → añade `C:\xampp\php` al PATH y reinicia la terminal.
- Export .xlsx genera clases desconocidas → ejecutar `composer require phpoffice/phpspreadsheet`.

## Cómo probar rápidamente
1. Importa la base de datos.
2. Crea usuarios de prueba (o usa `php/crear_admin_director.php`).
3. Accede a `http://localhost/sistema_proyecto/login.php`.
4. Inicia como Admin, prueba `Mi Perfil`, crear/editar analistas, crear incidencias.

## Contribuir / Extensiones recomendadas
- Separar la lógica en controladores/ORM si se pretende escalar.
- Migrar a Composer para manejar dependencias y PSR-4.

---
Si quieres, puedo:
- Mover el CSS inline del menú a `assets/css/index.css`.
- Añadir instrucciones detalladas para despliegue en producción.
- Generar una guía de migración a password_hash.

Fecha: 10 de noviembre de 2025
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
