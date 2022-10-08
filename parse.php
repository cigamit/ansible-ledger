<?php

include_once('includes/classes/Spyc.php');

use Async\Yaml;

include_once('vendor/autoload.php');
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;



$d = file_get_contents('php://input');
if ($d != '') {
	$d = json_decode($d, true);
}
//file_put_contents('data-all.txt', print_r($d, true), FILE_APPEND);
$time = time();
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
							$s = 'INSERT INTO `facts` (`host`, `fact`, `data`, `type`, `time`) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `data` = ?, `time` = ?';
							foreach ($fs as $k => $v) {
								db_execute_prepare($s, array($h, $k, $v, $t, $time, $v, $time));
							}
						}
					}
				}
			}

			if (isset($d['changed']) && $d['changed'] && isset($d['event']) && $d['event'] == 'runner_on_ok' && isset($d['job']) && $d['job'] && isset($d['event_data']['task_action']) && $d['event_data']['task_action'] != '') {
				include_once('includes/sql.php');
				$remove_invocation = read_setting('remove_invocation', 0);

				if (isset($d['event_data']['res']['results'])) {
					for ($r = 0; $r < count($d['event_data']['res']['results']); $r++) {
						if ($remove_invocation && isset($d['event_data']['res']['results'][$r]['invocation'])) {
							unset($d['event_data']['res']['results'][$r]['invocation']);
						}
						if (isset($d['event_data']['res']['results'][$r]['invocation']['module_args'])) {
							$d['event_data']['res']['results'][$r]['invocation']['module_args'] = clean_null_args($d['event_data']['res']['results'][$r]['invocation']['module_args']);
						}
						if (isset($d['event_data']['res']['results'][$r]['diff'])) {
							for ($a = 0; $a < count($d['event_data']['res']['results'][$r]['diff']); $a++) {
								if (isset($d['event_data']['res']['results'][$r]['diff'][$a]['before']) && isset($d['event_data']['res']['results'][$r]['diff'][$a]['after'])) {
									$d['event_data']['res']['results'][$r]['diff'][$a] = create_diff($d['event_data']['res']['results'][$r]['diff']);
								}
							}
						}
					}
				}

				if (isset($d['event_data']['res']['diff'])) {
					if (isset($d['event_data']['res']['invocation']['module_args'])) {
						$d['event_data']['res']['invocation']['module_args'] = clean_null_args($d['event_data']['res']['invocation']['module_args']);
					}
					for ($a = 0; $a < count($d['event_data']['res']['diff']); $a++) {
						if ($remove_invocation && isset($d['event_data']['res']['invocation'])) {
							unset($d['event_data']['res']['invocation']);
						}
						if (isset($d['event_data']['res']['diff'][$a]['before']) && isset($d['event_data']['res']['diff'][$a]['after'])) {
							$d['event_data']['res']['diff'][$a] = create_diff($d['event_data']['res']['diff']);
						}
					}
				}

				$h = check_host($d['host_name']);
				$role = (isset($d['role']) ? $d['role'] : '');
				$res = Yaml::dumper($d['event_data']['res']);
				db_execute_prepare('INSERT INTO `changes` (`host`, `time`, `job`, `playbook`, `play`, `role`, `task`, `task_action`, `res`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array($h, time(), $d['job'], $d['playbook'], $d['play'], $role, $d['task'], $d['event_data']['task_action'], $res));
			}
			break;
		case 'awx.analytics.activity_stream':
			// JOB DATA
			if (isset($d['operation']) && $d['operation'] == 'create' && isset($d['object1']) && $d['object1'] == 'job') {
				include_once('includes/sql.php');

//file_put_contents('jobs.txt', print_r($d, true), FILE_APPEND);

				if (!isset($d['summary_fields']['job_template'][0]['id'])) {
					// Older versions of Tower don't specify the job_template_id
					$t = explode('-', $d['changes']['job_template']);
					$jtid = $t[array_key_last($t)];
				} else {
					$jtid = $d['summary_fields']['job_template'][0]['id'];
				}

				db_execute_prepare('INSERT INTO `jobs` (`timestamp`, `job`, `job_template_id`, `host`, `name`, `job_type`, `inventory`, `project`, `scm_branch`, `execution_environment`, `actor`, `limit`) VALUES
									(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
									array($d['@timestamp'], $d['changes']['id'], $jtid,
										  $d['host'], $d['changes']['name'], $d['changes']['job_type'], $d['changes']['inventory'],
										  $d['changes']['project'], $d['changes']['scm_branch'], (isset($d['changes']['execution_environment']) ? $d['changes']['execution_environment'] : ''), 
										  $d['actor'], $d['changes']['limit']
										));
			}
			break;
	}
}

function clean_null_args($inv) {
	$ninv = array();
	foreach ($inv as $k => $i) {
		if (!is_null($i)) {
			$ninv[$k] = $i;
		}
	}
	return $ninv;
}

function create_diff($res) {
	$from = $to = (isset($res[1]['before_header']) ? $res[1]['before_header'] : '');

	$builder = new StrictUnifiedDiffOutputBuilder([
		'collapseRanges'      => true, // ranges of length one are rendered with the trailing `,1`
		'commonLineThreshold' => 6,    // number of same lines before ending a new hunk and creating a new one (if needed)
		'contextLines'        => 3,    // like `diff:  -u, -U NUM, --unified[=NUM]`, for patch/git apply compatibility best to keep at least @ 3
		'fromFile'            => $from,
		'fromFileDate'        => null,
		'toFile'              => $to,
		'toFileDate'          => null,
	]);

	$differ = new Differ($builder);
	$diff = $differ->diff($res[0]['before'], $res[0]['after']);
	return color_diff($diff);
}

function color_diff($diff) {
	$diff = explode("\n", $diff);
	for ($a = 0; $a < count($diff); $a++) {
		if (isset($diff[$a][0])) {
			if ($diff[$a][0] == '+') {
				$diff[$a] = '<font color=green>' . $diff[$a] . '</font>';
			}
			if ($diff[$a][0] == '-') {
				$diff[$a] = '<font color=red>' . $diff[$a] . '</font>';
			}
			if ($diff[$a][0] == '@') {
				$diff[$a] = '<font color=blue>' . $diff[$a] . '</font>';
			}
		}
	}
	return implode("\n", $diff);
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
	$time = time();
	if (isset($h['id'])) {
		db_execute_prepare('UPDATE `hosts` SET `time` = ? WHERE `id` = ?', array($time, $h['id']));
		return $h['id'];
	} else {
		$h = db_execute_prepare('INSERT INTO `hosts` (`hostname`, `time`) VALUES (?, ?)', array(strtolower($host), $time));
		return $h;
	}
}
