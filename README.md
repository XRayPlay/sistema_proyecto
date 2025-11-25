# 🏢 Sistema de Gestión de Incidencias (MINEC)

## 📋 Descripción del Proyecto

Sistema web completo **PHP procedural + MySQL** diseñado para la **gestión de incidencias (CAU)** del Ministerio de Ecosocialismo y Aguas (**MINEC**). Ofrece una interfaz moderna y responsiva con un robusto control de acceso basado en roles para la gestión eficiente de problemas técnicos, asignación de tareas, y generación de reportes estadísticos.

## 🚀 Características Principales

* ✅ **Gestión completa de incidencias** (CRUD) con seguimiento de estados y prioridades.
* ✅ **Control de Acceso por Roles** (Admin, Director, Técnico, Analista, Usuario).
* ✅ **Gestión de técnicos y analistas** (CRUD) con estados de disponibilidad.
* ✅ **Dashboard con estadísticas** y filtros avanzados.
* ✅ **Exportación de reportes** a formato `.xlsx` (con Composer) o fallback a `.csv`.
* ✅ **Interfaz moderna** alojada en `nuevo_diseno/`.

---

## 🔐 Roles y Credenciales de Acceso

El sistema utiliza distintos roles (`id_rol`) para controlar el acceso a las funcionalidades.

| ID Rol | Rol | Permisos Principales | Redirección Post-Login | Credenciales Simplificadas |
| :---: | :--- | :--- | :--- | :--- |
| **1** | **Admin** | Acceso completo: gestión de todo. | `nuevo_diseno/inicio_completo.php` | `12345678` / `Admin45*` |
| **2** | **Director** | Gestión de incidencias, técnicos y estadísticas. | `nuevo_diseno/inicio_completo.php` | `87654321` / `password` |
| **3** | **Técnico** | Ver y actualizar incidencias asignadas. | `nuevo_diseno/tecnicos/dashboard_tecnico.php` | `12312312` / `password` |
| **4** | **Analista** | Visualización de reportes y estadísticas. | `nuevo_diseno/gestionar_incidencias.php` | `12345612` / `password` |

---

## 🛠️ Instalación y Configuración

### 1. Requisitos del Sistema

* **Servidor Web:** Apache (XAMPP, WAMP, LAMP)
* **PHP:** `>= 7.4` (Recomendado `PHP 8.x`)
* **Base de Datos:** MySQL / MariaDB
* **Extensiones PHP Requeridas:** `mysqli`, `mbstring`, `fileinfo`, `zip`, `xml`.
* **Herramienta Opcional:** [Composer](https://getcomposer.org/) (Necesario para exportación `.xlsx` con PhpSpreadsheet).

### 2. Pasos de Instalación Rápida (XAMPP Local)

1.  **Clonar o copiar** el proyecto en el directorio público del servidor (ej. `C:\xampp\htdocs\sistema_proyecto`).
2.  **Importar la Base de Datos:**
    * Cargar el archivo `sistema_proyecto.sql` (ubicado en `db/` o la raíz) usando **phpMyAdmin** o MySQL CLI.
    * *(Opcional):* Importar datos de ejemplo y usuarios adicionales desde `db/crear_usuarios_completos.sql`.
3.  **Ajustar la Conexión:**
    * Editar el archivo `php/conexion_be.php` (o `php/config.php` según el proyecto) con sus credenciales de MySQL (host, usuario, password, nombre BD).
4.  **Permisos de Escritura:**
    * Asegurar que la carpeta `public/uploads/avatars` y la configuración `session.save_path` permitan la escritura.
5.  **Acceder:**
    * Reiniciar Apache/MySQL.
    * Acceder al sistema en `http://localhost/sistema_proyecto/login.php`.

### 3. Habilitar Exportación a `.xlsx` (Opcional - Recomendado)

Para generar reportes en formato nativo `.xlsx` (Excel) en lugar de CSV:

1.  **Instalar PHP CLI y Composer.** Asegurarse de que `php` y `composer` funcionen desde la terminal.
2.  **Ejecutar Composer** en la raíz del proyecto (`C:\xampp\htdocs\sistema_proyecto`):

    ```powershell
    composer require phpoffice/phpspreadsheet
    ```

3.  **Verificar Extensiones:** Asegúrese de que `extension=zip` y `extension=xml` estén descomentadas en su `php.ini`.
4.  El código detectará el autoloader (`vendor/autoload.php`) y usará PhpSpreadsheet automáticamente. **Si no se instala, hará *fallback* a CSV UTF-8 con BOM.**

---

## 📁 Estructura del Proyecto y Endpoints

El proyecto sigue una estructura modular, separando interfaces, lógica de negocio y recursos.

| Carpeta/Archivo | Descripción |
| :--- | :--- |
| `login.php` | Punto de entrada. Página de inicio de sesión y modal para crear incidencias. |
| `nuevo_diseno/` | Contiene las **interfaces modernas** (Dashboard, Gestión, Vistas por Rol). |
| `php/` | **Lógica del servidor.** Endpoints para CRUD, login, exportación y utilidades. |
| `public/` | Archivos **CSS y JS** públicos. |
| `assets/`, `resources/` | Librerías y recursos (FontAwesome, Chart.js, imágenes, etc.). |
| `sistema_proyecto.sql` | Volcado principal de la base de datos. |

### Endpoints Principales (Lógica BE)

| Archivo | Función |
| :--- | :--- |
| `php/login_usuario_be.php` | Procesa el login (acepta `usuario` o cédula + `password`). |
| `php/gestionar_incidencias_crud.php` | **CRUD para Incidencias** (crear/obtener/actualizar/eliminar). |
| `php/panel_usuarios_crud.php` | **CRUD para Analistas/Usuarios** (crear/editar/eliminar/obtener). |
| `php/actualizar_mi_cuenta.php` | Permite al usuario logueado ver y actualizar su perfil (GET/POST). |
| `php/exportar_incidencias_excel.php` | Exportación de datos de incidencias (usa PhpSpreadsheet o CSV). |

---

## ⚠️ Seguridad y Notas Importantes

* **Contraseñas:** El sistema utiliza **Hash de contraseñas** (`password_hash()`) y **Prepared Statements** para prevenir inyecciones SQL. Sin embargo, se incluyen hashes antiguos en el volcado SQL para compatibilidad inicial. **Se recomienda una migración total a `password_hash`/`password_verify`**.
* **Entorno de Producción:** Asegúrese de usar **HTTPS**, desactivar `display_errors` (debe estar `OFF`) y activar `log_errors` en el `php.ini`.
* **Correo Electrónico:** Para habilitar funciones de correo (ej. recuperar contraseña), configure `php.ini` (`sendmail_path`) o implemente un servicio SMTP desde código.

## 🐛 Problemas Comunes y Soluciones

| Problema | Solución |
| :--- | :--- |
| Error `composer not recognized` | Instalar Composer y añadir su ruta al `PATH` del sistema. |
| Export `.xlsx` genera clases desconocidas | Ejecutar `composer require phpoffice/phpspreadsheet` en la raíz del proyecto. |
| Falla al subir avatars/archivos | Revisar permisos de escritura en la carpeta `public/uploads/avatars`. |

---

## 💡 Contribuir / Extensiones Recomendadas

* **Refactorización:** Separar la lógica en controladores o migrar a un ORM para escalabilidad.
* **Estándares:** Migrar completamente a Composer para manejo de dependencias y adoptar el estándar PSR-4.

**Desarrollado para el Ministerio de Ecosocialismo y Aguas (MINEC)**  
**Versión:** 1.0.0  
**Fecha:** 10 de noviembre de 2025