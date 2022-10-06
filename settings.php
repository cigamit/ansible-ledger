<?php
include_once('includes/global.php');

$changes_retention = read_setting('changes_retention', 30);
$facts_retention = read_setting('facts_retention', 30);
$hosts_retention = read_setting('hosts_retention', 30);

if (isset($_GET['action']) && $_GET['action'] == 'save') {
	$changes_retention = intval($_POST['changes_retention']);
    $facts_retention = intval($_POST['facts_retention']);
    $hosts_retention = intval($_POST['hosts_retention']);

    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($changes_retention, 'changes_retention'));
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($facts_retention, 'facts_retention'));
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($hosts_retention, 'hosts_retention'));

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
