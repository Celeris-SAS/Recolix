-- Las rutas inactivas (baja lógica) no se listan: el índice acelera el filtro por activa.
-- Correr una sola vez en bases importadas antes de este cambio (recolix.sql ya lo incluye).
USE `recolix`;

ALTER TABLE `ruta_recoleccion` ADD INDEX IF NOT EXISTS `idx_ruta_activa_circuito` (`activa`, `id_circuito`);
