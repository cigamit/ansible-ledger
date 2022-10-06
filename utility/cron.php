<?php

chdir(dirname(getcwd()));

require_once('includes/version.php');
require_once('includes/sql.php');
require_once('vendor/autoload.php');
#require_once('includes/arrays.php');
require_once('includes/email.php');
#require_once('includes/misc.php');

$changes_retention = read_setting('changes_retension', 30);

$cr = time() - ($changes_retention * 86400);

db_execute_prepare('DELETE FROM `changes` WHERE `time` < ?', array($cr));