-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2026 at 03:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recolix`
--
DROP DATABASE IF EXISTS `recolix`;
CREATE DATABASE IF NOT EXISTS `recolix` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `recolix`;

-- --------------------------------------------------------

--
-- Table structure for table `camion`
--

DROP TABLE IF EXISTS `camion`;
CREATE TABLE `camion` (
  `id_camion` int(11) NOT NULL,
  `matricula` varchar(15) NOT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `tipo_carga` varchar(40) DEFAULT NULL,
  `estado` enum('disponible','en_ruta','en_mantenimiento','de_baja','en_deposito') NOT NULL DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `camion_centro`
--

DROP TABLE IF EXISTS `camion_centro`;
CREATE TABLE `camion_centro` (
  `id_camion` int(11) NOT NULL,
  `id_centro` int(11) NOT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `peso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `centro_acopio`
--

DROP TABLE IF EXISTS `centro_acopio`;
CREATE TABLE `centro_acopio` (
  `id_centro` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('ecocentro','planta_clasificacion','relleno_sanitario','vertedero') DEFAULT NULL,
  `calle` varchar(100) DEFAULT NULL,
  `num_calle` int(11) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `capacidad_total` int(11) DEFAULT NULL,
  `estado` enum('operativo','saturado','cerrado') NOT NULL DEFAULT 'operativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `centro_maquinaria`
--

DROP TABLE IF EXISTS `centro_maquinaria`;
CREATE TABLE `centro_maquinaria` (
  `id_centro` int(11) NOT NULL,
  `id_maquinaria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `centro_residuo`
--

DROP TABLE IF EXISTS `centro_residuo`;
CREATE TABLE `centro_residuo` (
  `id_centro` int(11) NOT NULL,
  `id_residuo` int(11) NOT NULL,
  `ocupacion_actual` int(11) DEFAULT 0,
  `capacidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `centro_usuario`
--

DROP TABLE IF EXISTS `centro_usuario`;
CREATE TABLE `centro_usuario` (
  `id_centro` int(11) NOT NULL,
  `ci_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `circuito`
--

DROP TABLE IF EXISTS `circuito`;
CREATE TABLE `circuito` (
  `id_circuito` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `actividad` varchar(60) DEFAULT NULL,
  `turno` enum('mañana','tarde','noche') DEFAULT NULL,
  `frecuencia` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contenedor`
--

DROP TABLE IF EXISTS `contenedor`;
CREATE TABLE `contenedor` (
  `id_contenedor` int(11) NOT NULL,
  `id_circuito` int(11) NOT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `calle` varchar(100) DEFAULT NULL,
  `num_calle` int(11) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `nivel_llenado` int(11) DEFAULT 0,
  `estado` enum('funcional','roto','desbordado','inactivo','en_deposito') NOT NULL DEFAULT 'funcional',
  `fecha_reparacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contenedor_levante`
--

DROP TABLE IF EXISTS `contenedor_levante`;
CREATE TABLE `contenedor_levante` (
  `id_levante` int(11) NOT NULL,
  `id_contenedor` int(11) NOT NULL,
  `estado_encontrado` enum('sobrepeso','riesgo_de_incendio','acceso_bloqueado','contenedor_ausente','todo_ok','otro') DEFAULT NULL,
  `motivo` varchar(150) DEFAULT NULL,
  `hora_vaciado` datetime DEFAULT NULL,
  `peso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cuadrilla`
--

DROP TABLE IF EXISTS `cuadrilla`;
CREATE TABLE `cuadrilla` (
  `id_cuadrilla` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL,
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incidencia`
--

DROP TABLE IF EXISTS `incidencia`;
CREATE TABLE `incidencia` (
  `id_incidencia` int(11) NOT NULL,
  `ci_usuario` int(11) NOT NULL,
  `id_cuadrilla` int(11) DEFAULT NULL,
  `tipo` enum('rotura','desborde','falta_recoleccion','vandalismo','otro') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `prioridad` enum('baja','media','alta','critica') NOT NULL DEFAULT 'baja',
  `estado` enum('abierta','asignada','resuelta') NOT NULL DEFAULT 'abierta',
  `urgencia` tinyint(1) DEFAULT 0,
  `url_imagen` varchar(255) DEFAULT NULL,
  `fecha_apertura` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incidencia_contenedor`
--

DROP TABLE IF EXISTS `incidencia_contenedor`;
CREATE TABLE `incidencia_contenedor` (
  `id_incidencia` int(11) NOT NULL,
  `id_contenedor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `levante`
--

DROP TABLE IF EXISTS `levante`;
CREATE TABLE `levante` (
  `id_levante` int(11) NOT NULL,
  `id_camion` int(11) NOT NULL,
  `id_cuadrilla` int(11) NOT NULL,
  `id_ruta` int(11) NOT NULL,
  `hora_salida` datetime NOT NULL,
  `hora_llegada` datetime DEFAULT NULL,
  `total_recolectado` decimal(10,2) DEFAULT 0.00,
  `hora_levante` date DEFAULT NULL,
  `estado` enum('pendiente','en_curso','completado','cancelado') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mantenimiento`
--

DROP TABLE IF EXISTS `mantenimiento`;
CREATE TABLE `mantenimiento` (
  `id_mantenimiento` int(11) NOT NULL,
  `id_camion` int(11) NOT NULL,
  `tipo` enum('preventivo','correctivo') DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maquinaria`
--

DROP TABLE IF EXISTS `maquinaria`;
CREATE TABLE `maquinaria` (
  `id_maquinaria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('compactadora','cinta','pala','bascula','herramienta','otro') DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `estado` enum('operativa','en_mantenimiento','fuera_servicio') NOT NULL DEFAULT 'operativa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `municipio`
--

DROP TABLE IF EXISTS `municipio`;
CREATE TABLE `municipio` (
  `id_municipio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificacion`
--

DROP TABLE IF EXISTS `notificacion`;
CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo_origen` enum('contenedor','incidencia','levante','camion','centro_acopio') DEFAULT NULL,
  `prioridad` enum('informativa','advertencia','critica') NOT NULL DEFAULT 'informativa',
  `estado` enum('pendiente','leida','archivada') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_lectura` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificacion_usuario`
--

DROP TABLE IF EXISTS `notificacion_usuario`;
CREATE TABLE `notificacion_usuario` (
  `id_notificacion` int(11) NOT NULL,
  `ci_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prediccion_llenado`
--

DROP TABLE IF EXISTS `prediccion_llenado`;
CREATE TABLE `prediccion_llenado` (
  `id_prediccion` int(11) NOT NULL,
  `id_contenedor` int(11) NOT NULL,
  `nivel_proyectado` int(11) DEFAULT NULL,
  `fecha_calculo` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reporte`
--

DROP TABLE IF EXISTS `reporte`;
CREATE TABLE `reporte` (
  `id_reporte` int(11) NOT NULL,
  `ci_usuario` int(11) NOT NULL,
  `tipo` enum('pantalla','pdf','csv') NOT NULL DEFAULT 'pantalla',
  `descripcion` text DEFAULT NULL,
  `formato` varchar(20) DEFAULT 'PDF',
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruta_contenedor`
--

DROP TABLE IF EXISTS `ruta_contenedor`;
CREATE TABLE `ruta_contenedor` (
  `id_ruta` int(11) NOT NULL,
  `id_contenedor` int(11) NOT NULL,
  `orden_visita` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruta_cuadrilla`
--

DROP TABLE IF EXISTS `ruta_cuadrilla`;
CREATE TABLE `ruta_cuadrilla` (
  `id_ruta` int(11) NOT NULL,
  `id_cuadrilla` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruta_recoleccion`
--

DROP TABLE IF EXISTS `ruta_recoleccion`;
CREATE TABLE `ruta_recoleccion` (
  `id_ruta` int(11) NOT NULL,
  `id_circuito` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `actividad` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipo_residuo`
--

DROP TABLE IF EXISTS `tipo_residuo`;
CREATE TABLE `tipo_residuo` (
  `id_tipo` int(11) NOT NULL,
  `color` varchar(30) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `reciclable` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `ci_usuario` int(11) NOT NULL,
  `id_municipio` int(11) DEFAULT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `p_apellido` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `rol` enum('administrador','cuadrilla','operario','vecino') NOT NULL DEFAULT 'vecino',
  `acceso` enum('pendiente','habilitado','deshabilitado') NOT NULL DEFAULT 'pendiente',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `camion`
--
ALTER TABLE `camion`
  ADD PRIMARY KEY (`id_camion`);

--
-- Indexes for table `camion_centro`
--
ALTER TABLE `camion_centro`
  ADD PRIMARY KEY (`id_camion`,`id_centro`),
  ADD KEY `id_centro` (`id_centro`);

--
-- Indexes for table `centro_acopio`
--
ALTER TABLE `centro_acopio`
  ADD PRIMARY KEY (`id_centro`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indexes for table `centro_maquinaria`
--
ALTER TABLE `centro_maquinaria`
  ADD PRIMARY KEY (`id_centro`,`id_maquinaria`),
  ADD KEY `id_maquinaria` (`id_maquinaria`);

--
-- Indexes for table `centro_residuo`
--
ALTER TABLE `centro_residuo`
  ADD PRIMARY KEY (`id_centro`,`id_residuo`),
  ADD KEY `id_residuo` (`id_residuo`);

--
-- Indexes for table `centro_usuario`
--
ALTER TABLE `centro_usuario`
  ADD PRIMARY KEY (`id_centro`,`ci_usuario`),
  ADD KEY `ci_usuario` (`ci_usuario`);

--
-- Indexes for table `circuito`
--
ALTER TABLE `circuito`
  ADD PRIMARY KEY (`id_circuito`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indexes for table `contenedor`
--
ALTER TABLE `contenedor`
  ADD PRIMARY KEY (`id_contenedor`),
  ADD KEY `id_circuito` (`id_circuito`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- Indexes for table `contenedor_levante`
--
ALTER TABLE `contenedor_levante`
  ADD PRIMARY KEY (`id_levante`,`id_contenedor`),
  ADD KEY `id_contenedor` (`id_contenedor`);

--
-- Indexes for table `cuadrilla`
--
ALTER TABLE `cuadrilla`
  ADD PRIMARY KEY (`id_cuadrilla`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indexes for table `incidencia`
--
ALTER TABLE `incidencia`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `ci_usuario` (`ci_usuario`),
  ADD KEY `id_cuadrilla` (`id_cuadrilla`);

--
-- Indexes for table `incidencia_contenedor`
--
ALTER TABLE `incidencia_contenedor`
  ADD PRIMARY KEY (`id_incidencia`,`id_contenedor`),
  ADD KEY `id_contenedor` (`id_contenedor`);

--
-- Indexes for table `levante`
--
ALTER TABLE `levante`
  ADD PRIMARY KEY (`id_levante`),
  ADD KEY `id_camion` (`id_camion`),
  ADD KEY `id_cuadrilla` (`id_cuadrilla`),
  ADD KEY `id_ruta` (`id_ruta`);

--
-- Indexes for table `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `id_camion` (`id_camion`);

--
-- Indexes for table `maquinaria`
--
ALTER TABLE `maquinaria`
  ADD PRIMARY KEY (`id_maquinaria`);

--
-- Indexes for table `municipio`
--
ALTER TABLE `municipio`
  ADD PRIMARY KEY (`id_municipio`);

--
-- Indexes for table `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`);

--
-- Indexes for table `notificacion_usuario`
--
ALTER TABLE `notificacion_usuario`
  ADD PRIMARY KEY (`id_notificacion`,`ci_usuario`),
  ADD KEY `ci_usuario` (`ci_usuario`);

--
-- Indexes for table `prediccion_llenado`
--
ALTER TABLE `prediccion_llenado`
  ADD PRIMARY KEY (`id_prediccion`),
  ADD KEY `id_contenedor` (`id_contenedor`);

--
-- Indexes for table `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `ci_usuario` (`ci_usuario`);

--
-- Indexes for table `ruta_contenedor`
--
ALTER TABLE `ruta_contenedor`
  ADD PRIMARY KEY (`id_ruta`,`id_contenedor`),
  ADD KEY `id_contenedor` (`id_contenedor`);

--
-- Indexes for table `ruta_cuadrilla`
--
ALTER TABLE `ruta_cuadrilla`
  ADD PRIMARY KEY (`id_ruta`,`id_cuadrilla`),
  ADD KEY `id_cuadrilla` (`id_cuadrilla`);

--
-- Indexes for table `ruta_recoleccion`
--
ALTER TABLE `ruta_recoleccion`
  ADD PRIMARY KEY (`id_ruta`),
  ADD KEY `id_circuito` (`id_circuito`);

--
-- Indexes for table `tipo_residuo`
--
ALTER TABLE `tipo_residuo`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ci_usuario`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `camion`
--
ALTER TABLE `camion`
  MODIFY `id_camion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `centro_acopio`
--
ALTER TABLE `centro_acopio`
  MODIFY `id_centro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `circuito`
--
ALTER TABLE `circuito`
  MODIFY `id_circuito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contenedor`
--
ALTER TABLE `contenedor`
  MODIFY `id_contenedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cuadrilla`
--
ALTER TABLE `cuadrilla`
  MODIFY `id_cuadrilla` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incidencia`
--
ALTER TABLE `incidencia`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `levante`
--
ALTER TABLE `levante`
  MODIFY `id_levante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maquinaria`
--
ALTER TABLE `maquinaria`
  MODIFY `id_maquinaria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `municipio`
--
ALTER TABLE `municipio`
  MODIFY `id_municipio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prediccion_llenado`
--
ALTER TABLE `prediccion_llenado`
  MODIFY `id_prediccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ruta_recoleccion`
--
ALTER TABLE `ruta_recoleccion`
  MODIFY `id_ruta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_residuo`
--
ALTER TABLE `tipo_residuo`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `camion_centro`
--
ALTER TABLE `camion_centro`
  ADD CONSTRAINT `camion_centro_ibfk_1` FOREIGN KEY (`id_camion`) REFERENCES `camion` (`id_camion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `camion_centro_ibfk_2` FOREIGN KEY (`id_centro`) REFERENCES `centro_acopio` (`id_centro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `centro_acopio`
--
ALTER TABLE `centro_acopio`
  ADD CONSTRAINT `centro_acopio_ibfk_1` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id_municipio`) ON UPDATE CASCADE;

--
-- Constraints for table `centro_maquinaria`
--
ALTER TABLE `centro_maquinaria`
  ADD CONSTRAINT `centro_maquinaria_ibfk_1` FOREIGN KEY (`id_centro`) REFERENCES `centro_acopio` (`id_centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `centro_maquinaria_ibfk_2` FOREIGN KEY (`id_maquinaria`) REFERENCES `maquinaria` (`id_maquinaria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `centro_residuo`
--
ALTER TABLE `centro_residuo`
  ADD CONSTRAINT `centro_residuo_ibfk_1` FOREIGN KEY (`id_centro`) REFERENCES `centro_acopio` (`id_centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `centro_residuo_ibfk_2` FOREIGN KEY (`id_residuo`) REFERENCES `tipo_residuo` (`id_tipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `centro_usuario`
--
ALTER TABLE `centro_usuario`
  ADD CONSTRAINT `centro_usuario_ibfk_1` FOREIGN KEY (`id_centro`) REFERENCES `centro_acopio` (`id_centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `centro_usuario_ibfk_2` FOREIGN KEY (`ci_usuario`) REFERENCES `usuario` (`ci_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `circuito`
--
ALTER TABLE `circuito`
  ADD CONSTRAINT `circuito_ibfk_1` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id_municipio`) ON UPDATE CASCADE;

--
-- Constraints for table `contenedor`
--
ALTER TABLE `contenedor`
  ADD CONSTRAINT `contenedor_ibfk_1` FOREIGN KEY (`id_circuito`) REFERENCES `circuito` (`id_circuito`) ON UPDATE CASCADE,
  ADD CONSTRAINT `contenedor_ibfk_2` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_residuo` (`id_tipo`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `contenedor_levante`
--
ALTER TABLE `contenedor_levante`
  ADD CONSTRAINT `contenedor_levante_ibfk_1` FOREIGN KEY (`id_levante`) REFERENCES `levante` (`id_levante`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contenedor_levante_ibfk_2` FOREIGN KEY (`id_contenedor`) REFERENCES `contenedor` (`id_contenedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cuadrilla`
--
ALTER TABLE `cuadrilla`
  ADD CONSTRAINT `cuadrilla_ibfk_1` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id_municipio`) ON UPDATE CASCADE;

--
-- Constraints for table `incidencia`
--
ALTER TABLE `incidencia`
  ADD CONSTRAINT `incidencia_ibfk_1` FOREIGN KEY (`ci_usuario`) REFERENCES `usuario` (`ci_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidencia_ibfk_2` FOREIGN KEY (`id_cuadrilla`) REFERENCES `cuadrilla` (`id_cuadrilla`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `incidencia_contenedor`
--
ALTER TABLE `incidencia_contenedor`
  ADD CONSTRAINT `incidencia_contenedor_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencia` (`id_incidencia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `incidencia_contenedor_ibfk_2` FOREIGN KEY (`id_contenedor`) REFERENCES `contenedor` (`id_contenedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `levante`
--
ALTER TABLE `levante`
  ADD CONSTRAINT `levante_ibfk_1` FOREIGN KEY (`id_camion`) REFERENCES `camion` (`id_camion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `levante_ibfk_2` FOREIGN KEY (`id_cuadrilla`) REFERENCES `cuadrilla` (`id_cuadrilla`) ON UPDATE CASCADE,
  ADD CONSTRAINT `levante_ibfk_3` FOREIGN KEY (`id_ruta`) REFERENCES `ruta_recoleccion` (`id_ruta`) ON UPDATE CASCADE;

--
-- Constraints for table `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD CONSTRAINT `mantenimiento_ibfk_1` FOREIGN KEY (`id_camion`) REFERENCES `camion` (`id_camion`) ON UPDATE CASCADE;

--
-- Constraints for table `notificacion_usuario`
--
ALTER TABLE `notificacion_usuario`
  ADD CONSTRAINT `notificacion_usuario_ibfk_1` FOREIGN KEY (`id_notificacion`) REFERENCES `notificacion` (`id_notificacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notificacion_usuario_ibfk_2` FOREIGN KEY (`ci_usuario`) REFERENCES `usuario` (`ci_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prediccion_llenado`
--
ALTER TABLE `prediccion_llenado`
  ADD CONSTRAINT `prediccion_llenado_ibfk_1` FOREIGN KEY (`id_contenedor`) REFERENCES `contenedor` (`id_contenedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `reporte_ibfk_1` FOREIGN KEY (`ci_usuario`) REFERENCES `usuario` (`ci_usuario`) ON UPDATE CASCADE;

--
-- Constraints for table `ruta_contenedor`
--
ALTER TABLE `ruta_contenedor`
  ADD CONSTRAINT `ruta_contenedor_ibfk_1` FOREIGN KEY (`id_ruta`) REFERENCES `ruta_recoleccion` (`id_ruta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ruta_contenedor_ibfk_2` FOREIGN KEY (`id_contenedor`) REFERENCES `contenedor` (`id_contenedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ruta_cuadrilla`
--
ALTER TABLE `ruta_cuadrilla`
  ADD CONSTRAINT `ruta_cuadrilla_ibfk_1` FOREIGN KEY (`id_ruta`) REFERENCES `ruta_recoleccion` (`id_ruta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ruta_cuadrilla_ibfk_2` FOREIGN KEY (`id_cuadrilla`) REFERENCES `cuadrilla` (`id_cuadrilla`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ruta_recoleccion`
--
ALTER TABLE `ruta_recoleccion`
  ADD CONSTRAINT `ruta_recoleccion_ibfk_1` FOREIGN KEY (`id_circuito`) REFERENCES `circuito` (`id_circuito`) ON UPDATE CASCADE;

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_municipio`) REFERENCES `municipio` (`id_municipio`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
