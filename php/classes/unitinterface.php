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

	class UnitInterface implements IEventListener {
		
		static private $m_instance = null;
		static public function Instance() {
			if (self::$m_instance == null) {
				self::$m_instance = new UnitInterface();
			}
			
			return self::$m_instance;
		}
		
		private function __construct() { }
		
		static public function getCosts($unitname) {
			$unit = MongoInterface::getUnitByName($unitname);
			return $unit['costs'];
		}
		
		static public function getNameByOID($OID) {
			$unit = MongoInterface::getUnitByOID($OID);
			return $unit['name'];
		}
		
		static public function getOIDByName($unitname) {
			$unit = MongoInterface::getUnitByName($unitname);
			return $unit['_id'];
		}
		
		static public function getGraphicsPathByOID($OID) {
			$unit = MongoInterface::getUnitByOID($OID);
			return $unit['graphicspath'];
		}
		
		static public function getGraphicsPathByName($name) {
			$unit = MongoInterface::getUnitByName($name);
			return $unit['graphicspath'];
		}
		
		// by name
		static public function getGraphicsPathList(array $units) {
			$graphcisPaths = [];
			
			for ($i = 0; $i < count($units); $i++) {
				$graphicsPath[] = self::getGraphicsPathByName($units[$i]);
			}
			
			return $graphicsPath;
		}
		
		public function notify($eventMessage) {
			$category = explode(':', $eventMessage['name'])[0];
			switch ($category) {
				case "unit":
				{
					$nameDotPos = strpos($eventMessage['name'], ':');
					$nameID = substr($eventMessage['name'], $nameDotPos + 1);
					
					$addUnit = array();
					$addUnit[] = $nameID;
					
					MongoInterface::addUnitsToPlanetOrbit($eventMessage['planetid'], $_SESSION['objectid'], $addUnit);
					break;
				}
				case "translate":
				{
					MongoInterface::addUnitsToPlanetOrbit($eventMessage['target'], $_SESSION['objectid'], $eventMessage['units']);
					break;
				}
				default:
				{
					break;
				}
			}
		}
		
		static public function removeUnitsFromPlanet($planetID, $units) {
			MongoInterface::removeUnitsFromPlanetOrbit($planetID, $_SESSION['objectid'], $units);
		}
	}
?>