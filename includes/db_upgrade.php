<?php

$new_db_version = 3;

$db_version = db_fetch_cell("SELECT `value` FROM settings WHERE `setting` = 'db_version'", 'value');
if ($db_version == '') {
	db_execute("INSERT INTO `settings` (`setting`, `value`) VALUES ('db_version', 0)");
	$db_version = 1;
}

if ($new_db_version != $db_version) {
	switch ($db_version) {
		case 2:
			@db_execute('ALTER TABLE `users` ADD `super` tinyint(1) NOT NULL DEFAULT '0';');

	}

	db_execute_prepare("UPDATE `settings` SET `value` = ? WHERE `setting` = 'db_version'", array($new_db_version));
}
