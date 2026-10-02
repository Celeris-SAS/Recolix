-- Permite borrar la cuenta de un vecino sin perder las incidencias que reportó:
-- al borrarse el usuario, sus incidencias quedan con ci_usuario = NULL.
-- Correr una sola vez en bases importadas antes de este cambio (recolix.sql ya lo incluye).
USE `recolix`;

ALTER TABLE `incidencia` DROP FOREIGN KEY `incidencia_ibfk_1`;

ALTER TABLE `incidencia`
  MODIFY `ci_usuario` int(11) DEFAULT NULL,
  ADD CONSTRAINT `incidencia_ibfk_1` FOREIGN KEY (`ci_usuario`) REFERENCES `usuario` (`ci_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;
