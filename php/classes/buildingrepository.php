<?php
	if (session_status() == PHP_SESSION_NONE)
		session_start();
	
	$dirPath = __DIR__;
	$slashPos = strrpos(__DIR__, "\\");
	
	if (!$slashPos) {
		$slashPos = strpos(__DIR__, "/");
	}
	
	$autoloaderPath = substr($dirPath, 0, $slashPos) . "/autoloader.php";
	$autoloaderPath = str_replace("\\", "/", $autoloaderPath);

	require_once $autoloaderPath;
	spl_autoload_register(array('Autoloader', 'load'));
    
/*
	$explNeedle = "/";
	$pathProject = __DIR__;
	
	if (!strpos($pathProject, "/"))
		$explNeedle = "\\";
	
	$arrPathParts = explode($explNeedle, $pathProject);
	
	$pathProject = '';
	
	for ($i = 0; $i < count($arrPathParts) - 2; $i++) {
		$pathProject .= $arrPathParts[$i];
		$pathProject .= $explNeedle;
	}
	
	$pathCfgBdngs = $pathProject . "data/gameplay/config_buildings.json";
	$pathCfgBdngs = str_replace("\\", "/", $pathCfgBdngs);
	
	DEFINE("PATH_CONFIG_BDNGS", $pathCfgBdngs);
*/
	
	class BuildingRepository {
		
		static public function getCosts($buildingName) {
			$arrCosts = MongoInterface::getConfigProperty("config_buildings", $buildingName);
			if (!isset($arrCosts['costs'])) {
				return;
			}
			
			return $arrCosts['costs'];
		}
		
		static public function getBuildingList() {
			$buildings = MongoInterface::getConfigBuildings();
			
			foreach ($buildings as $key => $value) {
				$arrList[] = $key;
			}
				
			return $arrList;
		}
		
		static public function getJobBuildings() {
			$buildings = MongoInterface::getConfigBuildings();
			
			foreach ($buildings as $key => $value) {
				if (!isset($buildings[$key]['jobs'])) {
					continue;
				}
				
				$arrList[] = $key;
			}
			
			return $arrList;
		}
		
		static public function getStructureUnits($buildingId) {
			$buildings = MongoInterface::getConfigBuildings();
			
			$unitOptions = [];
			
			foreach ($buildings as $key => $value) {
				if ($key != $buildingId) {
					continue;
				}
				
				if (isset($value['units']) == false) {
					break;
				}
				
				$unitOptions = $value['units'];
				break;
			}
			
			$completeData = [];
			
			for ($i = 0; $i < count($unitOptions); $i++) {
				$path = UnitInterface::getGraphicsPathByName($unitOptions[$i]);
				$completeData[] = array('name' => $unitOptions[$i], 'path' => $path);
			}
			
			return $completeData;
		}
	}
?>