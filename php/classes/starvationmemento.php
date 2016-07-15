<?php
	class StarvationMemento implements Serializable {
		
		private $m_data = array();
		
		public function __construct(array $data) {
			$this->m_data = $data;
			
			foreach ($this->m_data as $key => $value) {
				Debug::echo_info("this data " . $key . "," . $value);
			}
		}
		
		public function &getDataRef() {
			return $this->m_data;
		}
		
		public function getEntry($entryName) {
			return $this->m_data[$entryName];
		}
		
		public function serialize() {
			return json_encode($this->m_data);
		}
		
		public function unserialize($data) {
			$this->m_data = json_decode($data, true);
		}
	}
?>