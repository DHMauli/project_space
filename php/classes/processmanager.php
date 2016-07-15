<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));

	if (isset($_POST['email'])) {
		ProcessManager::clearProcessesOf($_POST['email']);
	}
	
	class ProcessManager {
		
		private $m_processes = array();
		
		public function __construct() { }

		public function addNewProcess($category, $id, array $eventListener, array $additionalInfo) {
			$m_processClassMap = array(
				"building"	=> "ProcessBuild",
				"station" 	=> "ProcessBuildStation",
				"unit"		=> "ProcessUnit",
				"translate"	=> "ProcessUnitTranslate"
			);
			
			$name = $id . ":" . time();
			Debug::echo_info("ProcessManager addNewProcess1 " . $category . ", " . $id);
			if (isset($m_processClassMap[$category]) ==  false) {
				Debug::echo_info("ProcessCategory " . $category . " not found.");
				return;
			}
			$class = $m_processClassMap[$category];
			Debug::echo_info("ProcessManager addNewProcess2 class " . $class);
			$process = new $class($name, $category, 12, $this->getRemainingProcessDuration($category), $additionalInfo);
			
			if ($eventListener != null) {
				foreach($eventListener as $value) {
					$process->registerEventListener($value);
				}
			}
			
			Debug::echo_info("ProcessManager addNewProcess3");
			
			if (!isset($this->m_processes[$category]) || count($this->m_processes[$category]) == 0) {
				$process->startProcess();
			}
			Debug::echo_info("ProcessManager addNewProcess4 of category " . $category);
			$this->m_processes[$category][$name] = $process;
			
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "processmng", serialize($this));
			
			return $process->getProcessData();
		}
		
		public function getProcessTime($name) {
			$category = explode(":", $name)[0];
			$name = substr($name, strlen($category) + 1);
			
			if (!isset($this->m_processes[$category])) {
				return -1;
			}
			if (!isset($this->m_processes[$category][$name])) {
				return -1;
			}
			
			return $this->m_processes[$category][$name]->getRemainingTime();
		}
		
		public function getProcessState($name) {
			$category = explode(":", $name)[0];
			$id = substr($name, strlen($category) + 1);
			
			return $this->getProcessState2($category, $id);
		}
		/* getProcessState: category, id seperate arguments */
		public function getProcessState2($category, $id) {
			if (!isset($this->m_processes[$category])) {
				return 'notfound';
			}
			if (!isset($this->m_processes[$category][$id])) {
				return 'notfound';
			}
			
			return $this->m_processes[$category][$id]->getState();
		}
		/* getProcessState: category, id fused in one argument */
		public function checkProcessState($name) {
			$category = explode(":", $name)[0];
			$id = substr($name, strlen($category) + 1);
			
			$state = $this->getProcessState2($category, $id);
			
			if ($state == 'finished') {
				$this->m_processes[$category][$id]->notifyEventListeners();
				
				unset($this->m_processes[$category][$id]);
				
				if (count($this->m_processes[$category]) > 0) {
					$firstValue = reset($this->m_processes[$category]);
					
					if ($firstValue != false) {
						$firstValue->startProcess();
					}
				}
				
				MongoInterface::updatePlayerObject($_SESSION['objectid'], "processmng", serialize($this));
			}
			
			return $state;
		}
		
		public function saveClass() {
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "processmng", serialize($this));
		}
		
		public function getAllPrcssInCtgry($category) {
			return $this->m_processes[$category];
		}
		
		public function checkAndGetActvPrcssInCtgry($category) {
			Debug::echo_info("ProcessManager checkAndGetActiveProcesses " . $category);
			
			$processes = [];

			if (!isset($this->m_processes[$category])) {
				return $processes;
			}
			
			Debug::echo_info("ProcessManager checkAndGetActiveProcesses 1");
			
			foreach ($this->m_processes[$category] as $key => $value) {
				Debug::echo_info("ProcessManager checkAndGetActiveProcesses state " . $this->getProcessState2($category, $key));
				if ($this->getProcessState2($category, $key) == 'finished') {
					$this->m_processes[$category][$key]->notifyEventListeners();
					unset($this->m_processes[$category][$key]);
					continue;
				}
				
				$processes[] = $value->getJson();
			}
			
			if (count($this->m_processes[$category]) > 0) {
				$firstValue = reset($this->m_processes[$category]);
				
				if ($firstValue != false) {
					$firstValue->startProcess();
				}
			}
			
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "processmng", serialize($this));
			
			return $processes;
		}
		
// private:
		// convert array of object to array of strings
		private function serializeProcesses() {
			$serArray = [];
			
			foreach ($this->m_processes as $key => $value) {
				foreach ($value as $subKey => $subValue) {
					$serArray[$key][$subKey] = serialize($subValue);
				}
			}
			return $serArray;
		}
		
		private function getRemainingProcessDuration($category) {
			if (!isset($this->m_processes[$category])) {
				return 0;
			}
			
			$duration = 0;
			
			foreach ($this->m_processes[$category] as $key => $value) {
				$duration += $value->getRemainingTime();
			}
				
			return $duration;
		}
		
// static:
		static public function clearProcessesOf($user_email) {
			$oid = MongoInterface::getUserHashByEmail($user_email);
			
			MongoInterface::dropSinglePlayerObject($oid, "processmng");
			MongoInterface::insertSinglePlayerObject($oid, "processmng", serialize(new ProcessManager()));
			echo "ProcessManager droppingSinglePlayerObject";
		}
	}
?>