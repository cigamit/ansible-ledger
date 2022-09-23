<?php
include_once('includes/global.php');

$hosts = reindex_arr_by_id(db_fetch_assocs("SELECT * FROM `hosts` ORDER BY `hostname`"));
$h = array();
foreach ($hosts as $host) {
	$h[$host['id']] = $host['hostname'];
}

if (isset($_GET['action']) && $_GET['action'] == 'view' && $_GET['change'] == intval($_GET['change']) && intval($_GET['change'])) {
	$change = db_fetch_assoc_prepare('SELECT * FROM `changes` WHERE `id` = ?', array(intval($_GET['change'])));
	if (isset($change['id']) && $change['id']) {
#		$change['res'] = unserialize(base64_decode($change['res']));

		$res = parse_res($change['task_action'], $change['res']);
		echo $twig->render('change.html', array_merge($twigarr, array('change' => $change, 'hosts' => $h, 'res' => $res)));
		exit;

	}
}

$playbooks = reindex_col(db_fetch_assocs("SELECT UNIQUE `playbook` FROM `changes` ORDER BY `playbook` ASC"), 'playbook');
$filters = array();
$host = '';
$playbook = '';


if (isset($_GET['host']) && intval($_GET['host']) == $_GET['host'] && isset($hosts[$_GET['host']])) {
	$host = intval($_GET['host']);
	$filters[] = " `host` = $host";
}

if (isset($_GET['playbook']) && in_array($_GET['playbook'], $playbooks)) {
	$playbook = $_GET['playbook'];
	$filters[] = " `playbook` = '$playbook'";
}

if (!empty($filters)) {
	$filters = "WHERE " . implode(' AND ', $filters);
} else {
	$filters = '';
}

$changes = db_fetch_assocs("SELECT * FROM `changes` $filters ORDER BY `time` DESC LIMIT 0,100");




echo $twig->render('changes.html', array_merge($twigarr, array('changes' => $changes, 'hosts' => $h, 'playbooks' => $playbooks, 'host' => $host, 'playbook' => $playbook)));






function parse_res($module, $r) {
	return print_r($r, true);

	switch ($module) {
		case 'win_feature':
			$t = "<br>Installed</b><br>";
			foreach ($r['feature_result'] as $f) {
				$t .= ' - ' . $f['display_name'] . '<br>';
			}
			return $t;

		case 'win_security_policy':
			return '<b>Key</b>: ' . $r['key'] . '<br><b>Section</b>: ' . $r['section'] . '<br><b>Value</b>: ' . $s['value'];
	}
	return print_r($r, true);
}
