<?
	class result {
		private $success = false;
		private $data = array();
		private $msg;

		public function __construct($success = null) {
			if ($success !== null)
				$this->set_success($success);
		}

		public function set_success(bool $success) : result {
			$this->success = $success;

			return $this;
		}

		public function set_msg($msg) : result {
			$this->msg = $msg;

			return $this;
		}

		public function set_data($key, $value) : result {
			$this->data[$key] = $value;

			return $this;
		}

		public function is_success() : bool {
			return $this->success;
		}

		public function get_msg() : string {
			return $this->msg;
		}

		public function get_data($key) {
			if (key_exists($key, $this->data))
				return $this->data[$key];
			else
				return null;
		}

		public function return_array() {
			return array(
				'success'   => $this->success,
				'msg'       => $this->msg,
				'data'      => $this->data,
			);
		}

		public function return_json() {
			return json_encode($this->return_array());
		}

		public function echo_json($die = false) {
			echo $this->return_json();

			if ($die)
				die();
		}
	}