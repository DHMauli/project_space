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
	
	DEFINE("PATH_DATA_PLANETS", "../../data/planets/");
	DEFINE("PATH_DATA_TEMPLATE", "../../data/gameplay/planet_templates/");
	
	if (isset($_POST['planetid']) && isset($_POST['planetname']) && isset($_POST['systemname'])) {
		$template = "";
		if (isset($_POST['template'])) {
			$template = $_POST['template'];
		} else {/* default template */
			$template = "earth";
		}
		
		PlanetManager::createPlanet($_POST['planetid'], $_POST['planetname'], $_POST['systemname'], $template, true);
	} else if (isset($_POST['pos-x']) && isset($_POST['pos-y'])) {
		MongoInterface::getIdOfField('p001', $_POST['pos-x'], $_POST['pos-y']);
	} /*else if (isset($_POST['planetid'])) {
		MongoInterface::clearSatellites($_POST['planetid']);
	}*/
	
	class PlanetManager implements IEventListener {
		
		static private $m_instance = null;
		static public function Instance() {
			if (self::$m_instance == null) {
				self::$m_instance = new PlanetManager();
			}
			
			return self::$m_instance;
		}
		
		private function __construct() { }
		
		public function getFieldId($x, $y) {
			$arrMap = MongoInterface::getMapOfPlanet($_SESSION['selecplanet']);
			return $arrMap[$x][$y];
		}
		
		static public function getMapSquare($left, $top, $width, $height, $planetid = null) {
			if ($planetid == null) {
				$arrMap = MongoInterface::getMapOfPlanet($_SESSION['selecplanet']);
			} else {
				$arrMap = MongoInterface::getMapOfPlanet($planetid);
			}
			
			$arrSquare = array();
			
			$limitX = count($arrMap);
			$limitY = count($arrMap[0]);
			
			$right = $left + $width;
			$right = max(0, min($limitX, $right));
			
			$bottom = $top + $height;
			$bottom = max(0, min($limitY, $bottom));
			
			$itX = 0;
			$itY = 0;
			
			$x = min($left, $right);
			$limitX = max($left, $right);
			
			$y = min($top, $bottom);
			$limitY = max($top, $bottom);
			
			for (; $x < $limitX; $x++) {
				for (; $y < $limitY; $y++) {
					$arrSquare[$itX][$itY] = $arrMap[$x][$y];
					$itY++;
				}
				$y = min($top, $bottom);
				$itX++;
				$itY = 0;
			}
			
			return $arrSquare;
		}
		
		static public function getMapDimensions($planetid = null) {
			if ($planetid == null) {
				$map = MongoInterface::getMapOfPlanet($_SESSION['selecplanet']);
			} else {
				$map = MongoInterface::getMapOfPlanet($planetid);
			}
			
			return array(count($map[0]), count($map));
		}
		
		public function setFieldId($x, $y, $id) {
			if ($id == null) {
				return;
			}
			
			MongoInterface::updateMapOfPlanet($_SESSION['selecplanet'], $x, $y, $id);
		}
		public function notify($eventMessage) {
			$type = explode(':', $eventMessage['name'])[0];
			switch($type) {
				case "station":
				{
					$satType = explode(":", $eventMessage['name'])[1];

					$planetSats = MongoInterface::getPlanetSats($_SESSION['selecplanet']);

					$typeCount = $this->getCountOfType($planetSats, $satType);
					$typeCount++;

					$newIdentifier = $satType . "_" . $typeCount;

					MongoInterface::addSatelliteToPlanet($_SESSION['selecplanet'], $newIdentifier);
					break;
				}
				case "building":
				{
					$fieldKey = explode(':', $eventMessage['name'])[1];
					$fieldId = ConfigRepository::getStringIdExpression($fieldKey);
					PlanetManager::setFieldId($eventMessage['pos_x'], $eventMessage['pos_y'], $fieldId);
					break;
				}
				default:
					break;
			}
		}
      
		private function getCountOfType(array $satellites, $type) {
			$count = 0;
			for ($i = 0; $i < count($satellites); $i++) {
				$thisType = explode('_', $satellites[$i])[0];
				if ($thisType == $type) {
					$count++;
				}
			}

			return $count;
		}
		
		public function getPlanetUnits($planetID, $userID) {
			return MongoInterface::getUnitsOfPlanet($planetID, $userID);
		}
		/*
		static public function getBuildingCountOnMap() {
			$arrBuildingCount = array();
			
			$limitX = count($this->userInfo['map']);
			$limitY = count($this->userInfo['map'][0]);
			
			for ($x = 0; $x < $limitX; $x++) {
				for ($y = 0; $y < $limitY; $y++) {
					$fieldId = $this->getFieldId($x, $y);
					$fieldName = ConfigRepository::getIdStringExpression($fieldId);
					if (!isset($arrBuildingCount[$fieldName]))
						$arrBuildingCount[$fieldName] = 1;
					else
						$arrBuildingCount[$fieldName]++;
				}
			}
			
			return $arrBuildingCount;
		}
		*/
		
		public function getPlanetOwner($planetid) {
			return MongoInterface::getPlanetProperty($planetid, "owner");
		}
		
		public function createPlanet($id, $name, $system, $template, $redirect = false) {
			
			// TODO: check if id already taken, or auto assign id
			
			$jsonTemplate = file_get_contents(PATH_DATA_TEMPLATE . "template_" . strtolower($template) . ".json");
			
			if ($jsonTemplate === false) {
				Debug::echo_info("failed to load template " . $template);
				return;
			}
			
			MongoInterface::addSatelliteToSystem($system, $id);
			
			MongoInterface::insertPlanet(array(
				"id" => $id,
				"label" => $name,
				"free" => true,
				"sats" => [],
				"map" => json_decode($jsonTemplate, true)['map']
			));
			
			if ($redirect) {
				Redirect::to("../../htm/admin.php");
			}
		}
	}
?>