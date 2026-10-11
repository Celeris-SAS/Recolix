<?php
// Plantilla de Comun/mail-config.php (ver Mailer.php). Copiá este archivo como
// "Comun/mail-config.php" y completá tus propios datos: ESE archivo no se versiona
// en git (está en .gitignore) porque va a tener una contraseña real.
//
// Para activar el envío de mails:
//   1) Activá la verificación en 2 pasos en la cuenta de Gmail que va a enviar
//      (myaccount.google.com/security).
//   2) Generá una "contraseña de aplicación": myaccount.google.com/apppasswords
//      (elegí "Otra" / "Correo", y copiá la contraseña de 16 letras que te da Google).
//   3) Pegá el mail y esa contraseña acá abajo, en tu copia local (Comun/mail-config.php).
//
// Sin ese archivo (o con estos campos vacíos), el sistema no manda nada: solo registra
// en mail_log.txt qué habría enviado. Cada persona que quiera mails reales en su entorno
// local necesita su propia cuenta de Gmail con contraseña de aplicación (o coordinar con
// el equipo para usar una cuenta compartida).
return [
    'gmailUser'        => '',
    'gmailAppPassword' => '',
];
