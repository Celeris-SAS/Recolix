<?php
session_start();
session_destroy();
header('Location: ../LandingPage/login.php');
exit;
