<?php

if( !isset($_SESSION)) {
	session_start();
}

unset($_SESSION['auth']);
unset($_SESSION['username']);
session_destroy();

Header("Location: /index.php\n\n");
exit;
