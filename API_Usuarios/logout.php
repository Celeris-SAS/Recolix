<?php
session_start();
session_destroy();
header('Location: /recolix/LandingPage/login.html');
exit;
