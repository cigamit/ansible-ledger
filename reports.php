<?php
include_once('includes/global.php');

if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['report'] == intval($_GET['report']) && intval($_GET['report'])) {
	$report = new Report($_GET['report']);
	if ($report->id) {
		$report->delete();
	}
	Header("Location: /reports/\n\n");
	exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'new') {
	$report = new Report();
	$report->set_owner($account['id']);
	$report->set_created(time());
	$report->set_name('New Report - ' . $account['name']);
	$report->save();
	Header("Location: /reports/edit/" . intval($report->id) . "\n\n");
	exit;
}

if (isset($_POST['action']) && $_POST['action'] == 'savesort' && $_POST['report'] == intval($_POST['report']) && intval($_POST['report'])) {
	$report = new Report(intval($_POST['report']));
	if ($report->id) {
		$sortc = intval($_POST['sortc']);
		$sortd = $_POST['sortd'];
		$report->set_sortc($sortc);
		$report->set_sortd($sortd);
		$report->save();
		Header("Location: /reports/edit/" . intval($_POST['report']) . "\n\n");
		exit;
	}
}

if (isset($_POST['action']) && $_POST['action'] == 'savename' && $_POST['report'] == intval($_POST['report']) && intval($_POST['report'])) {
	$report = new Report(intval($_POST['report']));
	if ($report->id) {
		$name = sql_clean_ans($_POST['name']);
		$report->set_name($name);
		$report->save();
		Header("Location: /reports/edit/" . intval($_POST['report']) . "\n\n");
		exit;
	}
}

if (isset($_GET['action']) && $_GET['action'] == 'deletefilter' && $_GET['report'] == intval($_GET['report']) && intval($_GET['report'])) {
	$report = new Report(intval($_REQUEST['report']));
	if ($report->id) {
		$filter = intval($_GET['filter']);
		$report->remove_filter($filter);
		Header("Location: /reports/edit/" . intval($_GET['report']) . "\n\n");
		exit;
	}
}


if (isset($_POST['action']) && $_POST['action'] == 'addfilter' && $_POST['report'] == intval($_POST['report']) && intval($_POST['report'])) {
	$report = new Report(intval($_POST['report']));
	if ($report->id) {
		$value = $_POST['value'];
		$compare = $_POST['compare'];
		$fact = $_POST['fact'];
		$report->add_filter($fact, $compare, $value);
		Header("Location: /reports/edit/" . intval($_POST['report']) . "\n\n");
		exit;
	}
}

if (isset($_GET['action']) && $_GET['action'] == 'moveup' && $_GET['report'] == intval($_GET['report']) && intval($_GET['report'])) {
	$report = new Report(intval($_REQUEST['report']));
	if ($report->id) {
		$fact = intval($_GET['fact']);
		$report->move_column_up($fact);
		Header("Location: /reports/edit/" . intval($_GET['report']) . "\n\n");
		exit;
	}
}

if (isset($_GET['action']) && $_GET['action'] == 'movedown' && $_GET['report'] == intval($_GET['report']) && intval($_GET['report'])) {
	$report = new Report(intval($_REQUEST['report']));
	if ($report->id) {
		$fact = intval($_GET['fact']);
		$report->move_column_down($fact);
		Header("Location: /reports/edit/" . intval($_GET['report']) . "\n\n");
		exit;
	}
}

if (isset($_GET['action']) && $_GET['action'] == 'deletefact' && $_GET['report'] == intval($_GET['report']) && intval($_GET['report'])) {
	$report = new Report(intval($_REQUEST['report']));
	if ($report->id) {
		$fact = intval($_GET['fact']);
		$report->remove_column($fact);
		Header("Location: /reports/edit/" . intval($_GET['report']) . "\n\n");
		exit;
	}
}

if (isset($_POST['action']) && $_POST['action'] == 'addcolumn' && $_POST['report'] == intval($_POST['report']) && intval($_POST['report'])) {
	$report = new Report(intval($_POST['report']));
	if ($report->id) {
		$display = sql_clean_ans($_POST['display']);
		if ($display != '') {
			$facts = (isset($_POST['facts']) ? $_POST['facts'] : array());
			$report->add_column($display, $facts);
		}
		Header("Location: /reports/edit/" . intval($_POST['report']) . "\n\n");
		exit;
	}
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['report'] == intval($_REQUEST['report']) && intval($_REQUEST['report'])) {
	$report = db_fetch_assoc_prepare('SELECT * FROM reports WHERE `id` = ?', array(intval($_REQUEST['report'])));
	if (isset($report['id'])) {
		$allfacts = db_fetch_assocs('SELECT DISTINCT `fact` FROM facts');
		$facts = array();
		foreach ($allfacts as $f) {
			$facts[] = $f['fact'];
		}

		$filters = @unserialize(base64_decode($report['filters']));
		$columns = @unserialize(base64_decode($report['columns']));
		echo $twig->render('report_edit.html', array_merge($twigarr, array('report' => $report, 'facts' => $facts, 'filters' => $filters, 'columns' => $columns, 'compares' => $compares)));
		exit;
	}
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view' && $_REQUEST['report'] == intval($_REQUEST['report']) && intval($_REQUEST['report'])) {
	$report = new Report(intval($_REQUEST['report']));
	if ($report->id) {
		$w = build_filter($report->filters);
		
		$sql = $w[0];
		$pr = $w[1];

		$hosts = db_fetch_assocs_prepare($sql, $pr);

		$data = array();
		foreach ($hosts as $h) {
			$facts = db_fetch_assocs_prepare('SELECT `fact`,`data` FROM `facts` WHERE `host` = ?', array($h['host']));
			$x = 0;
			$data[$h['host']] = array();
			if (count($facts)) {
				foreach ($report->columns as $d => $k) {
					$data[$h['host']][$x] = '';
					foreach ($facts as $f) {
						if (is_array($k)) {
							if (in_array($f['fact'], $k)) {
								if ($data[$h['host']][$x] != '') {
									$data[$h['host']][$x] .= ', ';
								}
								$data[$h['host']][$x] .= $f['data'];
							}
						} else {
							if ($k == $f['fact']) {
								$data[$h['host']][$x] = $f['data'];
							}
						}
					}
					$x++;
				}
			}
			$e = true;
			foreach ($data[$h['host']] as $d) {
				if ($d != '') {
					$e = false;
				}
			}
			if ($e) {
				unset($data[$h['host']]);
			}
		}

		echo $twig->render('report.html', array_merge($twigarr, array('report' => $report, 'data' => $data, 'filters' => $report->filters, 'columns' => $report->columns, 'sortc' => $report->sortc, 'sortd' => $report->sortd)));
		exit;
	} else {

		Header("Location: /reports/\n\n");
		exit;
	}
}

$reports = db_fetch_assocs_prepare("SELECT `reports`.*, `reports_perms`.`role` FROM `reports`
							LEFT JOIN `reports_perms` ON `reports_perms`.`report` = `reports`.`id`
							WHERE `reports_perms`.`user` = ?
							ORDER BY `reports`.`name`", array($account['id']));

$users = reindex_arr_by_id_col(db_fetch_assocs('SELECT `id`, `name` FROM `users`'), 'name');

echo $twig->render('reports.html', array_merge($twigarr, array('reports' => $reports, 'users' => $users, 'compares' => $compares)));

function build_filter($filters) {

	$sql = "SELECT DISTINCT f.`host` FROM `facts` as f\n";
	$p = array();
	$c = 0;
	foreach ($filters as $f) {
		$c++;

		$sql .= "INNER JOIN (SELECT * FROM `facts` WHERE `fact`= ? AND ";

		$p[] = $f['fact'];
		switch ($f['compare']) {
			case 'eq':
				$sql .= '`data` = ?';
				$p[] = $f['value'];
				break;
			case 'ne':
				$sql .= '`data` != ?';
				$p[] = $f['value'];
				break;
			case 'gt':
				$sql .= 'cast(`data` as signed) > ?';
				$p[] = $f['value'];
				break;
			case 'lt':
				$sql .= '`cast(data` as signed) < ?';
				$p[] = $f['value'];
				break;
			case 'contains':
				$sql .= '`data` LIKE ?';
				$p[] = "%" . $f['value'] . "%";
				break;
			case 'starts':
				$sql .= '`data` LIKE ?';
				$p[] = "%" . $f['value'];
				break;
			case 'ends':
				$sql .= '`data` LIKE ?';
				$p[] = "%" . $f['value'];
				break;
		}
		$sql .= ') as f' . $c . ' ON f.host = f' . $c . ".host\n";
	}

	return array($sql, $p);
}
