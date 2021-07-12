<?
	if (session_status() === PHP_SESSION_NONE)
		@session_start();

	$push_acts = array(
		'push_table',
		'push_column',
		'push_index',
		'push_constraint',
	);

	$drop_acts = array(
		'drop_table',
		'drop_column',
		'drop_index',
		'drop_constraint',
	);

	$valid_acts = array_merge($push_acts, $drop_acts);

	if (in_array($_GET['act'], $valid_acts)) {

		if (!$_GET['table'])
			log_message_redirect('Table was not specified.', 'error', 'Error', THIS_URL_FULL);

		$table_name = $_GET['table'];

		if (in_array($_GET['act'], $push_acts)) {

			if (!($db_src_name = $_GET['db_src']))
				log_message_redirect('Source database was not specified.', 'error', 'Error', THIS_URL_FULL);

			if (!($db_dest_name = $_GET['db_dest']))
				log_message_redirect('Destination database was not specified.', 'error', 'Error', THIS_URL_FULL);

			$db_src       = get_db_from_name($db_src_name);
			$db_dest      = get_db_from_name($db_dest_name);
			$conn_src     = get_conn_from_db_name($db_src_name);
			$conn_dest    = get_conn_from_db_name($db_dest_name);

			$table_src  = $db_src->get_table($table_name);
			$table_dest = $db_dest->get_table($table_name);

			/*-- PUSH TABLE --*/

			if ($_GET['act'] == 'push_table') {

				if ($table_src) {

					$engine  = $table_src->get_engine();
					$charset = $table_src->get_charset();

					$builder = new \sql\builder($conn_dest);

					// table exists
					if ($table_dest) {
						$stmt[] = "ALTER TABLE `$db_dest_name`.`$table_name` ENGINE = $engine";
						$stmt[] = "ALTER TABLE `$db_dest_name`.`$table_name` CONVERT TO CHARACTER SET $charset";
					} else {

						// get columns
						$columns  = $table_src->get_columns();

						$builder->add_stmt(\database\table::build_insert_query($table_src, $db_dest_name));

						// indexes

						foreach ($table_src->get_indexes() as $index_name => $index)
							$builder->add_stmt(\database\index::build_insert_query($index, $db_dest_name, $table_name));

						// auto increment

						foreach ($columns as $column_name => $column) {
							$column_type = $column->get_type();
							$column_null = !$column->is_null() ? 'NOT NULL' : '';

							if ($column->is_auto_increment())
								$builder->add_stmt("ALTER TABLE `$db_dest_name`.`$table_name` MODIFY COLUMN `$column_name` $column_type $column_null AUTO_INCREMENT");
						}

						// constraints

						foreach ($table_src->get_constraints() as $constraint_name => $constraint) {
							$constraint_ref_db_name    = $constraint->get_ref_db_name();
							$constraint_ref_table_name = $constraint->get_ref_table_name();
							$constraint_ref_col_name   = $constraint->get_ref_table_name();

							// use the right database
							if ($constraint_ref_db_name == $db_src_name)
								$constraint_ref_db_name = $db_dest_name;

							// if dest db ref table exists
							if ($constraint_ref_table = $db_dest->get_table($constraint_ref_table_name)) {

								// if ref col exists
								if ($constraint_ref_table->has_column($constraint_ref_col_name))
									\database\constraint::build_insert_query($constraint, $db_dest_name, $table_name);
							}
						}
					}

					$result = $builder->exec();
					$stmt   = $result->get_data('stmt');

					if ($result->is_success()) {
						$_SESSION['last_stmt'] = $stmt;

						log_action('Pushed the table "'.$table_name.'" from database "'.$db_src_name.'" to database "'.$db_dest_name.'"');
						log_message_redirect('Table <b>'.$table_name.'</b> pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.', 'success', 'Table Pushed', THIS_URL_FULL);
					} else
						log_message_redirect('Table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.<br><br>Error: '.$result->get_data('error').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Table Push Error', THIS_URL_FULL);
				} else
					log_message_redirect('Table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Table does not exist on source database.', 'error', 'Table Push Error', THIS_URL_FULL);
			}

			/*-- PUSH COLUMN --*/

			if ($_GET['act'] == 'push_column') {

				if (!($column_name = $_GET['column']))
					log_message_redirect('Column was not specified.', 'error', 'Error', THIS_URL_FULL);

				$builder = new \sql\builder($conn_dest);

				if ($table_dest) {

					if ($column_src = $table_src->get_column($column_name)) {

						// if column exists, else, create column
						if ($table_dest->has_column($column_name))
							$builder->add_stmt(\database\column::build_insert_query($column_src, $db_dest_name, $table_name, 'MODIFY'));
						else
							$builder->add_stmt(\database\column::build_insert_query($column_src, $db_dest_name, $table_name, 'ADD'));

						$result = $builder->exec();
						$stmt   = $result->get_data('stmt');

						if ($result->is_success()) {
							$_SESSION['last_stmt'] = $stmt;

							log_action('Pushed the column "'.$column_name.'" on table "'.$table_name.'" from database "'.$db_src_name.'" to database "'.$db_dest_name.'"');
							log_message_redirect('Column <b>'.$column_name.'</b> on table <b>'.$table_name.'</b> pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.', 'success', 'Column Pushed', THIS_URL_FULL);
						} else
							log_message_redirect('Column <b>'.$column_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.<br><br>Error: '.$result->get_data('error').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Column Push Error', THIS_URL_FULL);
					} else
						log_message_redirect('Column <b>'.$column_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Column does not exist on source database', 'error', 'Column Push Error', THIS_URL_FULL);
				} else
					log_message_redirect('Column <b>'.$column_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Table does not exist on destination database', 'error', 'Column Push Error', THIS_URL_FULL);
			}

			/*-- PUSH INDEX --*/

			if ($_GET['act'] == 'push_index') {

				if (!($index_name = $_GET['index']))
					log_message_redirect('Index was not specified.', 'error', 'Error', THIS_URL_FULL);

				if ($table_dest) {

					if ($index_src = $table_src->get_index($index_name)) {

						$builder = new \sql\builder($conn_dest);

						// if constraint exists
						if ($table_dest->has_index($index_name))
							$builder->add_stmt(\database\index::build_insert_query($index_src, $db_dest_name, $table_name, true));
						else
							$builder->add_stmt(\database\index::build_insert_query($index_src, $db_dest_name, $table_name));

						$result = $builder->exec();
						$stmt   = $result->get_data('stmt');

						if ($result->is_success()) {
							$_SESSION['last_stmt'] = $stmt;

							log_action('Pushed the index "'.$index_name.'" on table "'.$table_name.'" from database "'.$db_src_name.'" to database "'.$db_dest_name.'"');
							log_message_redirect('Index <b>'.$index_name.'</b> on table <b>'.$table_name.'</b> pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.', 'success', 'Index Pushed', THIS_URL_FULL);
						} else
							log_message_redirect('Index <b>'.$index_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.<br><br>Error: '.$result->get_data('error').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Index Push Error', THIS_URL_FULL);
					} else
						log_message_redirect('Index <b>'.$index_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Index does not exist on source database.', 'error', 'Index Push Error', THIS_URL_FULL);
				} else
					log_message_redirect('Index <b>'.$index_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Table does not exist on destination database.', 'error', 'Index Push Error', THIS_URL_FULL);
			}

			/*-- PUSH CONSTRAINT --*/

			if ($_GET['act'] == 'push_constraint') {

				if (!($constraint_name = $_GET['constraint']))
					log_message_redirect('Constraint was not specified.', 'error', 'Error', THIS_URL_FULL);

				if ($table_dest) {

					if ($constraint_src = $table_src->get_constraint($constraint_name)) {

						$builder = new \sql\builder($conn_dest);

						// if constraint exists
						if ($table_dest->has_constraint($constraint_name))
							$builder->add_stmt(\database\constraint::build_insert_query($constraint_src, $db_dest_name, $table_name, true));
						else
							$builder->add_stmt(\database\constraint::build_insert_query($constraint_src, $db_dest_name, $table_name));

						$result = $builder->exec();
						$stmt   = $result->get_data('stmt');

						if ($result->is_success()) {
							$_SESSION['last_stmt'] = $stmt;

							log_action('Pushed the constraint "'.$constraint_name.'" on table "'.$table_name.'" from database "'.$db_src_name.'" to database "'.$db_dest_name.'"');
							log_message_redirect('Constraint <b>'.$constraint_name.'</b> on table <b>'.$table_name.'</b> pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.', 'success', 'Constraint Pushed', THIS_URL_FULL);
						} else
							log_message_redirect('Constraint <b>'.$constraint_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>.<br><br>Error: '.$result->get_data('stmt').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Constraint Push Error', THIS_URL_FULL);
					} else
						log_message_redirect('Constraint <b>'.$constraint_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Index does not exist on source database.', 'error', 'Constraint Push Error', THIS_URL_FULL);
				} else
					log_message_redirect('Constraint <b>'.$constraint_name.'</b> on table <b>'.$table_name.'</b> could not be pushed from database <b>'.$db_src_name.'</b> to database <b>'.$db_dest_name.'</b>. Table does not exist on destination database.', 'error', 'Constraint Push Error', THIS_URL_FULL);
			}
		}

		if (in_array($_GET['act'], $drop_acts)) {

			if (!$_GET['db'])
				log_message_redirect('Database was not specified.', 'error', 'Error', THIS_URL_FULL);

			$db_name = $_GET['db'];
			$conn    = get_conn_from_db_name($db_name);

			$builder = new \sql\builder($conn);

			/*-- DROP TABLE --*/

			if ($_GET['act'] == 'drop_table') {

				$builder->add_stmt("DROP TABLE `$db_name`.`$table_name`");

				$result = $builder->exec();
				$stmt   = $result->get_data('stmt');

				if ($result->is_success()) {
					$_SESSION['last_stmt'] = $stmt;

					log_action('Dropped the table "'.$table_name.'" from database "'.$db_name.'"');
					log_message_redirect('Table <b>'.$table_name.'</b> dropped from database <b>'.$db_name.'</b>.', 'success', 'Table Dropped', THIS_URL_FULL);
				} else
					log_message_redirect('Table <b>'.$table_name.'</b> could not be dropped from database <b>'.$db_name.'</b>.<br><br>Error: '.$result->get_data('stmt').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Table Drop Error', THIS_URL_FULL);
			}

			/*-- DROP COLUMN --*/

			if ($_GET['act'] == 'drop_column') {

				if (!($column_name = $_GET['column']))
					log_message_redirect('Column was not specified.', 'error', 'Error', THIS_URL_FULL);

				$builder->add_stmt("ALTER TABLE `$db_name`.`$table_name` DROP COLUMN `$column_name`");

				$result = $builder->exec();
				$stmt   = $result->get_data('stmt');

				if ($result->is_success()) {
					$_SESSION['last_stmt'] = $stmt;

					log_action('Dropped the column "'.$column_name.'" on table "'.$table_name.'" from database "'.$db_name.'"');
					log_message_redirect('Column <b>'.$column_name.'</b> on table <b>'.$table_name.'</b> dropped from database <b>'.$db_name.'</b>.', 'success', 'Table Dropped', THIS_URL_FULL);
				} else
					log_message_redirect('Column <b>'.$column_name.'</b> on table <b>'.$table_name.'</b> could not be dropped from database <b>'.$db_name.'</b>.<br><br>Error: '.$result->get_data('stmt').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Table Drop Error', THIS_URL_FULL);
			}

			/*-- DROP INDEX --*/

			if ($_GET['act'] == 'drop_index') {

				if (!($index_name = $_GET['index']))
					log_message_redirect('Index was not specified.', 'error', 'Error', THIS_URL_FULL);

				$builder->add_stmt("ALTER TABLE `$db_name`.`$table_name` DROP INDEX `$index_name`");

				$result = $builder->exec();
				$stmt   = $result->get_data('stmt');

				if ($result->is_success()) {
					$_SESSION['last_stmt'] = $stmt;

					log_action('Dropped the index "'.$index_name.'" on table "'.$table_name.'" from database "'.$db_name.'"');
					log_message_redirect('Index <b>'.$index_name.'</b> on table <b>'.$table_name.'</b> dropped from database <b>'.$db_name.'</b>.', 'success', 'Index Dropped', THIS_URL_FULL);
				} else
					log_message_redirect('Index <b>'.$index_name.'</b> on table <b>'.$table_name.'</b> could not be dropped from database <b>'.$db_name.'</b>.<br><br>Error: '.$result->get_data('error').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Index Drop Error', THIS_URL_FULL);
			}

			/*-- DROP CONSTRAINT --*/

			if ($_GET['act'] == 'drop_constraint') {

				if (!($constraint_name = $_GET['constraint']))
					log_message_redirect('Constraint was not specified.', 'error', 'Error', THIS_URL_FULL);

				$builder->add_stmt("ALTER TABLE `$db_name`.`$table_name` DROP FOREIGN KEY `$constraint_name`");

				$result = $builder->exec();
				$stmt   = $result->get_data('stmt');

				if ($result->is_success()) {
					$_SESSION['last_stmt'] = $stmt;

					log_action('Dropped the constraint "'.$constraint_name.'" on table "'.$table_name.'" from database "'.$db_name.'"');
					log_message_redirect('Constraint <b>'.$constraint_name.'</b> on table <b>'.$table_name.'</b> dropped from database <b>'.$db_name.'</b>.', 'success', 'Constraint Dropped', THIS_URL_FULL);
				} else
					log_message_redirect('Constraint <b>'.$constraint_name.'</b> on table <b>'.$table_name.'</b> could not be dropped from database <b>'.$db_name.'</b>.<br><br>Error: '.$result->get_data('error').'<br><br>Statement:<br><pre>'.$stmt.'</pre>', 'error', 'Constraint Drop Error', THIS_URL_FULL);
			}
		}
	}

	// TODO: make push alls

	if (isset($_SESSION['log_msg'])) {
		log_message($_SESSION['log_msg']['text'], $_SESSION['log_msg']['type'], $_SESSION['log_msg']['title']);
		unset($_SESSION['log_msg']);
	}