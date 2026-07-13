<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: login_backoffice.php');
exit;
