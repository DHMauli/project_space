<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));

	class ProcessBuildStation extends Process implements ISerializeChild, IGetProcessDataChild {
		
		protected $m_planetId = 0;
		
		public function __construct($name, $category, $duration, $prevProcDuration, array $additionalInfo) {
			parent::__construct($name, $category, $duration, $prevProcDuration);
			
			$this->m_planetId = $additionalInfo[0];
			
			RepoRepository::retrieveObj('eventrepo')->registerEvent(array(
				"qualifier"		=> "started",
				"name"			=> "station:" . $this->m_name,
				"planetId"		=> $this->m_planetId
			));
			
			Debug::echo_info("Created ProcessBuildStation " . $this->m_name);
		}
		
		public function serializeChild(array &$serializingArray) {
			$serializingArray['planetId'] = $this->m_planetId;
		}
		
		public function unserializeChild(array $arrData) {
			$this->m_planetId = $arrData['planetId'];
		}
		
		public function getProcessDataChild(array &$arrData) {
			$arrData['planetId'] = $this->m_planetId;
		}
		
		public function getEventMessage() {
			return array(
				"qualifier"	=> "finished",
				"name"		=> "station:" . $this->m_name,
				"planetId"	=> $this->m_planetId,
				"action"	=> MongoInterface::getBuildEventMessage(explode(':', $this->m_name)[0])
			);
		}
		
		protected function requirementsMet() {
			return true;
		}
	}
?>