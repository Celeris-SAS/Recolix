<?php
class Mailer {
    public static function enviar($destinatario, $asunto, $mensaje) {
        if (!$destinatario) {
            return false;
        }

        if (getenv('RECOLIX_MAIL_ENABLED') !== '1') {
            self::registrar("[deshabilitado] Para: $destinatario | Asunto: $asunto | $mensaje");
            return false;
        }

        $remitente = getenv('RECOLIX_MAIL_FROM') ?: 'celerisuy@gmail.com';
        $headers = "From: Recolix <$remitente>\r\nContent-Type: text/plain; charset=UTF-8";
        $asunto = '=?UTF-8?B?' . base64_encode($asunto) . '?=';

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
