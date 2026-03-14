-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-03-2026 a las 03:53:46
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
-- Base de datos: `sistema_minec`
--
CREATE DATABASE IF NOT EXISTS `sistema_minec` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sistema_minec`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id_cargo`, `code`, `name`, `description`) VALUES
(1, 'Sup', 'Soporte', 'Departamento de Soporte'),
(2, 'Sist', 'Sistemas', 'Departamento de Sistemas'),
(3, 'Red', 'Redes', 'Departamento de Redes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `floors`
--

CREATE TABLE `floors` (
  `id_floors` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(10) NOT NULL,
  `description` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `floors`
--

INSERT INTO `floors` (`id_floors`, `code`, `name`, `description`) VALUES
(1, 'PC', 'Plaza Cara', 'Plaza Caracas'),
(2, 'P3', 'Piso 3', 'Piso 3'),
(3, 'P4', 'Piso 4', 'Piso 4'),
(4, 'P5', 'Piso 5', 'Piso 5'),
(5, 'P6', 'Piso 6', 'Piso 6'),
(6, 'P7', 'Piso 7', 'Piso 7'),
(7, 'P8', 'Piso 8', 'Piso 8'),
(8, 'P9', 'Piso 9', 'Piso 9'),
(9, 'P10', 'Piso 10', 'Piso 10'),
(10, 'P11', 'Piso 11', 'Piso 11'),
(11, 'P12', 'Piso 12', 'Piso 12'),
(12, 'P13', 'Piso 13', 'Piso 13'),
(13, 'P14', 'Piso 14', 'Piso 14'),
(14, 'P15', 'Piso 15', 'Piso 15'),
(15, 'P16', 'Piso 16', 'Piso 16'),
(16, 'P17', 'Piso 17', 'Piso 17'),
(17, 'P18', 'Piso 18', 'Piso 18'),
(18, 'P19', 'Piso 19', 'Piso 19'),
(19, 'P20', 'Piso 20', 'Piso 20'),
(20, 'P21', 'Piso 21', 'Piso 21'),
(21, 'P22', 'Piso 22', 'Piso 22'),
(22, 'P23', 'Piso 23', 'Piso 23'),
(23, 'P24', 'Piso 24', 'Piso 24'),
(24, 'P25', 'Piso 25', 'Piso 25'),
(25, 'P26', 'Piso 26', 'Piso 26'),
(26, 'P27', 'Piso 27', 'Piso 27'),
(27, 'P28', 'Piso 28', 'Piso 28'),
(28, 'P29', 'Piso 29', 'Piso 29'),
(29, 'P30', 'Piso 30', 'Piso 30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id_incidencias` int(11) NOT NULL,
  `tipo_incidencia` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `status_incidencia` int(11) NOT NULL,
  `usuario_creador` int(11) NOT NULL,
  `tecnico_asignado` int(11) NOT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `fecha_resolucion` date DEFAULT NULL,
  `comentarios_tecnico` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incident_type`
--

CREATE TABLE `incident_type` (
  `id_incident_type` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `id_cargo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `incident_type`
--

INSERT INTO `incident_type` (`id_incident_type`, `name`, `description`, `id_cargo`) VALUES
(1, 'Hardware', 'Problemas con computadoras, impresoras, monitores y equipos físicos', 1),
(2, 'Software', 'Instalación, configuración y problemas con programas y aplicaciones', 2),
(3, 'Internet/Red', 'Problemas de conectividad, WiFi y acceso a internet', 3),
(4, 'Email', 'Configuración y problemas con correo electrónico', 1),
(5, 'Impresoras', 'Instalación, configuración y problemas con impresoras', 1),
(6, 'Sistema', 'Problemas con Windows, actualizaciones y configuración del sistema', 2),
(7, 'Seguridad', 'Antivirus, contraseñas y problemas de seguridad', 1),
(8, 'Configuración de Equipo', 'Configuración y ajustes de equipos de computo', 1),
(9, 'Sistema', 'Olvido su contraseña', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `person`
--

CREATE TABLE `person` (
  `id_person` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `apellido` varchar(30) NOT NULL,
  `nacionalidad` varchar(1) NOT NULL,
  `cedula` int(8) NOT NULL,
  `sexo` varchar(9) DEFAULT NULL,
  `phone_code` int(4) NOT NULL,
  `phone` int(7) NOT NULL,
  `email` varchar(50) NOT NULL,
  `birthday` date NOT NULL,
  `id_floor` int(11) NOT NULL,
  `id_cargo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `person`
--

INSERT INTO `person` (`id_person`, `name`, `apellido`, `nacionalidad`, `cedula`, `sexo`, `phone_code`, `phone`, `email`, `birthday`, `id_floor`, `id_cargo`) VALUES
(1, 'Administrador', 'Sistema', 'V', 12345678, NULL, 412, 1234567, 'admin@admin.com', '2005-11-01', 2, NULL),
(2, 'Tecnico', 'Sistema', 'V', 12312312, NULL, 412, 1235656, 'tecnico@tecnico.com', '2000-01-01', 2, 1),
(3, 'Analista', 'Sistema', 'V', 12345612, NULL, 412, 1234545, 'analista@analista.com', '2004-02-01', 1, NULL),
(4, 'Director', 'Sistema', 'V', 87654321, NULL, 412, 1234594, 'director@director.com', '2000-11-01', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `code`, `name`, `description`) VALUES
(1, 'Admin', 'Administrador', 'Administrador'),
(2, 'Direc', 'Director', 'Director'),
(3, 'Tecn', 'Tecnico', 'Empleado'),
(4, 'Anali', 'Analista', 'Analista del Sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_incidencia`
--

CREATE TABLE `status_incidencia` (
  `id_status_incidencia` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `status_incidencia`
--

INSERT INTO `status_incidencia` (`id_status_incidencia`, `code`, `name`, `description`) VALUES
(1, 'Asig.', 'Asignado', 'Se asigna un reporte a un tecnico disponible'),
(2, 'Proce.', 'En Proceso', 'El tecnico esta en proceso de solventar el problema'),
(3, 'Rediri.', 'Redirigido', 'El reporte es redirigido a otro departamento'),
(4, 'Cerr.', 'Cerrado', 'El problema esta resuelto'),
(5, 'Certf.', 'Certificar', 'La incidencia esta certificada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_user`
--

CREATE TABLE `status_user` (
  `id_status_user` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL
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
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `avatar` varchar(50) NOT NULL,
  `last_connection` date NOT NULL,
  `id_person` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_status_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`id_incidencias`),
  ADD KEY `id_incidencias` (`tipo_incidencia`),
  ADD KEY `id_status_incidencia` (`status_incidencia`),
  ADD KEY `id_person` (`usuario_creador`),
  ADD KEY `person_id` (`tecnico_asignado`);

--
-- Indices de la tabla `incident_type`
--
ALTER TABLE `incident_type`
  ADD PRIMARY KEY (`id_incident_type`),
  ADD KEY `id_cargo` (`id_cargo`);

--
-- Indices de la tabla `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`id_person`),
  ADD KEY `id_floor` (`id_floor`),
  ADD KEY `id_cargo` (`id_cargo`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `status_incidencia`
--
ALTER TABLE `status_incidencia`
  ADD PRIMARY KEY (`id_status_incidencia`);

--
-- Indices de la tabla `status_user`
--
ALTER TABLE `status_user`
  ADD PRIMARY KEY (`id_status_user`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_status_user` (`id_status_user`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_person` (`id_person`);

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
  MODIFY `id_floors` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id_incidencias` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incident_type`
--
ALTER TABLE `incident_type`
  MODIFY `id_incident_type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `person`
--
ALTER TABLE `person`
  MODIFY `id_person` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `status_incidencia`
--
ALTER TABLE `status_incidencia`
  MODIFY `id_status_incidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `status_user`
--
ALTER TABLE `status_user`
  MODIFY `id_status_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `id_incident_type` FOREIGN KEY (`tipo_incidencia`) REFERENCES `incident_type` (`id_incident_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_status_incidencia` FOREIGN KEY (`status_incidencia`) REFERENCES `status_incidencia` (`id_status_incidencia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tecnico_asignado` FOREIGN KEY (`tecnico_asignado`) REFERENCES `person` (`id_person`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_creador` FOREIGN KEY (`usuario_creador`) REFERENCES `person` (`id_person`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `incident_type`
--
ALTER TABLE `incident_type`
  ADD CONSTRAINT `id_cargo	` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `person`
--
ALTER TABLE `person`
  ADD CONSTRAINT `id_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_floor` FOREIGN KEY (`id_floor`) REFERENCES `floors` (`id_floors`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `id_person` FOREIGN KEY (`id_person`) REFERENCES `person` (`id_person`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_status_user` FOREIGN KEY (`id_status_user`) REFERENCES `status_user` (`id_status_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
