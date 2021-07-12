<?
	class change {
		private $changed_object, $changed_object_other;
		private $reasons = array();
		private $data = array();

		public function __construct($changed_object = null, $reason = null) {
			if ($changed_object)
				$this->set_object($changed_object);

			if ($reason)
				$this->add_reason($reason);

			return $this;
		}

		public function set_object($object) { $this->changed_object = $object; return $this; }

		public function set_object_other($object) { $this->changed_object_other = $object; return $this; }

		public function add_reason($reason) { $this->reasons[] = $reason; return $this; }

		public function set_data($key, $value) { $this->data[$key] = $value; }

		public function get_object() { return $this->changed_object; }

		public function get_object_other() { return $this->changed_object_other; }

		public function get_reasons() { return $this->reasons; }

		public function has_reason($reason) { return in_array($reason, $this->reasons); }

		public function get_reason($reason) { return $this->has_reason($reason) ? $reason : false; }

		public function get_data($key) { return $this->data[$key] ?? null; }
	}

	class changes {
		private $changes = array();

		// takes either a change object or (preferably) any other object
		public function add($change_or_object, $reason = '') {

			if (!$change_or_object instanceof change)
				$change = new change($change_or_object);
			else
				$change = $change_or_object;

			if ($reason) {
				if (is_array($reason)) {
					foreach ($reason as $r)
						$change->add_reason($r);
				} else
					$change->add_reason($reason);
			}

			$this->changes[] = $change;

			return $this;
		}

		public function get() { return $this->changes; }
	}