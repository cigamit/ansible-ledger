<?php

$new_db_version = 3;

$db_version = read_setting('db_version', 1);

if ($new_db_version != $db_version) {
	try {
		switch ($db_version) {
			case 2:
				db_execute("ALTER TABLE `users` ADD `super` tinyint(1) NOT NULL DEFAULT '0';");


		}
	} catch (Exception $e) {

	}
	db_execute_prepare("UPDATE `settings` SET `value` = ? WHERE `setting` = 'db_version'", array($new_db_version));
}
