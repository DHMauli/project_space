<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));
	
	class ProcessUnitTranslate extends Process implements ISerializeChild, IGetProcessDataChild {
		
		protected $m_start = "";
		protected $m_target = "";
		
		protected $m_units = array();
		
		public function __construct($name, $category, $duration, $prevProcDuration, array $additionalInfo) {
			parent::__construct($name, $category, $duration, $prevProcDuration);
			
			$this->m_start = $additionalInfo[0];
			$this->m_target = $additionalInfo[1];
			$this->m_units = $additionalInfo[2];
			
			UnitInterface::Instance()->removeUnitsFromPlanet($additionalInfo[0], $additionalInfo[2]);
		}
		
		public function serializeChild(array &$serializingArray) {
			$serializingArray['start'] = $this->m_start;
			$serializingArray['target'] = $this->m_target;
			$serializingArray['units'] = json_encode($this->m_units);
		}
		
		public function unserializeChild(array $arrData) {
			$this->m_start = $arrData['start'];
			$this->m_target = $arrData['target'];
			$this->m_units = json_decode($arrData['units'], true);
		}
		
		public function getProcessDataChild(array &$arrData) {
			$arrData['start'] = $this->m_start;
			$arrData['target'] = $this->m_target;
			$arrData['units'] = $this->m_units;
		}
		
		public function getEventMessage() {
			return array(
				"qualifier"		=> "finished",
				"name"			=> "translate:" . $this->m_name,
				"start"			=> $this->m_start,
				"target"		=> $this->m_target,
				"units"			=> $this->m_units
				/*"action"		=> MongoInterface::getBuildEventMessage(explode(':', $this->m_name)[0])*/
			);
		}
		
		protected function requirementsMet() {
			return true;
		}
	}
?>