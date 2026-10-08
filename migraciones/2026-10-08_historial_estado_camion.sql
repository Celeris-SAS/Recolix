-- RF36 / RF43: historial de cambios de estado de cada camión.
-- Los triggers registran el cambio sin importar desde qué parte del código (o a mano) se actualice
-- camion.estado. Correr una sola vez en bases importadas antes de este cambio (recolix.sql ya lo incluye).
USE `recolix`;

CREATE TABLE IF NOT EXISTS `camion_estado_historial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_camion` int(11) NOT NULL,
  `estado_anterior` varchar(20) DEFAULT NULL,
  `estado_nuevo` varchar(20) NOT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_camion_fecha` (`id_camion`, `fecha_cambio`),
  CONSTRAINT `camion_estado_historial_ibfk_1` FOREIGN KEY (`id_camion`) REFERENCES `camion` (`id_camion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TRIGGER IF EXISTS `camion_estado_alta`;
DROP TRIGGER IF EXISTS `camion_estado_cambio`;

DELIMITER $$
CREATE TRIGGER `camion_estado_alta` AFTER INSERT ON `camion` FOR EACH ROW
BEGIN
  INSERT INTO `camion_estado_historial` (`id_camion`, `estado_anterior`, `estado_nuevo`) VALUES (NEW.`id_camion`, NULL, NEW.`estado`);
END$$

CREATE TRIGGER `camion_estado_cambio` AFTER UPDATE ON `camion` FOR EACH ROW
BEGIN
  IF NOT (OLD.`estado` <=> NEW.`estado`) THEN
    INSERT INTO `camion_estado_historial` (`id_camion`, `estado_anterior`, `estado_nuevo`) VALUES (NEW.`id_camion`, OLD.`estado`, NEW.`estado`);
  END IF;
END$$
DELIMITER ;

-- Estado inicial de los camiones ya existentes.
INSERT INTO `camion_estado_historial` (`id_camion`, `estado_anterior`, `estado_nuevo`)
SELECT c.`id_camion`, NULL, c.`estado` FROM `camion` c
WHERE NOT EXISTS (SELECT 1 FROM `camion_estado_historial` h WHERE h.`id_camion` = c.`id_camion`);
