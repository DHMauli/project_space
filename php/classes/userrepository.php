<?php
	$dirPath = __DIR__;
	$slashPos = strrpos(__DIR__, "\\");
	
	if (!$slashPos) {
		$slashPos = strpos(__DIR__, "/");
	}
	
	$autoloaderPath = substr($dirPath, 0, $slashPos) . "/autoloader.php";
	$autoloaderPath = str_replace("\\", "/", $autoloaderPath);

	require_once $autoloaderPath;
	spl_autoload_register(array('Autoloader', 'load'));
	
	DEFINE("PATH_USERDATA", "../../data/user/");
	
	class UserRepository {
		
// public:
		static public function executeAction($eventMessage) {
			if (isset($eventMessage['action']) == false) {
				return;
			}
			
			self::executeProcessCommand($eventMessage['action']);
		}
		
		static public function setLogoutTime() {
			MongoInterface::setPlayerProperty($_SESSION['objectid'], "logout", time());
		}
		
		static public function setSelectedPlanet($planetid) {
			$_SESSION['selecplanet'] = $planetid;
		}
		
// private:
		static private function executeProcessCommand($action) {
			$commands = explode('|', $action);
			
			for ($i = 0; $i < count($commands); $i++) {
				$command = explode(':', $commands[$i]);
				self::handleCommand($command);
			}
		}
      
      static private function handleCommand(array $command) {
         switch ($command[0]) {
            case "addResource":
            {
               $resourceCounter = RepoRepository::retrieveObj('resourcecounter');
               $resourceCounter->addResourceCountTo($command[1], intval($command[2]));
               break;
            }
            case "addJobs":
            {
               PopulationManager::addJobs($command[1], intval($command[2]));
               break;
            }
            default:
            {
               break;
            }
         }
      }
	}
?>