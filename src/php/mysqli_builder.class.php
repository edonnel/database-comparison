<?

	namespace sql {

		class builder {
			private $conn;
			private $statements = array();

			public function __construct(\mysqli $conn) {
				$this->conn = $conn;

				return $this;
			}

			public function set_conn(\mysqli $conn) {
				if (!$conn)
					die('Database connection error. '.mysqli_connect_error());

				$this->conn = $conn;

				return $this;
			}

			public function add_stmt($stmt) {
				$this->statements[] = $stmt;

				return $this;
			}

			public function prepend_stmt($stmt) {
				array_unshift($this->statements, $stmt);

				return $this;
			}

			private function build_stmt() : string {
				$this->prepend_stmt("START TRANSACTION");
				$this->prepend_stmt("SET AUTOCOMMIT = 0");
				$this->prepend_stmt("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");

				$this->add_stmt("COMMIT");

				$stmt = implode("\r", array_map(function ($x) {
					return ($x.';');
				}, $this->statements));

				return $stmt;
			}

			public function exec() : \result {
				$stmt = $this->build_stmt();

				$query = mysqli_multi_query($this->conn, $stmt);

				$result = new \result;
				$result->set_data('stmt', $stmt);

				if ($query) {

					do {
						if (!mysqli_more_results($this->conn)) {
							$result->set_success(true);

							return $result;
						}

						if (!mysqli_next_result($this->conn) || mysqli_errno($this->conn)) {
							$result
								->set_success(false)
								->set_data('error', mysqli_error($this->conn));

							return $result;
						}
					} while(true);

				} else {
					$result
						->set_success(false)
						->set_data('error', mysqli_error($this->conn));

					return $result;
				}
			}
		}
	}