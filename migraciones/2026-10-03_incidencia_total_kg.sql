-- RF14 / cierre de incidencia por la cuadrilla: guarda el total recolectado (kg)
-- al cerrar la incidencia desde la interfaz de la cuadrilla.
-- Correr una sola vez en bases existentes.
USE `recolix`;

ALTER TABLE `incidencia`
  ADD COLUMN `total_kg` int(11) DEFAULT NULL AFTER `fecha_cierre`;
