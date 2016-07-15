<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));

	abstract class Process implements Serializable {
		protected $m_category;
		protected $m_name;
		// duration in seconds
		protected $m_duration;
		protected $m_timestamp;
		protected $m_processStarted = false;
		
		protected $m_eventListeners = array();

		public function __construct($name, $category, $duration, $prevProcDuration) {
			$this->m_name		= $name;
			$this->m_category 	= $category;
			$this->m_duration 	= $duration;
			$this->m_timestamp 	= time() + $prevProcDuration;
		}
	
		public function startProcess() {
			$this->m_processStarted = true;
		}
	
		public function getState() {
			if (!$this->m_processStarted) {
				return "pending";
			}
			
			if ($this->getRemainingTime() <= 0) {
				if ($this->requirementsMet()) {
					return "finished";
				}
			}
			
			return "ongoing";
		}

		public function getRemainingTime() {
			if (!$this->m_processStarted) {
				return $this->m_duration;
			}
			
			$timeDifference = time() - $this->m_timestamp;
			$remainingTime = $this->m_duration - $timeDifference;
			Debug::echo_info("Process " . $this->m_name . " getRemainingTime " .$remainingTime);
			
			return $remainingTime;
		}
		
		public function registerEventListener($eventListener) {
			$this->m_eventListeners[] = $eventListener;
		}
		
		public function notifyEventListeners() {
			// TODO: send time of finish
			$eventMsg = $this->getEventMessage();
			foreach ($this->m_eventListeners as $value) {
				$obj = RepoRepository::retrieveObj($value);
				
				if ($obj == null) {
					$obj = $this->getSingletonFromString($value);
				}
				
				if ($obj == null) {
					Debug::echo_info("Process notifyEventListeners after retrieving singleton NULL");
					continue;
				}
				
				if ($obj instanceof IEventListener) {
					$obj->notify($eventMsg);
				}
			}
			UserRepository::executeAction($eventMsg);
		}
      
      private function getSingletonFromString($value) {
         Debug::echo_info("Getting singleton from string " . $value);
         if (class_exists($value)) {
            $rc = new ReflectionClass($value);
            if ($rc->hasMethod("Instance")) {
               $obj = $value::Instance();
               Debug::echo_info("Process retrieved Singleton " . $value);
            }
         }
         
         return $obj;
      }
		
		public function getProcessData() {
			$arrData = array(
				"name"				=> $this->m_category . ":" . $this->m_name,
				"duration"			=> $this->m_duration
			);
			
			$this->getProcessDataChild($arrData);
			
			return $arrData;
		}
		
		public function getJson() {
			return json_encode($this->getProcessData());
		}
		
		public function serialize() {
			$serArray = array(
				"name"				=> $this->m_name,
				"category"			=> $this->m_category,
				"duration" 			=> $this->m_duration,
				"timestamp" 		=> $this->m_timestamp,
				"processStarted" 	=> $this->m_processStarted,
				"listeners"			=> json_encode($this->m_eventListeners)
			);
			
			$this->serializeChild($serArray);
			
			return serialize($serArray);
		}
		
		public function unserialize($data) {
			$arrData = unserialize($data);
			
			$this->m_name 			= $arrData['name'];
			$this->m_category		= $arrData['category'];
			$this->m_duration 		= $arrData['duration'];
			$this->m_timestamp 		= $arrData['timestamp'];
			$this->m_processStarted = $arrData['processStarted'];
			$this->m_eventListeners	= json_decode($arrData['listeners']);
			
			$this->unserializeChild($arrData);
		}
		
		abstract protected function requirementsMet();
	}
?>