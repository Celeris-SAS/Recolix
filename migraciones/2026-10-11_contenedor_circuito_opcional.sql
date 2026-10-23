-- ERR-03: RNE 26 estaba invertida. Un contenedor en_deposito no pertenece a ningún
-- circuito (está físicamente en el depósito, fuera de cualquier ruta), pero la columna
-- id_circuito era NOT NULL, así que la API nunca podía darlo de alta sin un circuito y
-- sí aceptaba asignarle uno. Se vuelve la columna opcional; el Controlador/Modelo ahora
-- exige un circuito para los demás estados y rechaza que un contenedor en_deposito tenga uno.
-- Correr una sola vez en bases importadas antes de este cambio (recolix.sql ya lo incluye).
USE `recolix`;

ALTER TABLE `contenedor`
  MODIFY `id_circuito` int(11) DEFAULT NULL;
