<?php

include_once('includes/classes/Spyc.php');
use Async\Yaml;


$data = file_get_contents('php://input');
if ($data != '') {
	$data = json_decode($data, true);
}
//file_put_contents('data-all.txt', print_r($data, true), FILE_APPEND);

if (isset($data['logger_name'])) {
	switch ($data['logger_name']) {
		case 'awx.analytics.job_events':
			if (isset($data['event_data']['res']['ansible_facts'])) {
				if (isset($data['host_name'])) {
					$f = $data['event_data']['res']['ansible_facts'];
					$t = $data['event_data']['task_action'];
					$fs = parse_facts($f);
					if (count($fs)) {
						include_once('includes/sql.php');
						$h = check_host($data['host_name']);
						if ($h) {
							$s = 'INSERT INTO `facts` (`host`, `fact`, `data`, `type`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `data` = ?';
							foreach ($fs as $k => $v) {
								db_execute_prepare($s, array($h, $k, $v, $t, $v));
							}
						}
					}
				}
			}

			if (isset($data['changed']) && $data['changed'] && isset($data['job']) && $data['job'] && isset($data['event_data']['task_action']) && $data['event_data']['task_action'] != '') {
				include_once('includes/sql.php');

				$h = check_host($data['host_name']);
				$role = (isset($data['role']) ? $data['role'] : '');
				$res = Yaml::dumper($data['event_data']['res']);
				db_execute_prepare('INSERT INTO `changes` (`tower`, `host`, `time`, `job`, `playbook`, `play`, `role`, `task`, `task_action`, `res`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array($_SERVER['REMOTE_ADDR'], $h, time(), $data['job'], $data['playbook'], $data['play'], $role, $data['task'], $data['event_data']['task_action'], $res));

//file_put_contents('data.txt', print_r($data, true), FILE_APPEND);

			}
			break;
	}
}

function parse_facts ($f, $fs = array(), $n = '') {
	if (!empty($f)) {
		foreach ($f as $k => $v) {
			if (is_array($v)) {
				$s = ($n != '' && substr($n, -1, 1) != '.' ? '.' : '');
				$fs = parse_facts($v, $fs, "$n$s$k.");
			} else {
				if ($v === true) $v = "true";
				if ($v === false) $v = "false";
				$fs[$n . $k] = $v;
			}
		}
	}
	return $fs;
}

function check_host ($host) {
	$h = db_fetch_assoc_prepare('SELECT id FROM hosts WHERE hostname = ?', array(strtolower($host)));
	if (isset($h['id'])) {
		return $h['id'];
	} else {
		$h = db_execute_prepare('INSERT INTO `hosts` (`hostname`) VALUES (?)', array(strtolower($host)));
		return $h;
	}
}
