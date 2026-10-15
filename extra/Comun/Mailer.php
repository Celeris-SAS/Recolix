<?php
// Envío de mail best-effort para las notificaciones (vecino, admin, cuadrilla, operario).
//
// Deshabilitado por defecto: si RECOLIX_MAIL_ENABLED != '1', no toca la red y solo registra
// en mail_log.txt lo que se habría enviado. Esto es intencional: este XAMPP no tiene
// sendmail_path configurado (ver php.ini), así que mail() intenta conectarse a un servidor
// SMTP que no existe. Aunque se acota el timeout a unos segundos, pagar esa espera en cada
// notificación -y en cada test que crea notificaciones- no es aceptable.
//
// Para enviar mails reales:
//   1) Configurar un servidor SMTP real (sendmail_path en php.ini, o un relay accesible
//      en SMTP/smtp_port).
//   2) Setear la variable de entorno RECOLIX_MAIL_ENABLED=1 (y opcionalmente
//      RECOLIX_MAIL_FROM con el remitente).
class Mailer {
    public static function enviar($destinatario, $asunto, $mensaje) {
        if (!$destinatario) {
            return false;
        }

        if (getenv('RECOLIX_MAIL_ENABLED') !== '1') {
            self::registrar("[deshabilitado] Para: $destinatario | Asunto: $asunto | $mensaje");
            return false;
        }

        $remitente = getenv('RECOLIX_MAIL_FROM') ?: 'notificaciones@recolix.uy';
        $headers = "From: Recolix <$remitente>\r\nContent-Type: text/plain; charset=UTF-8";

        // Acota el tiempo máximo que puede tardar en fallar la conexión SMTP: sin esto,
        // default_socket_timeout puede llegar a 60s y colgar la request.
        $timeoutPrevio = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 3);
        try {
            $enviado = @mail($destinatario, $asunto, $mensaje, $headers);
        } catch (\Throwable $e) {
            $enviado = false;
        } finally {
            ini_set('default_socket_timeout', $timeoutPrevio);
        }

        if (!$enviado) {
            self::registrar("[fallo] Para: $destinatario | Asunto: $asunto");
        }
        return $enviado;
    }

    private static function registrar($linea) {
        @file_put_contents(__DIR__ . '/mail_log.txt', '[' . date('Y-m-d H:i:s') . "] $linea\n", FILE_APPEND);
    }
}
