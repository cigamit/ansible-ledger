<?php

/*
	This file simply loads our vendor files and sets up our Twig environment
*/

$loader = new Twig_Loader_Filesystem('templates');

$twig = new Twig_Environment($loader, array('debug' => true));

/*
	We pass in the account variable automatically since we use it in most templates
*/
$is_dev = read_setting('is_dev');
$disable_email = read_setting('disable_email');
$imp = (isset($_SESSION['imp']) && intval($_SESSION['imp']) ? $_SESSION['imp'] : 0); 
// REMOVE ME
$is_dev = true;
if ($is_dev) {
	$twig->addExtension(new Twig_Extension_Debug());
} else {
	$twig = new Twig_Environment($loader, array(
	    'cache' => 'twig/cache',
	));
}

$twigarr = array('account' => $account, 'request_scheme' => $_SERVER['REQUEST_SCHEME'], 'server_name' => $_SERVER['SERVER_NAME'], 'server' => $_SERVER['PHP_SELF'], 'is_dev' => $is_dev, 'disable_email' => $disable_email, 'imp' => $imp, 'version' => VERSION, 'base_url' => BASE_URL);