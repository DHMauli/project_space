<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$dirPath = __DIR__;
	$slashPos = strrpos(__DIR__, "\\");
	
	if (!$slashPos) {
		$slashPos = strpos(__DIR__, "/");
	}
	
	$autoloaderPath = substr($dirPath, 0, $slashPos) . "/autoloader.php";
	$autoloaderPath = str_replace("\\", "/", $autoloaderPath);

	require_once $autoloaderPath;
	spl_autoload_register(array('Autoloader', 'load'));
	
	class PopulationManager {
		
		static private $dbservername 	= "localhost";
		static private $dbusername 		= "root";
		static private $dbpassword 		= "rootpassword";
		static private $dbname 			= "testdata";
		
		//public function __construct() { }
		
		static public function addJobs($buildingId, $amount) {
			$currentJobs = MongoInterface::updatePlanetJobs($_SESSION['selecplanet'], $buildingId, $amount);
			$currentJobs = MongoInterface::updatePlanetJobs($_SESSION['selecplanet'], "totaljobs", $amount);
		}
		
		static public function getJobsAll() {
			return MongoInterface::getPlanetProperty($_SESSION['selecplanet'], "jobs");
		}
		
		static public function getTotalJobs() {
			return MongoInterface::getJobsProperty($_SESSION['selecplanet'], "totaljobs");
		}
		
		static public function allocateJobs($data, $isJson = false) {
			$checkSum = 0;
			$arrUpdate = array();
			if ($isJson) {
				$arrUpdate = json_decode($data, true);
			} else {
				$arrUpdate = $data;
			}
			
			$arrModify = array();
			foreach ($arrUpdate as $key => $val) {
				$arrModify['jobsalloc.'.$key] = $val;
				$checkSum += $val;
			}
			
			$resourceCounter = RepoRepository::retrieveObj('resourcecounter');
			$currentPopulation = $resourceCounter->getResourceCountOf("population");
			
			if ($checkSum > $currentPopulation) {
				Debug::echo_info("checksum:" . $checkSum . " > population:" . $currentPopulation);
				return;
			}
				
			if ($checkSum > self::getTotalJobs()) {
				Debug::echo_info("checkSum:" . $checkSum . " > totaljobs:" . self::getTotalJobs());
				return;
			}
			
			MongoInterface::setPlanetArray($_SESSION['selecplanet'], $arrModify);
			
			$unemployedNew = $currentPopulation - $checkSum;
			return array(
				"employed" 		=> $checkSum,
				"unemployed" 	=> $unemployedNew
			);
		}
		
		static public function getJobsAllocationAll() {
			return MongoInterface::getPlanetProperty($_SESSION['selecplanet'], "jobsalloc");
		}
		/* ------------------------------- HARDCODED -------------------------------------------- */
		static public function reducePopulationByPercentage($percentage, $memento) {
			$mementoData = &$memento->getDataRef();
			
			$resourceRepo = RepoRepository::retrieveObj('resourcecounter');
			$currentPopulation = $resourceRepo->getResourceCount(array("population", "unemployed"));
			
			$totalLoss = round($mementoData['population'] * $percentage);
			
			$newPopulation = $currentPopulation["population"] - $totalLoss;
			$newPopulation = max($newPopulation, 0);
			
			$arrFractions = array();
			$arrGeneralDistr = self::getJobsAllocationAll();
			
			$peopleSum = 0;
			foreach ($arrGeneralDistr as $buildingId => $value) {
				$fNew = $value - $mementoData[$buildingId] * $percentage;
				$fNew = max($fNew, 0);
				$iNew = round($fNew);
				
				$arrFractions[$buildingId] = $fNew - $iNew;
				
				$arrGeneralDistr[$buildingId] = $iNew;	
				$peopleSum += $iNew;
			}
			
			$fNew = $currentPopulation["unemployed"] - $mementoData['unemployed'] * $percentage;
			$fNew = max($fNew, 0);
			
			if ($fNew < 0) {
				$fNew = max(0.0, $fNew);
				$arrFractions["unemployed"] = 0.0;
			} else {
				$arrFractions["unemployed"] = $fNew - $iNew;
			}
			
			$iNew = round($fNew);
			$arrGeneralDistr["unemployed"] = $iNew;
			$peopleSum += $iNew;
			
			$errorValue = $newPopulation - $peopleSum;
			
			$allJobs = self::getJobsAll();
			
			/* newPopulation > peopleSum -> add jobs to floored values */
			/* floored values = positive fraction */
			if ($errorValue > 0) {
				if (!arsort($arrFractions)) {
					Debug::echo_info("PopulationManager reducePopulationByPercentage sort failed.");
					return;
				}
				
				// number of corrected values
				$correctionVal = 0;
				// number of loops
				$iterator = 0;
				$highestIndex = count($arrFractions) - 1;
				$keys = array_keys($arrGeneralDistr);
				
				while ($correctionVal < abs($errorValue) && $iterator < 100) {
					$index = $iterator % $highestIndex;
					$indexKey = $keys[$index];
					if ($arrGeneralDistr[$indexKey] < $allJobs[$indexKey]) {
						$arrGeneralDistr[$indexKey]++;
						$correctionVal++;
					}
					$iterator++;
				}
				
			/* ceiled values = negative fractions */
			/* newPopulation < peopleSum -> substract jobs from ceiled values */
			} else if ($errorValue < 0) {
				if (!asort($arrFractions)) {
					Debug::echo_info("PopulationManager reducePopulationByPercentage sort failed.");
					return;
				}
				
				// number of corrected values
				$correctionVal = 0;
				// number of loops
				$iterator = 0;
				$highestIndex = count($arrFractions) - 1;
				$keys = array_keys($arrGeneralDistr);
				
				while ($correctionVal < abs($errorValue) && $iterator < 100) {
					$index = $iterator % $highestIndex;
					$indexKey = $keys[$index];
					if ($arrGeneralDistr[$indexKey] >= 1) {
						$arrGeneralDistr[$indexKey]--;
						$correctionVal++;
					}
					$iterator++;
				}
			}
			
			$resourceRepo->setResourceCountOf("population", $newPopulation);
			/* allocate new jobs count */
			$newResources = self::allocateJobs(array_slice($arrGeneralDistr, 0, count($arrGeneralDistr) - 2));
			$newResources['population'] = $newPopulation;
			
			return $newResources;
		}
		/* ---------------------------------------------------------------------------------- */
		public function addJobsToMemento(&$memento) {
			$mementoData = &$memento->getDataRef();
			$jobsAllocation = self::getJobsAllocationAll();
			
			foreach ($jobsAllocation as $buildingId => $value) {
				$mementoData[$buildingId] = $value;
			}
		}
		
// private
		static private function getUpdateStringJson($jsonData, &$checkSum) {
			$decoded = json_decode($jsonData);
			$strUpdate = "";
			
			for ($i = 0; $i < count($decoded); $i++) {
				$keyVal = explode(':', $decoded[$i]);
				$checkSum += $keyVal[1];
				$strUpdate .= ($keyVal[0] . "=" . $keyVal[1] . ",");
			}
			$strUpdate = substr($strUpdate, 0, strlen($strUpdate) - 1);
			
			return $strUpdate;
		}
		
		static private function getUpdateStringAssocArray(array $data, &$checkSum) {
			$strUpdate = "";
			
			foreach ($data as $key => $value) {
				$checkSum += $value;
				$strUpdate .= ($key . "=" . $value . ",");
			}
			$strUpdate = substr($strUpdate, 0, strlen($strUpdate) - 1);
			
			return $strUpdate;
		}
	}
?>