<?php

include_once('includes/global.php');





$hosts = reindex_arr_by_id(db_fetch_assocs("SELECT * FROM `hosts` ORDER BY `hostname`"));
$ufacts = reindex_col(db_fetch_assocs("SELECT UNIQUE `fact` FROM `facts` ORDER BY `fact` ASC"), 'fact');

$filters = array();
$host = '';
$fact = '';

if (isset($_GET['host']) && intval($_GET['host']) == $_GET['host'] && isset($hosts[$_GET['host']])) {
	$host = intval($_GET['host']);
	$filters[] = " `host` = $host";
}

if (isset($_GET['fact']) && in_array($_GET['fact'], $ufacts)) {
	$fact = $_GET['fact'];
	$filters[] = " `fact` = '$fact'";
}

$h = array();
foreach ($hosts as $h2) {
	$h[$h2['id']] = $h2['hostname'];
}
if (!empty($filters)) {
	$filters = "WHERE " . implode(' AND ', $filters);
} else {
	$filters = '';
}

$facts = db_fetch_assocs("SELECT * FROM `facts` $filters ORDER BY `fact` ASC");

echo $twig->render('facts.html', array_merge($twigarr, array('facts' => $facts, 'ufacts' => $ufacts, 'hosts' => $h, 'host' => $host, 'fact' => $fact)));
