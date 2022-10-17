<?php
include_once('includes/global.php');

$users = reindex_arr_by_id_col(db_fetch_assocs('SELECT `id`, `name` FROM `users`'), 'name');

if (isset($_REQUEST['action'])) {
	if (isset($_REQUEST['report']) && $_REQUEST['report'] == intval($_REQUEST['report']) && $_REQUEST['report']) {
		$report = new Report(intval($_REQUEST['report']));
		if ($report->id) {
			switch ($_REQUEST['action']) {
				case 'delete':
					$report->delete();
					Header("Location: /reports/\n\n");
					exit;
				case 'adduserperm':
					$user = intval($_POST['user']);
					$role = $_POST['role'];
					$report->add_user($user, $role);
					Header("Location: /reports/perms/" . $report->id . "\n\n");
					exit;
				case 'removeuserperm':
					$user = intval($_GET['user']);
					$report->remove_user($user);
					Header("Location: /reports/perms/" . $report->id . "\n\n");
					exit;
				case 'savesort':
					$sortc = intval($_POST['sortc']);
					$sortd = $_POST['sortd'];
					$report->set_sortc($sortc);
					$report->set_sortd($sortd);
					$report->save();
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'savename':
					$name = sql_clean_ans($_POST['name']);
					$report->set_name($name);
					$report->save();
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'deletefilter':
					$filter = intval($_GET['filter']);
					$report->remove_filter($filter);
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'addfilter':
					$value = $_POST['value'];
					$compare = $_POST['compare'];
					$fact = $_POST['fact'];
					$report->add_filter($fact, $compare, $value);
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'moveup':
					$fact = intval($_GET['fact']);
					$report->move_column_up($fact);
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'movedown':
					$fact = intval($_GET['fact']);
					$report->move_column_down($fact);
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'deletefact':
					$fact = intval($_GET['fact']);
					$report->remove_column($fact);
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'addcolumn':
					$display = sql_clean_ans($_POST['display']);
					if ($display != '') {
						$facts = (isset($_POST['facts']) ? $_POST['facts'] : array());
						$report->add_column($display, $facts);
					}
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
				case 'perms':
					$perms = reindex_arr_by_col(db_fetch_assocs_prepare('SELECT * FROM `reports_perms` WHERE `report` = ?', array($report->id)), 'user');
					$roles = array('owner' => 'Owner', 'edit' => 'Editor', 'view' => 'Viewer');
					echo $twig->render('report_perms.html', array_merge($twigarr, array('report' => $report, 'perms' => $perms, 'users' => $users, 'roles' => $roles)));
					exit;
				case 'view':
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
				case 'edit':
					$allfacts = db_fetch_assocs('SELECT DISTINCT `fact` FROM facts');
					$facts = array();
					foreach ($allfacts as $f) {
						$facts[] = $f['fact'];
					}
					echo $twig->render('report_edit.html', array_merge($twigarr, array('report' => $report, 'facts' => $facts, 'filters' => $report->filters, 
							'columns' => $report->columns, 'compares' => $compares)));
					exit;
			}
		} else {
			switch ($_REQUEST['action']) {
				case 'new':
					$report = new Report();
					$report->set_owner($account['id']);
					$report->set_created(time());
					$report->set_name('New Report - ' . $account['name']);
					$report->save();
					Header("Location: /reports/edit/" . $report->id . "\n\n");
					exit;
			}
		}
	}
}

$reports = db_fetch_assocs_prepare("SELECT `reports`.*, `reports_perms`.`role` FROM `reports`
							LEFT JOIN `reports_perms` ON `reports_perms`.`report` = `reports`.`id`
							WHERE `reports_perms`.`user` = ?
							ORDER BY `reports`.`name`", array($account['id']));


echo $twig->render('reports.html', array_merge($twigarr, array('reports' => $reports, 'users' => $users, 'compares' => $compares)));
