<?php
// Envío de mail para las notificaciones (vecino, admin, cuadrilla, operario), usando SMTP
// directo contra Gmail (smtp.gmail.com) en vez de un servicio externo o el mail() nativo de PHP.
//
// Por qué así: un servicio como Resend/Brevo en su plan gratis, sin un dominio propio
// verificado, no deja mandar a cualquier destinatario real. Gmail sí, porque es una cuenta de
// mail real y ya tiene reputación de envío — no depende de verificar nada adicional, solo de
// una "contraseña de aplicación" que genera Google para darle acceso SMTP a esta app sin
// exponer la contraseña real de la cuenta.
//
// Cómo activarlo:
//   1) Activá la verificación en 2 pasos en la cuenta de Gmail que va a enviar los mails
//      (myaccount.google.com/security).
//   2) Generá una "contraseña de aplicación": myaccount.google.com/apppasswords
//      (elegí "Otra" / "Correo" como app, y copiá la contraseña de 16 letras que te da).
//   3) Pegá el mail y esa contraseña en Comun/mail-config.php (ver ese archivo; no se versiona
//      en git).
// Sin esos datos configurados, no se envía nada: solo queda registrado en mail_log.txt.
class Mailer {
    public static function enviar($destinatario, $asunto, $mensaje) {
        if (!$destinatario) {
            return false;
        }

        $config = self::cargarConfig();
        if (empty($config['gmailUser']) || empty($config['gmailAppPassword'])) {
            self::registrar("[deshabilitado] Para: $destinatario | Asunto: $asunto | $mensaje");
            return false;
        }

        try {
            self::enviarPorSmtp($config['gmailUser'], $config['gmailAppPassword'], $destinatario, $asunto, $mensaje);
            return true;
        } catch (Exception $e) {
            self::registrar("[fallo] Para: $destinatario | Asunto: $asunto | " . $e->getMessage());
            return false;
        }
    }

    private static function enviarPorSmtp($usuario, $appPassword, $destinatario, $asunto, $mensaje) {
        // cafile explícito: el PHP de XAMPP no trae un paquete de certificados CA configurado
        // (y en esta máquina además hay un antivirus que intercepta el TLS saliente), así que
        // sin esto la conexión STARTTLS no puede verificar el certificado del servidor.
        $contexto = stream_context_create([
            'ssl' => [
                'cafile'            => __DIR__ . '/cacert.pem',
                'verify_peer'       => true,
                'verify_peer_name'  => true,
            ],
        ]);
        $socket = @stream_socket_client(
            'tcp://smtp.gmail.com:587',
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $contexto
        );
        if (!$socket) {
            throw new Exception("No se pudo conectar a smtp.gmail.com: $errstr ($errno)");
        }
        stream_set_timeout($socket, 10);

        try {
            self::leerRespuesta($socket, '220');
            self::comando($socket, "EHLO recolix.local", '250');
            self::comando($socket, "STARTTLS", '220');

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("No se pudo iniciar TLS con smtp.gmail.com");
            }

            self::comando($socket, "EHLO recolix.local", '250');
            self::comando($socket, "AUTH LOGIN", '334');
            self::comando($socket, base64_encode($usuario), '334');
            self::comando($socket, base64_encode($appPassword), '235');

            self::comando($socket, "MAIL FROM:<$usuario>", '250');
            self::comando($socket, "RCPT TO:<$destinatario>", '250');
            self::comando($socket, "DATA", '354');

            $cabeceras = "From: Recolix <$usuario>\r\n"
                . "To: <$destinatario>\r\n"
                . "Subject: $asunto\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n";
            $cuerpo = str_replace("\n.", "\n..", $mensaje);
            self::comando($socket, $cabeceras . "\r\n" . $cuerpo . "\r\n.", '250');

            self::comando($socket, "QUIT", '221');
        } finally {
            fclose($socket);
        }
    }

    private static function comando($socket, $linea, $codigoEsperado) {
        fwrite($socket, $linea . "\r\n");
        self::leerRespuesta($socket, $codigoEsperado);
    }

    private static function leerRespuesta($socket, $codigoEsperado) {
        $respuesta = '';
        do {
            $linea = fgets($socket, 1024);
            if ($linea === false) {
                throw new Exception("Conexión SMTP cerrada inesperadamente (esperaba $codigoEsperado)");
            }
            $respuesta .= $linea;
        } while (isset($linea[3]) && $linea[3] === '-');

        if (substr($respuesta, 0, 3) !== $codigoEsperado) {
            throw new Exception("Respuesta SMTP inesperada (esperaba $codigoEsperado): " . trim($respuesta));
        }
        return $respuesta;
    }

    private static function cargarConfig() {
        $archivo = __DIR__ . '/Comun/mail-config.php';
        if (!file_exists($archivo)) {
            return ['gmailUser' => '', 'gmailAppPassword' => ''];
        }
        $config = require $archivo;
        return is_array($config) ? $config : ['gmailUser' => '', 'gmailAppPassword' => ''];
    }

    private static function registrar($linea) {
        @file_put_contents(__DIR__ . '/mail_log.txt', '[' . date('Y-m-d H:i:s') . "] $linea\n", FILE_APPEND);
    }
}
