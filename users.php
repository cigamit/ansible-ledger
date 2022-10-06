<?php

include_once('includes/global.php');

if (!$account['super']) {
    header("Location: /\n\n");
    exit;
}

$users = db_fetch_assocs("SELECT * FROM `users` ORDER BY `username`");

echo $twig->render('users.html', array_merge($twigarr, array('users' => $users)));
