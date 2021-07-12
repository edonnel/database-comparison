<?
	if (!function_exists('array_key_first')) {
		function array_key_first(array $arr) {
			foreach($arr as $key => $unused)
				return $key;

			return null;
		}
	}

	function render_tables(database\database $db, database\database $db_other, $header, $position = 'right') {
		$header         = ucwords($header);
		$changed_tables = \database\database::get_table_changes($db, $db_other);

		require THIS_DIR.'/view/tables.php';
	}

	function render_columns(database\database $db, database\database $db_other, $header, $position = 'right') {
		$header             = ucwords($header);
		$changed_columns    = \database\database::get_column_changes($db, $db_other);

		require THIS_DIR.'/view/columns.php';
	}

	function render_indexes(database\database $db, database\database $db_other, $header, $position = 'right') {
		$header             = ucwords($header);
		$changed_indexes    = \database\database::get_index_changes($db, $db_other);

		require THIS_DIR.'/view/indexes.php';
	}

	function render_constraints(database\database $db, database\database $db_other, $header, $position = 'right') {
		$header                 = ucwords($header);
		$changed_constraints    = \database\database::get_constraint_changes($db, $db_other);

		require THIS_DIR.'/view/constraints.php';
	}

	if (!function_exists('get_file_contents')) {
		function get_file_contents($file_path, $data = array()) {
			if (file_exists($file_path)) {
				ob_start();
				extract($data);
				include($file_path);
				return ob_get_clean();
			} else
				return 'File <b>'.$file_path.'</b> does not exist.';
		}
	}

	function log_message_redirect($text, $type, $title, $redirect) {
		if (session_status() === PHP_SESSION_NONE)
			session_start();

		$_SESSION['log_msg'] = array(
			'text'  => $text,
			'type'  => $type,
			'title' => $title,
		);

		header('Location: '.$redirect);
		die();
	}

	function get_db_from_name($db_name) : \database\database {
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