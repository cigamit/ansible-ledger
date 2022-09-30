<?php
include_once('includes/global.php');

$user = new User($account['id']);

if (isset($_POST['action']) && $_POST['action'] == 'save') {
	if (isset($_POST['name'])) {
		$name = $user->clean_name($_POST['name']);
		if ($name != $user->name) {
			$user->set_name($name);
			$user->save();
		}
	}

	$newpass = $_POST['newpass'];
	$newpass2 = $_POST['newpass2'];
	if ($newpass != '' && strlen($newpass) > 7 && $newpass == $newpass2) {
		$user->set_password($newpass);
		$user->set_code('');
		$user->save();
	}
	Header("Location: /account/\n\n");
	exit;
}



echo $twig->render('account.html', $twigarr);
