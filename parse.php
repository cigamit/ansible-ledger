<?php

include_once('includes/classes/Spyc.php');
use Async\Yaml;


$d = file_get_contents('php://input');
if ($d != '') {
	$d = json_decode($d, true);
}
//file_put_contents('data-all.txt', print_r($d, true), FILE_APPEND);

if (isset($d['logger_name'])) {
	switch ($d['logger_name']) {
		case 'awx.analytics.job_events':
			// JOB EVENT DATA
			if (isset($d['event_data']['res']['ansible_facts'])) {
				if (isset($d['host_name'])) {
					$f = $d['event_data']['res']['ansible_facts'];
					$t = $d['event_data']['task_action'];
					$fs = parse_facts($f);
					if (count($fs)) {
						include_once('includes/sql.php');
						$h = check_host($d['host_name']);
						if ($h) {
							$s = 'INSERT INTO `facts` (`host`, `fact`, `data`, `type`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `data` = ?';
							foreach ($fs as $k => $v) {
								db_execute_prepare($s, array($h, $k, $v, $t, $v));
							}
						}
					}
				}
			}

			if (isset($d['changed']) && $d['changed'] && isset($d['job']) && $d['job'] && isset($d['event_data']['task_action']) && $d['event_data']['task_action'] != '') {
				include_once('includes/sql.php');

				$h = check_host($d['host_name']);
				$role = (isset($d['role']) ? $d['role'] : '');
				$res = Yaml::dumper($d['event_data']['res']);
				db_execute_prepare('INSERT INTO `changes` (`host`, `time`, `job`, `playbook`, `play`, `role`, `task`, `task_action`, `res`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array($h, time(), $d['job'], $d['playbook'], $d['play'], $role, $d['task'], $d['event_data']['task_action'], $res));

//file_put_contents('events.txt', print_r($d, true), FILE_APPEND);

			}
			break;
		case 'awx.analytics.activity_stream':
			// JOB DATA
			if (isset($d['operation']) && $d['operation'] == 'create' && isset($d['object1']) && $d['object1'] == 'job') {
				include_once('includes/sql.php');

//file_put_contents('jobs.txt', print_r($d, true), FILE_APPEND);

				db_execute_prepare('INSERT INTO `jobs` (`timestamp`, `job`, `job_template_id`, `host`, `name`, `job_type`, `inventory`, `project`, `scm_branch`, `execution_environment`, `actor`, `limit`) VALUES
									(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
									array($d['@timestamp'], $d['changes']['id'], $d['summary_fields']['job_template'][0]['id'],
										  $d['host'], $d['changes']['name'], $d['changes']['job_type'], $d['changes']['inventory'],
										  $d['changes']['project'], $d['changes']['scm_branch'], $d['changes']['execution_environment'], 
										  $d['actor'], $d['changes']['limit']
										));
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
