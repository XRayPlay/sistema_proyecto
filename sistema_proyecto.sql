-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-10-2025 a las 06:23:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_proyecto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id_cargo`, `code`, `name`, `description`) VALUES
(1, 'Sup', 'Soporte', 'Departamento de Soporte'),
(2, 'Sist', 'Sistema', 'Departamento de Sistemas'),
(3, 'Red', 'Redes', 'Departamento de Redes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `floors`
--

CREATE TABLE `floors` (
  `id_floors` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `floors`
--

INSERT INTO `floors` (`id_floors`, `code`, `name`, `description`) VALUES
(1, 'PB', 'Planta Baja', 'Planta Baja');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `incidencias`
--

INSERT INTO `incidencias` (`id`, `fecha_creacion`, `solicitante_nombre`, `solicitante_cedula`, `solicitante_email`, `solicitante_telefono`, `solicitante_direccion`, `solicitante_extension`, `tipo_incidencia`, `departamento`, `descripcion`, `prioridad`, `estado`, `tecnico_asignado`, `fecha_asignacion`, `fecha_resolucion`, `comentarios_tecnico`, `created_at`, `updated_at`) VALUES
(26, '2025-09-02 02:32:16', 'jennifer', '30990123', 'jennifer@gmail.com', '0427368940', 'Caracas,Caricuao', '5', 'Configuración de Equipo', 'gestion humana ', 'se volvio a dañar ', 'alta', 'asignada', 3, '2025-10-06 23:08:42', NULL, '\n\n--- Asignación por Administrador ---\nAsignado por administrador\n\n--- Asignación de Prueba ---\nAsignado para prueba del sistema', '2025-09-02 02:32:16', '2025-10-06 23:08:42'),
(33, '2025-10-07 11:59:26', 'Juan Diego', '29617414', 'juandiego@gmail.com', '04123931364', 'Bienestar', '17', 'General', 'General', 'No hay internet', 'media', 'asignada', 3, '2025-10-07 12:44:28', NULL, '\n\n--- Asignación por Administrador ---\nAsignado por administrador\n\n--- Asignación por Administrador ---\nAsignado por administrador\n\n--- Asignación por Administrador ---\nAsignado por administrador\n\n--- Asignación por Administrador ---\nAsignado por administrador\n\n--- Asignación por Administrador ---\nAsignado por administrador\n\n--- Asignación por Administrador ---\nAsignado por administrador', '2025-10-07 11:59:26', '2025-10-07 12:44:28'),
(34, '2025-10-08 01:08:59', 'Nicolas Carrillo ', '30990888', 'nicolas@gmail.com', '0412345345', '', '11', 'Seguridad', 'direccion de informatica ', 'restablecer contraseñas ', 'media', 'asignada', 3, NULL, NULL, NULL, '2025-10-08 01:08:59', '2025-10-10 01:20:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfomance_tecnico`
--

CREATE TABLE `perfomance_tecnico` (
  `id_perfomance_tecnico` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_tickects` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `report`
--

CREATE TABLE `report` (
  `id_report` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `problem` varchar(50) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_status_report` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reports_type`
--

CREATE TABLE `reports_type` (
  `id_reports_type` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reports_type`
--

INSERT INTO `reports_type` (`id_reports_type`, `name`, `description`) VALUES
(1, 'Hardware', 'Problemas con computadoras, impresoras, monitores y equipos físicos'),
(2, 'Software', 'Instalación, configuración y problemas con programas y aplicaciones'),
(3, 'Internet/Red', 'Problemas de conectividad, WiFi y acceso a internet'),
(4, 'Email', 'Configuración y problemas con correo electrónico'),
(5, 'Impresoras', 'Instalación, configuración y problemas con impresoras'),
(6, 'Sistema', 'Problemas con Windows, actualizaciones y configuración del sistema'),
(7, 'Seguridad', 'Antivirus, contraseñas y problemas de seguridad'),
(8, 'Configuración de Equipo', 'Configuración y ajustes de equipos de computo'),
(9, 'Otros', 'Cualquier otro problema no clasificado en las categorías anteriores');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_roles` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_roles`, `code`, `name`, `description`) VALUES
(1, 'Admin', 'Administrador', 'Administrador'),
(2, 'Direc', 'Director', 'Director'),
(3, 'Tecn', 'Tecnico', 'Empleado'),
(4, 'Anal', 'Analista', 'Analista del Sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_report`
--

CREATE TABLE `status_report` (
  `id_status_report` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `status_report`
--

INSERT INTO `status_report` (`id_status_report`, `code`, `name`, `description`) VALUES
(1, 'Asig.', 'Asignado', 'Se asigna un reporte a un tecnico disponible'),
(2, 'Proce.', 'En Proceso', 'El tecnico esta en proceso de solventar el problema'),
(3, 'Rediri.', 'Redirigido', 'El reporte es redirigido a otro departamento'),
(4, 'Cerr.', 'Cerrado', 'El problema esta resuelto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_user`
--

CREATE TABLE `status_user` (
  `id_status_user` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `status_user`
--

INSERT INTO `status_user` (`id_status_user`, `code`, `name`, `description`) VALUES
(1, 'Activ.', 'Activo', 'El usuario esta disponible'),
(2, 'Ocup.', 'Ocupado', 'El usuario esta ocupado'),
(3, 'Ausen.', 'Ausente', 'El usuario no se encuentra disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnicos`
--

CREATE TABLE `tecnicos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre completo del técnico',
  `especialidad` varchar(150) NOT NULL COMMENT 'Área de especialización',
  `email` varchar(100) DEFAULT NULL COMMENT 'Correo electrónico',
  `telefono` varchar(20) DEFAULT NULL COMMENT 'Número de teléfono',
  `password` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Vacaciones','Licencia') NOT NULL DEFAULT 'Activo' COMMENT 'Estado del técnico',
  `fecha_ingreso` date DEFAULT NULL COMMENT 'Fecha de ingreso al sistema',
  `nivel_experiencia` enum('Principiante','Intermedio','Avanzado','Experto') DEFAULT 'Intermedio' COMMENT 'Nivel de experiencia',
  `comentarios` text DEFAULT NULL COMMENT 'Observaciones adicionales',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado_disponibilidad` enum('Disponible','Ocupado') DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla para gestionar técnicos del sistema';

--
-- Volcado de datos para la tabla `tecnicos`
--

INSERT INTO `tecnicos` (`id`, `nombre`, `especialidad`, `email`, `telefono`, `password`, `estado`, `fecha_ingreso`, `nivel_experiencia`, `comentarios`, `created_at`, `updated_at`, `estado_disponibilidad`) VALUES
(11, 'Sergio Saiz', 'Soporte Técnico', 'sergi09@gmail.com', '0945869313', NULL, 'Activo', '2025-09-01', 'Intermedio', NULL, '2025-09-01 18:21:26', '2025-09-01 18:21:26', 'Disponible'),
(12, 'Técnico de Soporte', 'Soporte', 'tecnico1@sistema.com', '4129876543', NULL, 'Activo', '2025-09-01', 'Intermedio', NULL, '2025-09-01 19:30:15', '2025-09-01 19:30:15', 'Disponible'),
(14, 'Joiker Morales', 'Redes', 'Joiker28', '04241234567', '$2y$10$JvIshRoMAdp.fScYssyvuelgLUlurul/MXRwbcrWmQheDnrjr6pti', 'Activo', NULL, 'Intermedio', NULL, '2025-09-26 13:29:53', '2025-09-26 13:29:53', 'Disponible'),
(15, 'Jennifer Linarez', 'Sistema', 'mafer45', '3270507', '$2y$10$wj1eD7I0YCTEyqP8m4jZdO9JKoFmPIP3R5uZirnWZDUR8/vULc.QK', 'Activo', NULL, 'Intermedio', NULL, '2025-10-08 01:09:42', '2025-10-08 01:09:42', 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id_tickets` int(11) NOT NULL,
  `fecha_resuelto` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_report` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `cedula` bigint(9) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `phone` bigint(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `address` varchar(200) NOT NULL,
  `avatar` varchar(50) NOT NULL,
  `last_connection` date NOT NULL,
  `id_floor` int(11) DEFAULT NULL,
  `id_cargo` int(11) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `id_status_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id_user`, `username`, `pass`, `name`, `cedula`, `sexo`, `phone`, `email`, `birthday`, `address`, `avatar`, `last_connection`, `id_floor`, `id_cargo`, `id_rol`, `id_status_user`) VALUES
(3, 'tecnico1', '8d969eef6ecad3c29a3a', 'Técnico de Soporte', 87654321, 'Masculino', 4129876543, 'tecnico1@sistema.com', '1985-01-01', 'Departamento de Soporte, Piso 1', 'tecnico.png', '2025-10-08', 1, 1, 3, 1),
(14, 'admin', '7fcf4ba391c48784edde599889d6e3f1e47a27db36ecc050cc92f259bfac38afad2c68a1ae804d77075e8fb722503f3eca2b2c1006ee6f6c7b7628cb45fffd1d', 'Administrador del Sistema', 12345678, 'Masculino', 4121234567, 'admin@sistema.com', '1990-01-01', 'Sistema', 'default.jpg', '2025-10-10', NULL, NULL, 1, 1),
(17, 'director', '68870fbccbae596ee94ec691fffad29e49078fee4b3a3d56f588eca946787c8ad1285534e05b140bde019a971169d617430358f8294ecb4b2f1d95aa376b8bbb', 'Director General', 87654321, 'Masculino', 4129876543, 'director@sistema.com', '1985-01-01', 'Sistema', 'default.jpg', '2025-10-08', NULL, NULL, 2, 1),
(18, 'mafer45', '8d969eef6ecad3c29a3a', 'Mafer', 30990666, 'Femenino', 4129602525, 'mariblanco351@gmail.com', '2005-09-28', 'Caracas,Caricuao', 'default.jpg', '2025-10-10', NULL, NULL, 4, 1),
(24, '', '8d969eef6ecad3c29a3a', 'Jennifer Linarez', 48960543, '', 4241234567, 'mafer45', '0000-00-00', '', '', '0000-00-00', NULL, NULL, 3, 2),
(26, 'juan.test', '8d969eef6ecad3c29a3a', 'Juan Pérez Test', 0, 'No especif', 1234567890, 'juan.test@ejemplo.com', '1990-01-01', 'No especificado', 'default.jpg', '2025-10-10', NULL, NULL, 4, 1),
(28, 'hugo3030', '8d969eef6ecad3c29a3a', 'Hugo Pineda', 37876515, '', 4243931364, 'hugo3030@gmail.com', '0000-00-00', '', '', '2025-10-10', NULL, NULL, 3, 1),
(29, 'testdebug', '8d969eef6ecad3c29a3a', 'Test Analista Debug', 0, 'No especif', 1234567890, 'testdebug@ejemplo.com', '1990-01-01', 'No especificado', 'default.jpg', '2025-10-10', NULL, NULL, 4, 1),
(30, 'testfinal', '8d969eef6ecad3c29a3a', 'Test Analista Final', 0, 'No especif', 1234567890, 'testfinal@ejemplo.com', '1990-01-01', 'No especificado', 'default.jpg', '2025-10-10', NULL, NULL, 4, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Indices de la tabla `floors`
--
ALTER TABLE `floors`
  ADD PRIMARY KEY (`id_floors`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_incidencias_estado` (`estado`),
  ADD KEY `idx_incidencias_prioridad` (`prioridad`),
  ADD KEY `idx_incidencias_departamento` (`departamento`),
  ADD KEY `idx_incidencias_fecha` (`fecha_creacion`),
  ADD KEY `idx_incidencias_tecnico` (`tecnico_asignado`);

--
-- Indices de la tabla `perfomance_tecnico`
--
ALTER TABLE `perfomance_tecnico`
  ADD PRIMARY KEY (`id_perfomance_tecnico`),
  ADD KEY `id_tickets` (`id_tickects`),
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id_report`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_status_report` (`id_status_report`);

--
-- Indices de la tabla `reports_type`
--
ALTER TABLE `reports_type`
  ADD PRIMARY KEY (`id_reports_type`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_roles`);

--
-- Indices de la tabla `status_report`
--
ALTER TABLE `status_report`
  ADD PRIMARY KEY (`id_status_report`);

--
-- Indices de la tabla `status_user`
--
ALTER TABLE `status_user`
  ADD PRIMARY KEY (`id_status_user`);

--
-- Indices de la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_especialidad` (`especialidad`),
  ADD KEY `idx_nombre` (`nombre`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_tickets`),
  ADD KEY `id_report` (`id_report`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_floor` (`id_floor`),
  ADD KEY `id_status_user` (`id_status_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `floors`
--
ALTER TABLE `floors`
  MODIFY `id_floors` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `perfomance_tecnico`
--
ALTER TABLE `perfomance_tecnico`
  MODIFY `id_perfomance_tecnico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `report`
--
ALTER TABLE `report`
  MODIFY `id_report` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reports_type`
--
ALTER TABLE `reports_type`
  MODIFY `id_reports_type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_roles` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `status_report`
--
ALTER TABLE `status_report`
  MODIFY `id_status_report` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `status_user`
--
ALTER TABLE `status_user`
  MODIFY `id_status_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id_tickets` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `perfomance_tecnico`
--
ALTER TABLE `perfomance_tecnico`
  ADD CONSTRAINT `perfomance_tecnico_ibfk_1` FOREIGN KEY (`id_tickects`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `perfomance_tecnico_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `report_ibfk_3` FOREIGN KEY (`id_status_report`) REFERENCES `status_report` (`id_status_report`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`id_report`) REFERENCES `report` (`id_report`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_floor`) REFERENCES `floors` (`id_floors`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_3` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_roles`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_4` FOREIGN KEY (`id_status_user`) REFERENCES `status_user` (`id_status_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
