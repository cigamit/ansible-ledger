<?php
include_once('includes/global.php');

$changes_retention = read_setting('changes_retention', 30);
$facts_retention = read_setting('facts_retention', 30);
$hosts_retention = read_setting('hosts_retention', 30);
$remove_invocation = read_setting('remove_invocation', 0);
$from_name = read_setting('from_name', 'Ansible Ledger');
$from_email = read_setting('from_email', 'ledger@ansible.local');

if (isset($_GET['action']) && $_GET['action'] == 'save') {
	$changes_retention = intval($_POST['changes_retention']);
    $facts_retention = intval($_POST['facts_retention']);
    $hosts_retention = intval($_POST['hosts_retention']);
    $remove_invocation = intval($_POST['remove_invocation']);

    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($changes_retention, 'changes_retention'));
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($facts_retention, 'facts_retention'));
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($hosts_retention, 'hosts_retention'));

    $from_name = sql_clean_username($_POST['from_name']);
    $from_email = sql_clean_username($_POST['from_email']);
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($from_name, 'from_name'));
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($from_email, 'from_email'));

    $remove_invocation = (isset($_POST['remove_invocation']) ? 1 : 0);
    db_execute_prepare('UPDATE `settings` SET `value` = ? WHERE `setting` = ?', array($remove_invocation, 'remove_invocation'));

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
