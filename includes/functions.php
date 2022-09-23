<?php

/*
	This file contains various misc functions that didn't fit in well anywhere else
*/


// Start the session
if (!isset($_SESSION)) {
	session_start();
}

$logged = false;
$account = array();

// $account holds information about the current logged in user.  We should probably swap this to a session variable
global $account;
$account['code'] = '';

if (isset($_SESSION['imp']) && $_SESSION['imp'] && $_SESSION['imp'] != '' && isset($_GET['revertimp'])) {
	$id = intval($_SESSION['imp']);
	$user = new User($id);
	if ($user->id && $user->enabled && $user->operator) {
		unset($_SESSION['imp']);
		$_SESSION['username'] = $user->username;
	}
	Header("Location: index.php?reverted=1\n\n");
	exit;
}


if (isset($_SESSION['auth']) && $_SESSION['auth'] == TRUE) {
	$username = $_SESSION['username'];
	$logged = true;
}

if ($_SERVER['PHP_SELF'] != '/login.php' && $_SERVER['PHP_SELF'] != '/forgot_password.php') {
	auth_check();
}

if ($_SERVER['PHP_SELF'] == '/login.php' && isset($_GET['code'])) {
	auth_check();
}

if ($account['code'] != '' && $_SERVER['PHP_SELF'] != '/account.php') {
	Header("Location: account.php\n\n");
	exit;
}

include_once('includes/templates.php');


/*
	A simple function to check if we are an operator.  It is used on pages that are for admins only to 
	redirect back to the main page if they are not authorized
*/
function op_check() {
	global $account;
	if ($account['operator'] == 0) {
		Header("Location: /index.php\n\n");
		exit;	
	}
	return true;
}

/*
	This function does most everything for authorization.  It will check to see if you are already logged in or if a 
	username / password is passed and will check it.  It then sets a few session variables and an account variable
*/
function auth_check() {
	global $username, $logged, $userid, $account;
	if (isset($_SESSION['auth']) && $_SESSION['auth'] == TRUE) {
		// Check for authorization on specific pages
              $user = new User();
              $account = $user->retrieve_by_username($username);
		if (!$user->enabled) {
			$account = null;
		}
		return true;
	}

	if (isset($_GET['code']) && sql_clean_ans($_GET['code']) != '') {
		$code = sql_clean_ans($_GET['code']);
              $user = new User();
              $account = $user->retrieve_by_code($code);
		if ($user->enabled) {
			$_SESSION['auth'] = TRUE;	
			$_SESSION['username'] = $account['username'];
			return true;
		} else {
			sleep(5);
			$account = null;
			Header("Location: /index.php\n\n");
			exit;
		}
	}

	if (isset($_POST['username']) && $_POST['username'] != '') {
		$username = sql_clean_username($_POST['username']);
	}

	if (!isset($_POST['username']) || !isset($_POST['password'])) {
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$_SESSION['referrer'] = $url;
		showloginbox(0);
		return false;
	}

	$check = check_user_password(trim($username), trim($_POST['password']));

	if ($check) {
		$_SESSION['auth'] = TRUE;	
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $_POST['password'];
		$logged = true;
		//db_execute_prepare('UPDATE users SET lastlogin = ? WHERE username = ?', array(time(), $username));
		//$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		//$ip = trim($ip[0]);
		//db_execute_prepare('INSERT INTO ip_log (`time`, `type`, `username`, `ip`) VALUES (?, ?, ?, ?)', array(time(), 'Website', $username, $ip));
              $user = new User();
              $account = $user->retrieve_by_username($username);
		if (!$user->enabled) {
			$account = null;
		}

		//if (isset($_SESSION['referrer']) && $_SESSION['referrer'] != '') {
		//	$url = $_SESSION['referrer'];
		//	$_SESSION['referrer'] = '';
		//	header("Location: $url\n\n");
		//	exit;
		//}

		return true;
	} else {
		showloginbox(1);;
		exit;
	}
}

function check_user_password($username, $pass) {
	$username  = strtolower($username);
	$user = new User();
	$account = $user->retrieve_by_username($username);
	if ($user->id && $user->enabled) {
		if (sha1($pass) == $user->password) {
			return true;	
		}
	}
	sleep(1);
	return false;
}

function showloginbox($error) {
	Header("Location: /login.php\n\n");
	exit;
}

function stripUnwantedTagsAndAttrs($html_str){
	$xml = new DOMDocument();
  //Suppress warnings: proper error handling is beyond scope of example
	libxml_use_internal_errors(false);
  //List the tags you want to allow here, NOTE you MUST allow html and body otherwise entire string will be cleared
	$allowed_tags = array("b", "br", "em", "i", "li", "ol", "u", "ul", "p");
  //List the attributes you want to allow here
	$allowed_attrs = array ();
	if (!strlen($html_str)){return false;}
	if ($xml->loadHTML($html_str, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)){
	  foreach ($xml->getElementsByTagName("*") as $tag){
		if (!in_array($tag->tagName, $allowed_tags)){
		  $tag->parentNode->removeChild($tag);
		}else{
		  foreach ($tag->attributes as $attr){
			if (!in_array($attr->nodeName, $allowed_attrs)){
			  $tag->removeAttribute($attr->nodeName);
			}
		  }
		}
	  }
	}
	$d = $xml->saveHTML();
	return strip_tags($d);
}

function random_str($length) {
	$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$str = '';
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$str .= $keyspace[random_int(0, $max)];
	}
	return $str;
}

function reindex_col($a, $c) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[] = $b[$c];
		}
	}

	return $d;
}

function reindex_arr_by_id($a) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[$b['id']] = $b;
		}
	}

	return $d;
}

function reindex_arr_by_id_col($a, $c) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[$b['id']] = $b[$c];
		}
	}

	return $d;
}