<?php
include_once('includes/global.php');

$changes_retention = read_setting('changes_retension', 30);

if (isset($_POST['action']) && $_POST['action'] == 'save') {
	if (isset($_POST['changes_retension'])) {
		$changes_retension = intval($_POST['changes_retension']);
        db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($changes_retension, 'changes_retension'));
	}

    $is_dev = (isset($_POST['is_dev']) ? 1 : 0);
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($is_dev, 'is_dev'));

    $disable_email = (isset($_POST['disable_email']) ? 1 : 0);
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($disable_email, 'disable_email'));

    Header("Location: /settings/\n\n");
    exit;
}

$settings = array();
$set      = db_fetch_assocs('SELECT * FROM `settings`');

foreach ($set as $s) {
	$settings[$s['setting']] = $s['value'];
}

echo $twig->render('settings.html', array_merge($twigarr, array('settings' => $settings)));
