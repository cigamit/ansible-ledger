<?php

$new_db_version = 2;

$db_version = db_fetch_cell("SELECT `value` FROM settings WHERE `setting` = 'db_version'", 'value');
if ($db_version == '') {
	db_execute("INSERT INTO `settings` (`setting`, `value`) VALUES ('db_version', 0)");
	$db_version = 1;
}
if ($new_db_version != $db_version) {
	switch ($db_version) {
		case 1:
			db_execute('ALTER TABLE `changes` DROP `tower`');

	}

	db_execute_prepare("UPDATE `settings` SET `value` = ? WHERE `setting` = 'db_version'", array($new_db_version));
}
