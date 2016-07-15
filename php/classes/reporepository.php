<?php
	$dirPath = __DIR__;
	$slashPos = strrpos(__DIR__, "\\");
	
	if (!$slashPos)
		$slashPos = strpos(__DIR__, "/");
	
	$autoloaderPath = substr($dirPath, 0, $slashPos) . "/autoloader.php";
	$autoloaderPath = str_replace("\\", "/", $autoloaderPath);

	require_once $autoloaderPath;
	spl_autoload_register(array('Autoloader', 'load'));

	class RepoRepository {
		
		static private $objects = array();
		
		//public function __construct() { }
		// TODO: check, if SESSION-Object is updated, objects[]-Objects remain the same
		//		 when serialize() force new retrieve/pass new object to RepoRepository
		static public function retrieveObj($strName) {
			if (isset(self::$objects[$strName])) {
				return self::$objects[$strName];
			}
			
			self::$objects[$strName] = unserialize(MongoInterface::getPlayerObject($_SESSION['objectid'], $strName));
			
			if (!isset(self::$objects[$strName]) || self::$objects[$strName] == false) {
				Debug::echo_info("Object " . $strName . " couldn't be retrieved.");
				return null;
			}
			
			return self::$objects[$strName];
		}
		
		static public function updateObj($strName) {
			self::$objects[$strName] = unserialize(MongoInterface::getPlayerObject($_SESSION['objectid'], $strName));
		}
	}
?>