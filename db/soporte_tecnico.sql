-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-06-2025 a las 04:39:37
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
-- Base de datos: `soporte_tecnico`
--
CREATE DATABASE IF NOT EXISTS `soporte_tecnico` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `soporte_tecnico`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos_reparados`
--

CREATE TABLE `equipos_reparados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos_reparados`
--

INSERT INTO `equipos_reparados` (`id`, `nombre`, `fecha`) VALUES
(15, 'Mike', '2025-06-21'),
(16, 'Laptop HP', '2025-06-20'),
(17, 'Impresora Epson', '2025-06-19'),
(18, 'Monitor Samsung', '2025-06-18'),
(19, 'CPU Dell', '2025-06-17'),
(20, 'Tablet Lenovo', '2025-06-16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rendimiento`
--

CREATE TABLE `rendimiento` (
  `id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `reparaciones` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rendimiento`
--

INSERT INTO `rendimiento` (`id`, `tecnico_id`, `reparaciones`) VALUES
(1, 1, 14),
(2, 2, 11),
(3, 3, 10),
(4, 6, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnicos`
--

CREATE TABLE `tecnicos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo','ausente') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tecnicos`
--

INSERT INTO `tecnicos` (`id`, `nombre`, `estado`) VALUES
(1, 'Michael', 'activo'),
(2, 'Mike', 'inactivo'),
(3, 'Juan de las aguas termales', 'ausente'),
(4, 'Juan Pérez', 'activo'),
(5, 'Ana Gómez', 'inactivo'),
(6, 'Luis Torres', 'activo'),
(7, 'Carlos Ruiz', 'inactivo'),
(8, 'María León', 'ausente'),
(9, 'José García', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipos_reparados`
--
ALTER TABLE `equipos_reparados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rendimiento`
--
ALTER TABLE `rendimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tecnico_id` (`tecnico_id`);

--
-- Indices de la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipos_reparados`
--
ALTER TABLE `equipos_reparados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `rendimiento`
--
ALTER TABLE `rendimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `rendimiento`
--
ALTER TABLE `rendimiento`
  ADD CONSTRAINT `rendimiento_ibfk_1` FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
