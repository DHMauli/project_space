<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));

	class ProcessUnit extends Process implements ISerializeChild, IGetProcessDataChild {
		
		protected $m_planetid = 0;
		
		public function __construct($name, $category, $duration, $prevProcDuration, array $additionalInfo) {
			parent::__construct($name, $category, $duration, $prevProcDuration);
			
			$this->m_planetid = $additionalInfo[0];
			
			RepoRepository::retrieveObj('eventrepo')->registerEvent(array(
				"qualifier"		=> "started",
				"name"			=> "unit:" . $this->m_name,
				"planetid"		=> $this->m_planetid
			));
		}
		
		public function serializeChild(array &$serializingArray) {
			$serializingArray['planetid'] = $this->m_planetid;
		}
		
		public function unserializeChild(array $arrData) {
			$this->m_planetid = $arrData['planetid'];
		}
		
		public function getProcessDataChild(array &$arrData) {
			$arrData['planetid'] = $this->m_planetid;
		}
		
		public function getEventMessage() {
			return array(
				"qualifier" 	=> "finished",
				"planetid"		=> $this->m_planetid,
				"name"			=> "unit:" . $this->m_name
			);
		}
		
		protected function requirementsMet() {
			//$unitKey = explode(':', $this->m_name)[0];
			//$buildingInfo = MongoInterface::getConfigProperty("config_buildings", $buildingKey);
			
			if (isset($buildingInfo['endconditions']) == false) {
				return true;
			}
			
			foreach ($buildingInfo['endconditions'] as $key => $value) {
				if (Flag::getFlag($key) != $value) {
					Debug::echo_info("Flag " . $key . " is required to have value " . var_export($value, true) . " but has value " . Flag::getFlag($key));
					return false;
				}
			}
			
			return true;
		}
	}
?>