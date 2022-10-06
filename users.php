<?php

include_once('includes/global.php');

if (!$account['super']) {
    header("Location: /\n\n");
    exit;
}

if (isset($_GET['user']) && $_GET['user'] == intval($_GET['user']) && intval($_GET['user'])) {
    $user = new User($_GET['user']);
} else {
    $user = new User();
}


if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	if ($user->id) {
		$user->delete();
	}
	Header("Location: /users/\n\n");
	exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
	if ($user->id) {
        echo $twig->render('user.html', array_merge($twigarr, array('user' => $user)));
        exit;
   	}
}

if (isset($_GET['action']) && $_GET['action'] == 'save') {
    if ($user->id) {
        if (isset($_POST['name'])) {
            $user->set_name($user->clean_name($_POST['name']));
        }

        if (isset($_POST['username'])) {
            $user->set_username($user->clean_username($_POST['username']));
        }

        if (isset($_POST['email'])) {
            $user->set_email($user->clean_username($_POST['email']));
        }

        if (isset($_POST['enabled'])) {
            $user->set_enabled(intval($_POST['enabled']));
        }

        if (isset($_POST['super'])) {
            $user->set_super(intval($_POST['super']));
        }

        $newpass = $_POST['newpass'];
        $newpass2 = $_POST['newpass2'];
        if ($newpass != '' && strlen($newpass) > 7 && $newpass == $newpass2) {
            $user->set_password($newpass);
            $user->set_code('');
        }
        $user->save();
    }
	Header("Location: /users/\n\n");
	exit;
}

$users = db_fetch_assocs("SELECT * FROM `users` ORDER BY `username`");

echo $twig->render('users.html', array_merge($twigarr, array('users' => $users)));
