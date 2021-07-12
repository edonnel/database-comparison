<?
	namespace database {
		class database {
			private $name, $conn;
			private $tables = array();

			public function __construct($host, $user, $pass, $name) {
				$this->name = $name;

				$this->conn = mysqli_connect($host, $user, $pass, $name);

				if (mysqli_connect_errno()) {
					printf("Connect failed: %s\n", mysqli_connect_error());
					die();
				} else {
					$this->get_tables_data();
				}
			}

			/**
			 * @desc Combines tables from two databases and returns list of table names
			 * @param database $database_1
			 * @param database $database_2
			 * @return array|mixed Array of table names
			 */
			public static function combine_tables(database $database_1, database $database_2) {
				$tables_1 = $database_1->get_tables();
				$tables_2 = $database_2->get_tables();

				$func_combine = function($tables, $tables_to_combine = array()) {
					foreach ($tables as $table) {
						$table_name = $table->get_name();

						if (!in_array($table_name, $tables_to_combine))
							$tables_to_combine[] = $table_name;
					}

					return $tables_to_combine;
				};

				$tables = $func_combine($tables_1);
				$tables = $func_combine($tables_2, $tables);

				sort($tables);

				return $tables;
			}

			/**
			 * @desc Combines columns from two databases and returns list of column names
			 * @param database $database_1
			 * @param database $database_2
			 * @return array|mixed Associative array of column names with tables as the parent keys
			 */
			public static function combine_columns(database $database_1, database $database_2) {
				$tables_1 = $database_1->get_tables();
				$tables_2 = $database_2->get_tables();

				$func_combine = function($tables, $columns = array()) {
					foreach ($tables as $table) {
						$table_name     = $table->get_name();
						$table_columns  = $table->get_columns();

						if ($table_columns) {

							if (!isset($columns[$table_name]))
								$columns[$table_name] = array();

							foreach ($table_columns as $column_name => $column) {

								if (!in_array($column_name, $columns[$table_name]))
									$columns[$table_name][] = $column_name;
							}
						}
					}

					return $columns;
				};

				$columns = $func_combine($tables_1);
				$columns = $func_combine($tables_2, $columns);

				ksort($columns);

				foreach ($columns as $key => $x)
					sort($columns[$key]);

				return $columns;
			}

			/**
			 * @desc Combines indexes from two databases and returns list of index names
			 * @param database $database_1
			 * @param database $database_2
			 * @return array|mixed Associative array of index names with tables as the parent keys
			 */
			public static function combine_indexes(database $database_1, database $database_2) {
				$tables_1 = $database_1->get_tables();
				$tables_2 = $database_2->get_tables();

				$func_combine = function($tables, $indexes = array()) {
					foreach ($tables as $table) {
						$table_name     = $table->get_name();
						$table_indexes  = $table->get_indexes();

						if ($table_indexes) {

							if (!isset($indexes[$table_name]))
								$indexes[$table_name] = array();

							foreach ($table_indexes as $index_name => $table_index) {

								if (!in_array($index_name, $indexes[$table_name]))
									$indexes[$table_name][] = $index_name;
							}
						}
					}

					return $indexes;
				};

				$indexes = $func_combine($tables_1);
				$indexes = $func_combine($tables_2, $indexes);

				ksort($indexes);

				foreach ($indexes as $key => $x)
					sort($indexes[$key]);

				return $indexes;
			}

			/**
			 * @desc Combines constraints from two databases and returns list of constraint names
			 * @param database $database_1
			 * @param database $database_2
			 * @return array Associative array of constraint names with tables as the parent keys
			 */
			public static function combine_constraints(database $database_1, database $database_2) {
				$constraints = array();

				$func_combine = function($tables, $constraints) {
					foreach ($tables as $table) {
						$table_name         = $table->get_name();
						$table_constraints  = $table->get_constraints();

						if ($table_constraints) {

							if (!isset($constraints[$table_name]))
								$constraints[$table_name] = array();

							foreach ($table_constraints as $constraint_name => $table_constraint) {

								if (!in_array($constraint_name, $constraints[$table_name]))
									$constraints[$table_name][] = $constraint_name;
							}
						}
					}

					return $constraints;
				};

				$tables_1 = $database_1->get_tables();
				$tables_2 = $database_2->get_tables();

				$constraints = $func_combine($tables_1, $constraints);
				$constraints = $func_combine($tables_2, $constraints);

				ksort($constraints);

				foreach ($constraints as $key => $x)
					sort($constraints[$key]);

				return $constraints;

			}

			public static function get_table_changes(database $db, database $db_other) {

				$all_tables = \database\database::combine_tables($db, $db_other);

				$changes = new \changes();

				foreach ($all_tables as $table_name) {
					$table          = $db->get_table($table_name);
					$table_other    = $db_other->get_table($table_name);

					if ($table) {
						$change = new \change;
						$diff   = false;

						if ($table_other) {

							if ($table->get_engine() != $table_other->get_engine()) {
								$diff = true;
								$change->add_reason('engine');
							}

							if ($table->get_charset() != $table_other->get_charset()) {
								$diff = true;
								$change->add_reason('charset');
							}

							if ($diff) {
								$change->set_object($table);
								$changes->add($change);
							}
						} else
							$changes->add($table, 'new');
					} else
						$changes->add($table_other, 'dne');
				}

				return $changes->get();
			}

			public static function get_column_changes(database $db, database $db_other) {

				$all_columns = \database\database::combine_columns($db, $db_other);

				$changes = new \changes();

				foreach ($all_columns as $table_name => $table_columns) {
					$table          = $db->get_table($table_name);
					$table_other    = $db_other->get_table($table_name);

					if ($table) {

						foreach ($table_columns as $column_name) {
							$column = $table->get_column($column_name);

							$change = new \change;
							$diff   = false;

							if ($column) {

								if ($table_other) {
									$column_name = $column->get_name();

									if ($column_other = $table_other->get_column($column_name)) {

										if ($column->get_name() != $column_other->get_name()) {
											$diff = true;
											$change->add_reason('name');
										}

										if ($column->get_type() != $column_other->get_type()) {
											$diff = true;
											$change->add_reason('type');
										}

										if ($column->is_null() != $column_other->is_null()) {
											$diff = true;
											$change->add_reason('null');
										}

										if ($column->get_default() != $column_other->get_default()) {
											$diff = true;
											$change->add_reason('default');
										}

										if ($column->is_auto_increment() != $column_other->is_auto_increment()) {
											$diff = true;
											$change->add_reason('auto_increment');
										}

									} else {
										$diff = true;
										$change->add_reason('new');
									}

									if ($diff) {
										$change->set_object($column);
										$changes->add($change);
									}
								}
							} else {
								$changes->add($table_other->get_column($column_name), 'dne');
							}
						}
					}
				}

				return $changes->get();
			}

			public static function get_constraint_changes(database $db, database $db_other) {

				$all_constraints = \database\database::combine_constraints($db, $db_other);

				$changes = new \changes();

				foreach ($all_constraints as $table_name => $table_constraints) {
					$table          = $db->get_table($table_name);
					$table_other    = $db_other->get_table($table_name);

					if ($table) {

						foreach ($table_constraints as $constraint_name) {
							$constraint = $table->get_constraint($constraint_name);

							$change = new \change;
							$diff   = false;

							if ($constraint) {

								if ($table_other) {
									$constraint_name = $constraint->get_name();

									if ($constraint_other = $table_other->get_constraint($constraint_name)) {

										if ($constraint->get_col_name() != $constraint_other->get_col_name()) {
											$diff = true;
											$change->add_reason('col');
										}

										if ($constraint->get_ref_db_name() != $constraint_other->get_ref_db_name() && !($constraint->get_ref_db_name() == $db->get_name() && $constraint_other->get_ref_db_name() == $db_other->get_name())) {
											$diff = true;
											$change->add_reason('ref_db');
										}

										if ($constraint->get_ref_table_name() != $constraint_other->get_ref_table_name()) {
											$diff = true;
											$change->add_reason('ref_table');
										}

										if ($constraint->get_ref_col_name() != $constraint_other->get_ref_col_name()) {
											$diff = true;
											$change->add_reason('ref_col');
										}

										if ($constraint->get_update_rule() != $constraint_other->get_update_rule()) {
											$diff = true;
											$change->add_reason('update');
										}

										if ($constraint->get_delete_rule() != $constraint_other->get_delete_rule()) {
											$diff = true;
											$change->add_reason('delete');
										}

									} else {
										$diff = true;
										$change->add_reason('new');
									}

									if ($diff) {

										// if col on current db doesn't exist, add reason so we can flag it
										if ($constraint->get_ref_db_name() == $db->get_name()) {
											$db_to   = $db_other;
											$db_from = $db;
										} elseif ($constraint->get_ref_db_name() == $db_other->get_name()) {
											$db_to   = $db;
											$db_from = $db_other;
										} else {
											$db_to   = null;
											$db_from = null;
										}

										if (!$table_other->has_column($constraint->get_col_name()))
											$change->add_reason('col_dne');

										if ($db_to) {
											$ref_table_name = $constraint->get_ref_table_name();

											// if col on db doesn't exist, add reason so we can flag it
											if ($table_to = $db_to->get_table($ref_table_name)) {

												if (!$table_to->has_column($constraint->get_ref_col_name()))
													$change->add_reason('ref_col_dne');
											}

											if (!$db_other->has_table($ref_table_name))
												$change->add_reason('table_dne');
										} else
											$change->add_reason('ref_mne');     // referenced values might not exist

										$change->set_object($constraint);
										$changes->add($change);
									}

									if ($table_other->get_engine() != 'InnoDB')
										$change->add_reason('diff_engine');
								}
							} else {
								$changes->add($table_other->get_constraint($constraint_name), 'dne');
							}
						}
					}
				}

				return $changes->get();
			}

			public static function get_index_changes(database $db, database $db_other) {

				$all_indexes = \database\database::combine_indexes($db, $db_other);

				$changes = new \changes();

				foreach ($all_indexes as $table_name => $table_indexes_names) {
					$table          = $db->get_table($table_name);
					$table_other    = $db_other->get_table($table_name);

					if ($table) {

						foreach ($table_indexes_names as $index_name) {
							$index = $table->get_index($index_name);

							$change = new \change();

							if ($index) {

								if ($table_other) {
									$index_other = $table_other->get_index($index_name);

									$diff  = false;
									$class = '';

									if ($index_other) {

										if ($index->is_unique() != $index_other->is_unique()) {
											$diff = true;
											$change->add_reason('unique');
										}

										if ($index->get_type() != $index_other->get_type()) {
											$diff = true;
											$change->add_reason('type');
										}

										// compare index columns
										foreach ($index->get_columns() as $index_column_name => $index_column) {
											if ($table_other->has_column($index_column_name)) {
												if (!$index_other->has_column($index_column_name)) {
													$diff = true;
													$change->add_reason('col');
												}
											} else {
												$diff = true;
												$change->add_reason('col_dne');
											}
										}

										if ($diff) {
											$change->set_object($index);
											$index_change = true;
											$class        = 'diff';

											$changes->add($change);
										}
									} else {
										$change = new \change($index, 'new');

										// compare index columns
										foreach ($index->get_columns() as $index_column_name => $index_column) {
											if (!$table_other->has_column($index_column_name)) {
												$diff = true;
												$change->add_reason('col_dne');
											}
										}

										$changes->add($change);
									}
								}
							} else
								$changes->add($table_other->get_index($index_name), 'dne');
						}
					}
				}

				return $changes->get();
			}

			private function add_table(table $table) {
				if ($table_name = $table->get_name())
					$this->tables[$table_name] = $table;
				else
					$this->tables[] = $table;
			}

			public function has_table($table_name) {
				return key_exists($table_name, $this->tables);
			}

			public function get_table($table_name) {
				if ($this->has_table($table_name))
					return $this->tables[$table_name];
				else
					return false;
			}

			public function get_name() {
				return $this->name;
			}

			public function get_tables() {
				return $this->tables;
			}

			private function get_tables_data() {
				// get tables
				$stmt = "
					SELECT 
					    TABLE_NAME,
				        ENGINE,
				        TABLE_COLLATION,
					    CHARACTER_SET_NAME
					FROM INFORMATION_SCHEMA.TABLES is_t
					LEFT JOIN INFORMATION_SCHEMA.COLLATION_CHARACTER_SET_APPLICABILITY is_csa ON is_t.TABLE_COLLATION = is_csa.COLLATION_NAME
					WHERE TABLE_SCHEMA = '$this->name'";

				if ($query = mysqli_query($this->conn, $stmt)) {

					// for each table
					while ($r = mysqli_fetch_assoc($query)) {
						$table_name = $r['TABLE_NAME'];
						$engine     = $r['ENGINE'];
						$collation  = $r['TABLE_COLLATION'];
						$charset    = $r['CHARACTER_SET_NAME'];

						$table = new table($table_name);
						$table->set_engine($engine);
						$table->set_collation($collation);
						$table->set_charset($charset);

						// get columns
						$stmt = "
							SELECT 
							    COLUMN_NAME,
							    COLUMN_TYPE,
						        COLUMN_KEY,
						        IS_NULLABLE,
						        COLUMN_DEFAULT,
							    EXTRA
							FROM INFORMATION_SCHEMA.COLUMNS
							WHERE
							    TABLE_SCHEMA = '$this->name' AND 
							    TABLE_NAME = '$table_name'";

						if ($query_2 = mysqli_query($this->conn, $stmt)) {

							// for each column
							while ($r = mysqli_fetch_assoc($query_2)) {
								$column_name    = $r['COLUMN_NAME'];
								$column_type    = $r['COLUMN_TYPE'];
								$column_key     = $r['COLUMN_KEY'];
								$is_nullable    = $r['IS_NULLABLE'];
								$default        = $r['COLUMN_DEFAULT'];
								$column_extra   = $r['EXTRA'];

								$column = new column();
								$column
									->set_name($column_name)
									->set_type($column_type)
									->set_default($default)
									->set_table_name($table_name);

								if ($column_extra == 'auto_increment')
									$column->set_auto_increment(true);
								else {
									$column->set_auto_increment(false);
									$column->set_extra($column_extra);
								}

								if ($is_nullable === 'YES')
									$column->set_null(true);
								else
									$column->set_null(false);

								$table->add_column($column);
							}
						}

						// get indexes
						$indexes = $this->get_table_indexes($table_name);
						$table->set_indexes($indexes);

						$this->add_table($table);
					}

					// get constraints
					$this->get_tables_constraints();

					mysqli_free_result($query);
				}
			}

			private function get_table_constraints($table_name) {
				$constraints = array();

				$stmt = "
					SELECT
						KEY_COLUMN_USAGE.CONSTRAINT_NAME as `CONSTRAINT_NAME`,
				        KEY_COLUMN_USAGE.TABLE_NAME as `TABLE_NAME`,
						UPDATE_RULE,
						DELETE_RULE,
						COLUMN_NAME,
						KEY_COLUMN_USAGE.REFERENCED_TABLE_SCHEMA as `REFERENCED_TABLE_SCHEMA`,
					    KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME as `REFERENCED_TABLE_NAME`,
					    REFERENCED_COLUMN_NAME
					FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS ON INFORMATION_SCHEMA.KEY_COLUMN_USAGE.CONSTRAINT_NAME = INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS.CONSTRAINT_NAME
					WHERE 
					    KEY_COLUMN_USAGE.TABLE_NAME = '$table_name' AND 
				        TABLE_SCHEMA = '$this->name' AND 
				        KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME IS NOT NULL";

				$query = mysqli_query($this->conn, $stmt);

				if ($query) {
					while ($r = mysqli_fetch_assoc($query)) {
						$constraint = new constraint();
						$constraint
							->set_name($r['CONSTRAINT_NAME'])
							->set_table_name($r['TABLE_NAME'])
							->set_delete_rule($r['DELETE_RULE'])
							->set_update_rule($r['UPDATE_RULE'])
							->set_col_name($r['COLUMN_NAME'])
							->set_ref_db_name($r['REFERENCED_TABLE_SCHEMA'])
							->set_ref_table_name($r['REFERENCED_TABLE_NAME'])
							->set_ref_col_name($r['REFERENCED_COLUMN_NAME']);

						$constraints[] = $constraint;
					}
				}

				return $constraints;
			}

			private function get_tables_constraints() {
				$stmt = "
					SELECT
				        KEY_COLUMN_USAGE.TABLE_NAME as `TABLE_NAME`,
						KEY_COLUMN_USAGE.CONSTRAINT_NAME as `CONSTRAINT_NAME`,
						UPDATE_RULE,
						DELETE_RULE,
						COLUMN_NAME,
						KEY_COLUMN_USAGE.REFERENCED_TABLE_SCHEMA as `REFERENCED_TABLE_SCHEMA`,
					    KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME as `REFERENCED_TABLE_NAME`,
					    REFERENCED_COLUMN_NAME
					FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS ON INFORMATION_SCHEMA.KEY_COLUMN_USAGE.CONSTRAINT_NAME = INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS.CONSTRAINT_NAME
					WHERE 
				        TABLE_SCHEMA = '$this->name' AND 
				        KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME IS NOT NULL";
				$query = mysqli_query($this->conn, $stmt);

				while ($r = mysqli_fetch_assoc($query)) {
					$constraint = new constraint();
					$constraint
						->set_name($r['CONSTRAINT_NAME'])
						->set_table_name($r['TABLE_NAME'])
						->set_delete_rule($r['DELETE_RULE'])
						->set_update_rule($r['UPDATE_RULE'])
						->set_col_name($r['COLUMN_NAME'])
						->set_ref_db_name($r['REFERENCED_TABLE_SCHEMA'])
						->set_ref_table_name($r['REFERENCED_TABLE_NAME'])
						->set_ref_col_name($r['REFERENCED_COLUMN_NAME']);

					$table_name = $r['TABLE_NAME'];

					$this->tables[$table_name]->add_constraint($constraint);
				}
			}

			private function get_table_indexes($table_name) {
				$indexes_raw    = array();
				$indexes        = array();

				$stmt = "SHOW INDEXES IN `$this->name`.`$table_name`";
				$query = mysqli_query($this->conn, $stmt);

				while ($r = mysqli_fetch_assoc($query))
					$indexes_raw[$r['Key_name']][] = $r;

				foreach ($indexes_raw as $index_name => $index_columns) {
					$index = new index();
					$index
						->set_name($index_name)
						->set_db_name($this->name)
						->set_table_name($table_name)
						->set_type($index_columns[0]['Index_type'])
						->set_unique(!$index_columns[0]['Non_unique']);

					foreach ($index_columns as $index_column) {
						$column = new index_column();
						$column
							->set_name($index_column['Column_name'])
							->set_cardinality($index_column['Cardinality'])
							->set_collation($index_column['Collation']);

						if ($index_column['Null'] != '')
							$column->set_null($index_column['Null']);

						$index->add_column($column);
					}

					$indexes[$index_name] = $index;
				}

				return $indexes;
			}
		}

		class table {
			private $name, $engine, $collation, $charset;
			private $columns = array();
			private $indexes = array();
			private $constraints = array(); // array of constraint objects
			private $columns_not_existing = array();    // for database comparison

			public function __construct($name = false) {
				if ($name)
					$this->set_name($name);
			}

			public function set_name($name) {
				$this->name = $name;

				return $this;
			}

			public function set_engine($engine) {
				$this->engine = $engine;

				return $this;
			}

			public function set_constraints($constraints) {
				$this->constraints = $constraints;

				return $this;
			}

			public function set_columns_not_existing($columns) {
				$this->columns_not_existing = $columns;

				return $this;
			}

			public function add_column_not_existing(column $column) {
				$this->columns_not_existing[$column->get_name()] = $column;

				return $this;
			}

			public function add_constraint(constraint $constraint) {
				$this->constraints[$constraint->get_name()] = $constraint;

				return $this;
			}

			/**
			 * @param array $indexes assoc array of index objects
			 * @return $this
			 */
			public function set_indexes($indexes) {
				$this->indexes = $indexes;

				return $this;
			}

			public function set_collation($collation) {
				$this->collation = $collation;

				return $this;
			}

			public function set_charset($charset) {
				$this->charset = $charset;

				return $this;
			}

			public function has_column($column_name) {
				return key_exists($column_name, $this->columns);
			}

			public function get_constraints() {
				return $this->constraints;
			}

			public function get_column($column_name) {
				if ($this->has_column($column_name))
					return $this->columns[$column_name];
				else
					return false;
			}

			// takes either a columns object or a string as a column_name
			public function add_column($args) {
				$args = func_get_args();

				if ($args[0] instanceof column) {
					$column = $args[0];
					$column_name = $column->get_name();
				} else {
					$column_name = $args[0];

					if (isset($args[1]) && $args[1])
						$column_type = $args[1];
					else
						$column_type = false;

					$column = new column();
					$column->set_name($column_name);

					if ($column_type)
						$column->set_type($column_type);
				}

				$this->columns[$column_name] = $column;

				return $this;
			}

			public function get_name() {
				return $this->name;
			}

			public function get_columns() {
				return $this->columns;
			}

			public function get_engine() {
				return $this->engine;
			}

			public function get_index($index_name) {
				if ($this->has_index($index_name))
					return $this->indexes[$index_name];
				else
					return false;
			}

			public function get_indexes() {
				return $this->indexes;
			}

			public function get_columns_not_existing() {
				return $this->columns_not_existing;
			}

			public function has_index($index_name) {
				return key_exists($index_name, $this->indexes);
			}

			public function has_constraint($constraint_name) {
				return key_exists($constraint_name, $this->constraints);
			}

			public function get_constraint($constraint_name) {
				if ($this->has_constraint($constraint_name))
					return $this->constraints[$constraint_name];
				else
					return false;
			}

			public function get_collation() {
				return $this->collation;
			}

			public function get_charset() {
				return $this->charset;
			}

			public static function build_insert_query(table $table, $db_name) {
				$table_name     = $table->get_name();
				$table_engine   = $table->get_engine();
				$table_charset  = $table->get_charset();

				$stmt_table = "CREATE TABLE `$db_name`.`$table_name` (";

				// get columns
				$columns  = $table->get_columns();
				$columns_ = array();

				foreach ($columns as $column_name => $column) {
					$column_type    = $column->get_type();
					$column_null    = !$column->is_null() ? 'NOT NULL' : '';
					$column_default = $column->get_default() ? 'DEFAULT '.$column->get_default() : '';
					$column_extra   = $column->get_extra();

					$columns_[] = "\r\t`$column_name` $column_type $column_null $column_default $column_extra";
				}

				$stmt_table .= implode(',', $columns_);

				$stmt_table .= "\r) ENGINE=$table_engine DEFAULT CHARSET=$table_charset";

				return $stmt_table;
			}
		}

		class column {
			private $name, $type, $extra, $default = null, $auto_increment = false, $null = true;
			private $table_name;

			public function set_name($name) {
				$this->name = $name;

				return $this;
			}

			public function set_type($type) {
				$this->type = $type;

				return $this;
			}

			public function set_table_name($table_name) {
				$this->table_name = $table_name;

				return $this;
			}

			public function set_default($default) {
				$this->default = $default;

				return $this;
			}

			public function set_auto_increment(bool $auto_increment = false) {
				$this->auto_increment = $auto_increment;

				return $this;
			}

			public function set_null(bool $null = true) {
				$this->null = $null;

				return $this;
			}

			public function set_extra($extra) {
				$this->extra = $extra;

				return $this;
			}

			public function get_name() {
				return $this->name;
			}

			public function get_type() {
				return $this->type;
			}

			public function get_table_name() {
				return $this->table_name;
			}

			public function get_default() {
				return $this->default;
			}

			public function is_auto_increment() {
				return $this->auto_increment;
			}

			public function is_null() : bool {
				return $this->null;
			}

			public function get_extra() {
				return $this->extra;
			}

			public static function build_insert_query(column $column, $db_name, $table_name, $type = 'ADD') {
				$column_name    = $column->get_name();
				$column_type    = $column->get_type();
				$column_default = $column->get_default() ? 'DEFAULT '.$column->get_default() : '';
				$column_null    = !$column->is_null() ? 'NOT NULL' : '';
				$column_ai      = $column->is_auto_increment() ? 'AUTO_INCREMENT' : '';

				if ($type == 'ADD')
					$type = 'ADD';
				elseif ($type == 'MODIFY')
					$type = 'MODIFY';
				else
					$type = 'ADD';

				return "ALTER TABLE `$db_name`.`$table_name` $type COLUMN `$column_name` $column_type $column_default $column_null $column_ai";
			}
		}

		class index_column {
			private $name, $cardinality, $collation, $null = false;

			public function get_name() {
				return $this->name;
			}

			public function set_name($name) {
				$this->name = $name;

				return $this;
			}

			public function set_cardinality($cardinality) {
				$this->cardinality = $cardinality;

				return $this;
			}

			public function set_collation($collation) {
				$this->collation = $collation;

				return $this;
			}

			public function set_null($null = false) {
				$this->null = $null;
				return $this;
			}

			public function get_cardinality() {
				return $this->cardinality;
			}

			public function get_collation() {
				return $this->collation;
			}

			public function is_null() {
				return $this->null;
			}
		}

		class index {
			private $name, $type, $unique = false;
			private $db_name, $table_name;
			private $columns;

			public function set_name($name) {
				$this->name = $name;

				return $this;
			}

			public function set_type($type) {
				$this->type = $type;

				return $this;
			}

			public function set_unique($unique) {
				$this->unique = $unique;

				return $this;
			}

			public function set_table_name($table_name) {
				$this->table_name = $table_name;

				return $this;
			}

			public function set_db_name($db_name) {
				$this->db_name = $db_name;

				return $this;
			}

			public function add_column(index_column $index_column) {
				$this->columns[$index_column->get_name()] = $index_column;

				return $this;
			}

			public function get_name() {
				return $this->name;
			}

			public function get_type() {
				return $this->type;
			}

			public function is_unique() {
				return $this->unique;
			}

			public function get_table_name() {
				return $this->table_name;
			}

			public function get_db_name() {
				return $this->db_name;
			}

			public function get_columns() {
				return $this->columns;
			}

			public function get_column($column_name) {
				if ($this->has_column($column_name))
					return $this->columns[$column_name];
				else
					return false;
			}

			public function has_column($column_name) {
				return key_exists($column_name, $this->columns);
			}

			public static function build_insert_query(index $index, $db_name, $table_name, $drop = false) {
				$cols           = array();
				$index_columns  = $index->get_columns();
				$index_type     = $index->get_type() ? "USING ".$index->get_type() : '';
				$index_unique   = $index->is_unique() ? "UNIQUE" : '';

				foreach ($index_columns as $index_column)
					$cols[] = "`".$index_column->get_name()."`";

				$cols = implode(',', $cols);

				$index_name        = $index->get_name();
				$first_column_name = $index_columns[array_key_first($index_columns)]->get_name();

				if ($index_name == 'PRIMARY')
					$stmt = "PRIMARY KEY (`$first_column_name`)";
				elseif ($index_name == 'FULLTEXT')
					$stmt = "FULLTEXT KEY `$index_name` $index_unique ($cols) $index_type";
				else
					$stmt = "KEY `$index_name` ($cols) $index_type";

				$stmt = "ALTER TABLE `$db_name`.`$table_name` ADD $stmt";

				if ($drop)
					$stmt = "ALTER TABLE `$db_name`.`$table_name` DROP INDEX `$index_name`;"."\r".$stmt;

				return $stmt;
			}
		}

		class constraint {
			private
				$name,
				$table_name,
				$delete_rule,
				$update_rule,
				$column_name,
				$ref_db_name,
				$ref_table_name,
				$ref_col_name;

			public function set_name($name) {
				$this->name = $name;

				return $this;
			}

			public function set_table_name($table_name) {
				$this->table_name = $table_name;

				return $this;
			}

			public function set_delete_rule($delete_rule) {
				$this->delete_rule = $delete_rule;

				return $this;
			}

			public function set_update_rule($update_rule) {
				$this->update_rule = $update_rule;

				return $this;
			}

			public function set_col_name($column_name) {
				$this->column_name = $column_name;

				return $this;
			}

			public function set_ref_db_name($ref_db_name) {
				$this->ref_db_name = $ref_db_name;

				return $this;
			}

			public function set_ref_table_name($ref_table_name) {
				$this->ref_table_name = $ref_table_name;

				return $this;
			}

			public function set_ref_col_name($ref_col_name) {
				$this->ref_col_name = $ref_col_name;

				return $this;
			}

			public function get_update_rule() {
				return $this->update_rule;
			}

			public function get_name() {
				return $this->name;
			}

			public function get_table_name() {
				return $this->table_name;
			}

			public function get_delete_rule() {
				return $this->delete_rule;
			}

			public function get_col_name() {
				return $this->column_name;
			}

			public function get_ref_db_name() {
				return $this->ref_db_name;
			}

			public function get_ref_table_name() {
				return $this->ref_table_name;
			}

			public function get_ref_col_name() {
				return $this->ref_col_name;
			}

			public static function build_insert_query(constraint $constraint, $db_name, $table_name, $drop = false) {
				$constraint_name            = $constraint->get_name();
				$constraint_col_name        = $constraint->get_col_name();
				$constraint_ref_db_name     = $constraint->get_ref_db_name();
				$constraint_ref_table_name  = $constraint->get_ref_table_name();
				$constraint_ref_col_name    = $constraint->get_ref_col_name();
				$constraint_delete_rule     = $constraint->get_delete_rule();
				$constraint_update_rule     = $constraint->get_update_rule();

				$stmt = "ALTER TABLE `$db_name`.`$table_name` ADD CONSTRAINT `$constraint_name` FOREIGN KEY (`$constraint_col_name`) REFERENCES `$constraint_ref_db_name`.`$constraint_ref_table_name` (`$constraint_ref_col_name`) ON DELETE $constraint_delete_rule ON UPDATE $constraint_update_rule";

				if ($drop)
					$stmt = "ALTER TABLE `$db_name`.`$table_name` DROP FOREIGN KEY `$constraint_name`;"."\r".$stmt;

				return $stmt;
			}
		}
	}