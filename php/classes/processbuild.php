<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));
	
	class ProcessBuild extends Process implements ISerializeChild, IGetProcessDataChild {
		
		protected $m_x = 0;
		protected $m_y = 0;
		
		public function __construct($name, $category, $duration, $prevProcDuration, array $additionalInfo) {
			parent::__construct($name, $category, $duration, $prevProcDuration);
			
			$this->m_x = $additionalInfo[0];
			$this->m_y = $additionalInfo[1];
			
			RepoRepository::retrieveObj('eventrepo')->registerEvent(array(
				"qualifier"		=> "started",
				"name"			=> "building:" . $this->m_name,
				"pos_x"			=> $this->m_x,
				"pos_y"			=> $this->m_y
			));
			
			PlanetManager::Instance()->setFieldId($this->m_x, $this->m_y, ConfigRepository::getStringIdExpression('constructionsite'));
		}
		
		public function serializeChild(array &$serializingArray) {
			$serializingArray['pos_x'] = $this->m_x;
			$serializingArray['pos_y'] = $this->m_y;
		}
		
		public function unserializeChild(array $arrData) {
			$this->m_x = $arrData['pos_x'];
			$this->m_y = $arrData['pos_y'];
		}
		
		public function getProcessDataChild(array &$arrData) {
			$arrData['pos_x'] = $this->m_x;
			$arrData['pos_y'] = $this->m_y;
		}
		
		public function getEventMessage() {
			return array(
				"qualifier"		=> "finished",
				"name"			=> "building:" . $this->m_name,
				"pos_x"			=> $this->m_x,
				"pos_y"			=> $this->m_y,
				"action"		=> MongoInterface::getBuildEventMessage(explode(':', $this->m_name)[0])
			);
		}
		
		protected function requirementsMet() {
			$buildingKey = explode(':', $this->m_name)[0];
			$buildingInfo = MongoInterface::getConfigProperty("config_buildings", $buildingKey);
			
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