<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));
	
	/**
	 *	interface for javaScript, querying events
	 *  to change view correspondingly
	 */
	
	/**
	 *	lifetime = session
	 *	queryEvents when logging out
	 */
	
	class EventRepository implements IEventListener {
		
		private $m_eventList = array();
		
// public
		public function __construct() { }
		
		public function queryEvents($qualifier) {
			if (!isset($this->m_eventList[$qualifier])) {
				return null;
			}
			
			$arrReturn = array();
			$arrSelection = $this->m_eventList[$qualifier];
			foreach ($arrSelection as $event) {
				$arrReturn[] = json_encode($event);
			}
			
			unset($this->m_eventList[$qualifier]);
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "eventrepo", serialize($this));
			
			return $arrReturn;
		}
		
		public function registerEvent($event) {
			$this->m_eventList[$event['qualifier']] = $event;
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "eventrepo", serialize($this));
		}
		
		public function notify($eventMessage) {
			Debug::echo_info("EventRepository notify: " . json_encode($eventMessage));
			$this->m_eventList[$eventMessage["qualifier"]][] = $eventMessage;
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "eventrepo", serialize($this));
		}
	}
?>