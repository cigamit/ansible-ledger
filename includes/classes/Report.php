<?php

class Report {
	var $id = 0;
	var $owner = 0;
	var $name = 'New Report';
	var $created = 0;
	var $filters = array();
	var $columns = array('Hostname' => 'ansible_hostname');
	var $sortc = 0;
	var $sortd = 'asc';

	function __construct($id = 0) {
		if ($id) {
			$this->retrieve($id);
		}
   	}

	function retrieve($id) {
		$u = db_fetch_assoc_prepare('SELECT * FROM `reports` WHERE id = ?', array($id));
		$this->pop_class($u);
		return $u;
	}

	function set_owner($owner) {
		$this->owner = $owner;
	}

	function set_name($name) {
		$this->name = sql_clean_ans($name);
	}

	function set_created($created) {
		$this->created = $created;
	}

	function set_filters($password) {
		$this->filters = $filters;
	}

	function set_columns($columns) {
		$this->columns = $columns;
	}

	function set_sortc($sortc) {
		$this->sortc = intval($sortc);
	}

	function set_sortd($sortd) {
		if ($sortd == 'asc') {
			$this->sortd = 'asc';
		} else {
			$this->sortd = 'desc';
		}
	}

       function pop_class($u) {
		if (isset($u['id'])) {
			$this->id = $u['id'];
			$this->owner = $u['owner'];
			$this->name = $u['name'];

			$this->created = $u['created'];
			$this->filters = unserialize(base64_decode($u['filters']));
			$this->columns = unserialize(base64_decode($u['columns']));
			$this->sortc = $u['sortc'];
			$this->sortd = $u['sortd'];
		}
	}

	function add_filter($fact, $compare, $value) {
		$value = sql_clean_ans($value);

		$allfacts = db_fetch_assocs('SELECT DISTINCT `fact` FROM facts');
		$afs = array();
		foreach ($allfacts as $f) {
			$afs[] = $f['fact'];
		}

		if (!in_array($fact, $afs)) {
			return false;
		}

		$this->filters[] = array('fact' => $fact, 'compare' => $compare, 'value' => $value);
		$this->save();
	}

	function remove_filter($i) {
		unset($this->filters[$i]);
		$this->save();
	}

	function add_column($display, $facts) {
		$display = sql_clean_ans($display);
		$allfacts = db_fetch_assocs('SELECT DISTINCT `fact` FROM facts');
		$afs = array();
		foreach ($allfacts as $f) {
			$afs[] = $f['fact'];
		}

		for ($a = 0; $a < count($facts); $a++) {
			if (!in_array($facts[$a], $afs)) {
				unset($facts[$a]);
			}
		}

		if (count($facts) > 1) {
			$this->columns[$display] = $facts;
		} else {
			$this->columns[$display] = $facts[0];
		}
		$this->save();
	}

	function remove_column($i) {
		$a = 0;
		foreach ($this->columns as $k => $d) {
			if ($a == $i) {
				print $k;
				unset($this->columns[$k]);
			}
			$a++;
		}
		$this->save();
	}

	function move_column_up($i) {
		$new = array();
		$a = 0;
		$ok = '';
		$od = '';
		foreach ($this->columns as $k => $d) {
			if ($a == $i - 1) {
				$ok = $k;
				$od = $d;
			} else if ($a == $i) {
				$n[$k] = $d;
				$n[$ok] = $od;
			} else {
				$n[$k] = $d;
			}
			$a++;
		}
		$this->columns = $n;
		$this->save();
	}

	function move_column_down($i) {
		$new = array();
		$a = 0;
		$ok = '';
		$od = '';
		foreach ($this->columns as $k => $d) {
			if ($a == $i) {
				$ok = $k;
				$od = $d;
			} else if ($a == $i + 1) {
				$n[$k] = $d;
				$n[$ok] = $od;
			} else {
				$n[$k] = $d;
			}
			$a++;
		}
		$this->columns = $n;
		$this->save();
	}

	function clean_name($text) {
		return preg_replace('/[^A-Za-z0-9 ]/', '', $text);
	}

	function delete() {
		db_execute_prepare('DELETE FROM `reports` WHERE `id` = ?', array($this->id));
	}

	function save() {
		if ($this->id) {
			db_execute_prepare('UPDATE `reports` SET `owner` = ?, `name` = ?, `created` = ?, `filters` = ?, `columns` = ?, `sortc` = ?, `sortd` = ? WHERE `id` = ?',
						array($this->owner, $this->name, $this->created, base64_encode(serialize($this->filters)), base64_encode(serialize($this->columns)), $this->sortc, $this->sortd, $this->id));
		} else {
			$id = db_execute_prepare('INSERT INTO `reports` (`owner`, `name`, `created`, `filters`, `columns`, `sortc`, `sortd`) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array($this->owner, $this->name, $this->created,  base64_encode(serialize($this->filters)),base64_encode(serialize($this->columns)), $this->sortc, $this->sortd));
			$this->id = $id;
		}
	}
}