<?
	namespace database_comparison;

	function array_key_first(array $arr) {
		foreach($arr as $key => $unused)
			return $key;

		return null;
	}

	function render_tables(database $db, database $db_other, $header, $position = 'right') {
		$header         = ucwords($header);
		$changed_tables = database::get_table_changes($db, $db_other);

		require THIS_DIR.'/view/tables.php';
	}

	function render_columns(database $db, database $db_other, $header, $position = 'right') {
		$header             = ucwords($header);
		$changed_columns    = database::get_column_changes($db, $db_other);

		require THIS_DIR.'/view/columns.php';
	}

	function render_indexes(database $db, database $db_other, $header, $position = 'right') {
		$header             = ucwords($header);
		$changed_indexes    = database::get_index_changes($db, $db_other);

		require THIS_DIR.'/view/indexes.php';
	}

	function render_constraints(database $db, database $db_other, $header, $position = 'right') {
		$header                 = ucwords($header);
		$changed_constraints    = database::get_constraint_changes($db, $db_other);

		require THIS_DIR.'/view/constraints.php';
	}

	function get_file_contents($file_path, $data = array()) {
		if (file_exists($file_path)) {
			ob_start();
			extract($data);
			include($file_path);
			return ob_get_clean();
		} else
			return 'File <b>'.$file_path.'</b> does not exist.';
	}

	function get_db_from_name($db_name) : database {
		global $database_prod, $database_stag;

		if ($database_prod->get_name() == $db_name)
			return $database_prod;

		if ($database_stag->get_name() == $db_name)
			return $database_stag;

		die('Database <b>'.$db_name.'</b> does not exist in this setup.');
	}

	function get_conn_from_db_name($db_name) {
		if (db_cred_prod['name'] == $db_name)
			return mysqli_connect(db_cred_prod['host'], db_cred_prod['user'], db_cred_prod['pass'], db_cred_prod['name']);

		if (db_cred_stag['name'] == $db_name)
			return mysqli_connect(db_cred_stag['host'], db_cred_stag['user'], db_cred_stag['pass'], db_cred_stag['name']);

		die('Database specified was not valid.');
	}

	function start_the_session() {
		if (session_status() === PHP_SESSION_NONE)
			@session_start();
	}