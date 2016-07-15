<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));
	
	DEFINE("GAUSS_FACTOR", 0.3989423); /* 1 / sqrt(2*M_PI) */
	
	class ResourceCounter implements Serializable {
		
		private $m_timeStamp = 0;
		
		private $m_lackingFoodTime = 0.0;
		
		private $m_totalLosses = 0.0;
		
		private $m_starvMemento = null;
		
		public function __construct() { }
		
		public function getResourceCountAll() {
			$result = MongoInterface::getPlanetProperty($_SESSION['selecplanet'], "resources");
			
			$this->update($result);
			$this->setResourceCount($result);
			
			return $result;
		}
		
		public function getResourceCount(array $resources) {
			$arrReturn = array();
			
			foreach ($resources as $res) {
				$arrReturn['resources.'.$res] = true;
			}
			
			return MongoInterface::getResources($_SESSION['selecplanet'], $arrReturn);
		}
				
		public function getResourceCountOf($resourceType) {
			$arrReturn = array('_id' => false, 'resources.'.$resourceType => true);
			return MongoInterface::getResources($_SESSION['selecplanet'], $arrReturn)[$resourceType];
		}
		
		// getResourceCount2Of (array)
		
		public function setResourceCount(array $newCount) {	
			$set = array();
			foreach ($newCount as $elem => $val) {
				$set['resources.'.$elem] = $val;
			}
			
			MongoInterface::setPlanetArray($_SESSION['selecplanet'], $set);
		}
		
		public function setResourceCountOf($resourceType, $count) {
			MongoInterface::setPlanetArray($_SESSION['selecplanet'],
				array(('resources.'.$resourceType) => $count));
		}
		// TODO: mongodb
		public function addResourceCountTo($resourceType, $amount) {
			Debug::echo_info("addResourceCountTo");
			
			MongoInterface::incPlanetResources($_SESSION['selecplanet'], array(('resources.'.$resourceType) => $amount));
		}
		
		public function updateResources(array $data) {
			$arrModify = array();
			foreach ($data as $key => $val) {
				$arrModify['resources.'.$key] = $val;
			}
			
			MongoInterface::incPlanetResources($_SESSION['selecplanet'], $arrModify);
		}
		
		public function applyCosts(array $costs) {
			foreach ($costs as $key => $value) {
				$currentCount = $this->getResourceCountOf($key);
				
				if ($currentCount < $costs[$key]) {
					return false;
				}
				
				$costs[$key] *= -1;
			}
			
			$this->updateResources($costs);
			
			return true;
		}
		
		public function serialize() {
			if (isset($this->m_lackingFoodTime)) {
				$memberArray = array(
					"timelacking" 	=> $this->m_lackingFoodTime,
					"totalLosses"	=> $this->m_totalLosses,
					"starvMemento"	=> serialize($this->m_starvMemento)
				);
			}
				
			$memberArray['timestamp'] = $this->m_timeStamp;
			
			return serialize($memberArray);
		}
		
		public function unserialize($data) {
			$memberArray = unserialize($data);
			
			$this->m_timeStamp = $memberArray['timestamp'];
			
			if (isset($memberArray['timelacking'])) {
				$this->m_lackingFoodTime = $memberArray['timelacking'];
				$this->m_totalLosses = $memberArray['totalLosses'];
				$this->m_starvMemento = unserialize($memberArray['starvMemento']);				
			}
		}

// private
		private function update(array &$currentCount) {
			$lastStamp = 0;
			
			if ($this->m_timeStamp == 0) {
				$lastStamp = MongoInterface::getPlayerProperty($_SESSION['objectid'], "logout");
				if ($lastStamp == 0) {
					return;
				}
			} else {
				$lastStamp = $this->m_timeStamp;
			}
			
			$timeDiff = time() - $lastStamp;
				
			$arrPopAlloc = PopulationManager::getJobsAllocationAll();
			
			$arrBuildingConfig = MongoInterface::getConfigBuildings();
			
			$curProductionRate = array();
			
			foreach ($arrBuildingConfig as $curId => $config) {
				if (!isset($config['production'])) {
					continue;
				}
				if (!isset($arrPopAlloc[$curId])) {
					continue;
				}
				
				/* ------------------ HARDCODED ----------------------------------- */
				$energydeficit = $currentCount['energyproduction'] - $currentCount['energyconsumption'];
				$energycoverage = 1;
				if ($energydeficit < 0) {
					$energycoverage = $currentCount['energyproduction'] / $currentCount['energyconsumption'];
				}
				/*------------------------------------------------------------------*/
				
				$arrProduction = $config['production'];
				foreach ($arrProduction as $resource => $productionValue) {
					$curProductionRate[$resource] = $productionValue * $arrPopAlloc[$curId] * $energycoverage;
					$count = $timeDiff * $curProductionRate[$resource];
					$currentCount[$resource] += $count;
				}
			}
			
			$arrRes = MongoInterface::getConfigResources();
			
			/* time when everybody has starved to death (3 days)*/
			//$deadEnd = 259200;
			$deadEnd = 60;
			foreach ($arrRes as $resource => $member) {
				if (!isset($member['influenced'])) {
					continue;
				}
				
				$inflRes = $member['influenced']['res'];
				$count = $currentCount[$resource] * $member['influenced']['amount'] * $timeDiff;
				$newCount = $currentCount[$inflRes] + $count;
				/* ------------------ HARDCODED ----------------------------------- */
				if ($newCount > 0) {
					$currentCount[$inflRes] = $newCount;
					if (Flag::getFlag("starvation")) {
						Flag::setFlag("starvation", false);
						$this->unsetStarvation();
					}
				} else if ($newCount <= 0) {
					// TODO: without furder implementations: total reset after there is food again
					// TODO: adapt memento if there are more people
					if (!Flag::getFlag("starvation")) {
						Flag::setFlag("starvation", true);
						$this->setStarvationMemento($currentCount['population'], $currentCount['unemployed']);
						$this->m_lackingFoodTime = 0;
						/* how long has it been lacking during the last interval */
						/* difference of before and after */
						$denominator = ($currentCount[$inflRes] - $newCount);
						if ($denominator > 0) {
							$timeLacking = $timeDiff * abs($newCount) / $denominator;
						} else {
							$timeLacking = 0.0;
						}
						
					} else {
						$timeLacking = $timeDiff;
					}
					
					/* reset to zero, because we want no negative resource value */
					$currentCount[$inflRes] = 0;
					/* x1 of parabola */
					$norm1 = 1.5 * $this->m_lackingFoodTime / $deadEnd;
					$this->m_lackingFoodTime += $timeLacking;
					/* x2 of parabola */
					$norm2 = 1.5 * $this->m_lackingFoodTime / $deadEnd;
					/* sum of parabola area from [x1,x2] */
					$deathPercentage = $this->getParabolaSum($norm1, $norm2);
					/* if someone's dying */
					if ($deathPercentage > 0.0) {
						/* get new people distribution from PopulationManager */
						$newResources = PopulationManager::reducePopulationByPercentage($deathPercentage, $this->m_starvMemento);
						//$losses = $deathPercentage * $this->m_starvMemento->getEntry("population");
						//$this->m_totalLosses += $losses;
						//Debug::echo_info("totalLosses " . $this->m_totalLosses);
						/* set current resource count to new population values */
						foreach ($newResources as $resource => $value) {
							$currentCount[$resource] = $value;
						}
					}
				}
				/*------------------------------------------------------------------*/
			}
			
			$this->m_timeStamp = time();
			
			MongoInterface::updatePlayerObject($_SESSION['objectid'], "resourcecounter", serialize($this));
		}
		
		private function unsetStarvation() {
			unset($this->m_lackingFoodTime);
			unset($this->m_starvMemento);
		}
		
		private function setStarvationMemento($population, $unemployed) {									
			$arrMemento = array(
				"population" => $population,
				"unemployed" => $unemployed
			);
			
			$this->m_starvMemento = new StarvationMemento($arrMemento);
			PopulationManager::addJobsToMemento($this->m_starvMemento);
		}
		
		private function getParabolaSum($xStart, $xEnd) {
			if ($xStart > 1.5 || $xStart < 0) {
				$val1 = 1.0;
			} else {
				$val1 = -(16/27) * pow($xStart, 3) + (4/3) * pow($xStart, 2);
			}
			
			if ($xEnd > 1.5 || $xEnd < 0) {
				$val2 = 1.0;
			} else {
				$val2 = -(16/27) * pow($xEnd, 3) + (4/3) * pow($xEnd, 2);
			}
			
			$sum = abs($val1 - $val2);
			return $sum;
		}
		
		private function getGaussSum($xStart, $xEnd, $my) {
			$gaussVal1 = GAUSS_FACTOR * exp(-0.5 * pow($xStart - $my, 2));
			$gaussVal2 = GAUSS_FACTOR * exp(-0.5 * pow($xEnd   - $my, 2));
			Debug::echo_info("getGaussSum gaussVal1 " . $gaussVal1);
			Debug::echo_info("getGaussSum gaussVal2 " . $gaussVal2);
			$sum = abs($gaussVal2 - $gaussVal1);
			Debug::echo_info("getGaussSum sum " . $sum);
			return $sum;
		}
		
		/* calculate m_my in dependence of populationcount */
		private function getCurveShift($population) {
			// TODO: check, calculate my everytime new? -> (new if new starving phase?)
			$popInv = 1 / $population;
			// TODO: find special case solution
			if ($popInv > GAUSS_FACTOR) { /* for GAUSS_FACTOR = 0.3989423, minpopulation = sqrt(2*M_PI) */
				Debug::echo_info("PopInv greater than " . GAUSS_FACTOR . ", return form function");
				return;
			}
			
			$val = sqrt(-2 * log( $popInv/GAUSS_FACTOR ) );
			
			return $val;
		}
	}
?>