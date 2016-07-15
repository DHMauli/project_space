<?php
	$dirPath = __DIR__;
	$slashPos = strrpos(__DIR__, "\\");
	
	if (!$slashPos) {
		$slashPos = strpos(__DIR__, "/");
	}
	
	$classesPath = substr($dirPath, 0, $slashPos) . "/classes/";
	$classesPath = str_replace("\\", "/", $classesPath);
	
	$resourcePath = substr($dirPath, 0, $slashPos - 4) . "/img/";
	$resourcePath = str_replace("\\", "/", $resourcePath);
	
	define('PATH_CLASSES', $classesPath);
	define('PATH_RESOURCES', $resourcePath);
	
	//ini_set('unserialize_callback_func', 'unserializeClassLoad');
	require_once substr($dirPath, 0, $slashPos) . "/autoloader.php";
	spl_autoload_register(array('Autoloader', 'load'));
	
	function build($x, $y, $buildingid) {
		$fieldId = PlanetManager::Instance()->getFieldId($x, $y);
      
		$buildInfo = ConfigRepository::getFieldBuildInfo($fieldId, $buildingid);
		if (!isset($buildInfo['buildingoptions'])) {
			Debug::echo_info("No buildingoptions found");
			return;
		}
		$fieldInfo = $buildInfo['buildingoptions'];
		
		if (isset($buildInfo['startconditions'])) {
			foreach ($buildInfo['startconditions'] as $key => $value) {
				if (Flag::getFlag($key) != $value) {
					Debug::echo_error("Flag " . $key . " is required to have value " . var_export($value, true) . " but has value " . var_export(Flag::getFlag($key), true));
					return;
				}
			}
		}
		
		$idValid = false;
		
		for ($i = 0; $i < count($fieldInfo); $i++) {
			if ($fieldInfo[$i] == $buildingid) {
				$idValid = true;
				break;
			}
		}
		
		if (!$idValid) {
			Debug::echo_error("ID " . $buildingid . " not valid");
			return;
		}
		
		$resourceCounter = RepoRepository::retrieveObj('resourcecounter');
		$costs = BuildingRepository::getCosts($buildingid);
		$success = $resourceCounter->applyCosts($costs);
		
		if (!$success) {
			Debug::echo_error("Applying costs for " . $buildingid . " failed");
			return;
		}
		
		$processManager = RepoRepository::retrieveObj('processmng');
		
		$arrReturn = array();
		$arrReturn['costs'] = $costs;
		$arrReturn['process'] = $processManager->addNewProcess("building", $buildingid, array('eventrepo', 'PlanetManager'), array($x, $y));
		
		echo json_encode($arrReturn);
	}
	
	function buildInSpace($buildingId) {
		Debug::echo_info("utility buildInSpace " . $buildingId);
		$resourceCounter = RepoRepository::retrieveObj('resourcecounter');
		$costs = BuildingRepository::getCosts($buildingId);
		$success = $resourceCounter->applyCosts($costs);
		
		if (!$success) {
			Debug::echo_error("Applying costs for " . $buildingId . " failed");
			return;
		}
		
		$processManager = RepoRepository::retrieveObj('processmng');
		
		$arrReturn = array();
		$arrReturn['costs'] = $costs;
		$arrReturn['process'] = $processManager->addNewProcess("station", $buildingId, array('eventrepo', 'PlanetManager'), array($_SESSION['selecplanet']));
		
		echo json_encode($arrReturn);
	}
	
	function buildUnit($unitType) {
		$resourceCounter = RepoRepository::retrieveObj('resourcecounter');
		$costs = UnitInterface::Instance()->getCosts($unitType);
		
		$success = $resourceCounter->applyCosts($costs);
		
		if ($success == false) {
			Debug::echo_error("Applying costs for " . $buildingId . " failed");
			return;
		}
		
		$processManager = RepoRepository::retrieveObj('processmng');
		
		$arrReturn = array();
		$arrReturn['costs'] = $costs;
		$arrReturn['process'] = $processManager->addNewProcess("unit", $unitType, array('eventrepo', 'UnitInterface'), array($_SESSION['selecplanet']));
		
		echo json_encode($arrReturn);
	}
	
	function sendUnits($units, $start, $target) {
		
		if (!isset($target) || (strlen($target) == 0)) {
			Debug::echo_error("Target is not set");
			return;
		}
		if (strtolower($target[0]) != 'p') {
			Debug::echo_error("Target planet-name has wrong format.");
			return;
		}
		
		$selectedUnits = json_decode($units, true);
		$unitCount = array();
		
		foreach ($selectedUnits as $key => $value) {
			
			if (intval($value) == 0) {
				continue;
			}
			
			$unitCount[$key] = 0;
		}
		
		$planetUnits = MongoInterface::getUnitsOfPlanet($start, $_SESSION['objectid']);
		
		$arrUnitList = array();
		foreach ($planetUnits as $unit) {
			$unitType = explode(':', $unit)[0];
			
			if (!isset($unitCount[$unitType])) {
				continue;
			}
			if ($unitCount[$unitType] < $selectedUnits[$unitType]) {
				$arrUnitList[] = $unit;
				$unitCount[$unitType]++;
			}
		}
		
		$unitsHashBase = $start . $target;
		foreach ($selectedUnits as $key => $value) {
			$unitsHashBase .= $key . $value;
		}
		
		$groupHash = hash('md5', $unitsHashBase);
		$processManager = RepoRepository::retrieveObj('processmng');
		$processManager->addNewProcess("translate", $groupHash, array('eventrepo', 'UnitInterface'), array($start, $target, $arrUnitList));
	}
	
	function destroySession() {
		UserRepository::setLogoutTime();
		
		$processManager = RepoRepository::retrieveObj('processmng');
		$processManager->saveClass();
		
		session_destroy();
		echo "success";
	}
	
	function allocateJobs($data) {
		$resources = PopulationManager::allocateJobs($data, true);
		RepoRepository::retrieveObj('resourcecounter')->setResourceCount($resources);
	}
	
	function getPopulationAllocation() {
		echo json_encode(PopulationManager::getJobsAllocationAll());
	}
	
	function getJobsAll() {
		echo json_encode(PopulationManager::getJobsAll());
	}
	
	function getBuildingList() {
		echo json_encode(BuildingRepository::getBuildingList());
	}
	
	function getFieldTypeList() {
		echo json_encode(ConfigRepository::getFieldTypeList());
	}
	
	function getProcessTime($name) {
		$processManager = RepoRepository::retrieveObj('processmng');
		echo $processManager->getProcessTime($name);
	}

	function getActiveProcesses($category) {
		$processManager = RepoRepository::retrieveObj('processmng');
		echo json_encode($processManager->checkAndGetActvPrcssInCtgry($category));
	}
	
	function getProcessState($name) {
		$processManager = RepoRepository::retrieveObj('processmng');
		echo $processManager->getProcessState($name);
	}
	
	function checkProcessState($name) {
		$processManager = RepoRepository::retrieveObj('processmng');
		echo $processManager->checkProcessState($name);
	}
	
	function getFieldId($x, $y) {
		echo PlanetManager::Instance()->getFieldId($x, $y);
	}
	
	function getBuildOptions($x, $y) {
		$fieldId = PlanetManager::Instance()->getFieldId($x, $y);
		// TODO error handling like in getBuildOptionsSys
		echo json_encode(ConfigRepository::getFieldBuildOptions($fieldId));
	}
	
	function getBuildOptionsSys() {
		$options = array("shipyard");
		$imageCategory = "processicons/";
      
		if (Debug::$ENABLED) {
			for ($i = 0; $i < count($options); $i++) {
				$path = PATH_RESOURCES . $imageCategory . $options[$i] . ".jpg";
				if (file_exists($path) == false) {
					Debug::echo_internal_error("File " . $options[$i] . ".jpg" . " was not found.");
				}
			}
		}
		
		echo json_encode($options);
	}
	
	function getStructureBuildOptions($type) {
		$unitOptions = BuildingRepository::getStructureUnits($type);
		echo json_encode($unitOptions);
	}
	
	function getMap() {
		return PlanetManager::Instance()->getMap();
	}
	
	function getMapSquare($left, $top, $width, $height) {
		echo json_encode(PlanetManager::Instance()->getMapSquare($left, $top, $width, $height));
	}
	
	function getMapDimensions() {
		echo json_encode(PlanetManager::Instance()->getMapDimensions());
	}
	
	function getResource($resourceType) {
		echo PlanetManager::Instance()->getResource($resourceType);
	}
	
	function getAllResources() {
		$resourceCounter = RepoRepository::retrieveObj('resourcecounter');
		echo json_encode($resourceCounter->getResourceCountAll());
	}
	
	function queryEvents($qualifier) {
		$eventRep = RepoRepository::retrieveObj('eventrepo');
		$events = $eventRep->queryEvents($qualifier);
		
		if ($events == null) {
			echo '';
			return;
		}
		
		echo json_encode($events);
	}
	
	function getSatellitesOfSystem($systemId) {
		$listPlanets = MongoInterface::getSystemSats($systemId);
		$planetInfo = MongoInterface::getPlanetsInfo($listPlanets, array(
			'_id' => false, 'id' => true, 'template' => true, 'label' => true));
		
		$completeList = array();
		
		foreach ($planetInfo as $pair) {
			$image = MongoInterface::getConfigProperty("config_templates", $pair['template'])['sysgraphic'];
			$completeList[] = array(
				'id' => $pair['id'],
				'image' => $image,
				'label' => $pair['label']);
		}
		// TODO: passing template + img = redundant?
		echo json_encode($completeList);
	}
	
	function getSatellitesOfPlanet($planetId) {
		if (!isset($planetId)) {
			$planetId = $_SESSION['selecplanet'];
		}

		$listSatellites = MongoInterface::getPlanetSats($planetId);
		$satellites = array('id' => $planetId,
			'sats' => $listSatellites);
		echo json_encode($satellites);
	}
	
	function getPlanetUnits($planetId) {
		$info = PlanetManager::Instance()->getPlanetUnits($planetId, $_SESSION['objectid']);
		echo json_encode($info);
	}
	
	function resetAccountData() {
		RepoRepository::retrieveObj('resourcecounter')->setResourceCount(array(
			"food"			=> 1000000,
			"population" 	=> 200,
			"employed"		=> 0,
			"unemployed"	=> 200
		));
		
		PopulationManager::allocateJobs(array(
			"metalmine"			=> 40,
			"crystalmine"		=> 30,
			"methanerefinery"	=> 20
		));
	}
	
	function openPlanetView($offsetX, $offsetY, $planetid) {
		if (checkPlanetOwnership($planetid) == false) {
			return;
		}
		
		echo PlanetMap::getHtmlMap($offsetX, $offsetY, $planetid);
	}
	
	function checkPlanetOwnership($planetid) {
		$playerIsOwner = true;
		
		$owner = PlanetManager::Instance()->getPlanetOwner($planetid);
		
		if ($owner != $_SESSION['objectid']) {
			Debug::echo_error("You have no control over this planet.");
			$playerIsOwner = false;
		}
		
		return $playerIsOwner;
	}
	/*
	function unserializeClassLoad($classname) {
		$path = PATH_CLASSES . strtolower($classname) . '.php';
		require_once $path;
	}
	*/
?>