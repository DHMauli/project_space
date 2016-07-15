<?php
	class MongoInterface {
		
		static private $m_mongoClient = null;
		
		static private function getMongo() {
			if (self::$m_mongoClient == null) {
				self::$m_mongoClient = new MongoClient();
			}
			
			return self::$m_mongoClient;
		}
		
		/*
		*			_GENERAL
		*/
		static public function getCollection($database, $collection) {
			$mongoClient = new MongoClient();
			return iterator_to_array($mongoClient->selectCollection($database, $collection)->find(), true);
		}
		
		static public function insertDocument($database, $collection, $document) {
			$mongoClient = self::getMongo();
			$col = $mongoClient->selectCollection($database, $collection);
			return $col->insert($document);
		}
		
		/*
		*			_CONFIG
		*/
		static public function getConfigDocument($configName) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array($configName =>
					array('$exists' => true))
			);
			$oid = $result->getNext()[$configName];
			
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array('_id' => new MongoId($oid)),
				array('_id' => false)
			);
			
			return $result->getNext();
		}		
		
		static public function getConfigProperty($configName, $property) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array($configName =>
					array('$exists' => true))
			);
			$oid = $result->getNext()[$configName];
			
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array('_id' => new MongoId($oid)),
				array('_id' => false, $property => true)
			);
			
			return $result->getNext()[$property];
		}
		
		static public function getConfigBuildings() {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array("config_buildings" =>
					array('$exists' => true))
			);
			$oid = $result->getNext()["config_buildings"];
			
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->findOne(
				array('_id' => new MongoId($oid)),
				array('_id' => false)
			);
			
			return $result;
		}
		
		static public function getBuildEventMessage($buildingName) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array("config_buildings" =>
					array('$exists' => true))
			);
			$oid = $result->getNext()["config_buildings"];
			
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->findOne(
				array('_id' => new MongoId($oid)),
				array('_id' => false, $buildingName.'.eventMsg' => true)
			);
			
			if (isset($result[$buildingName]['eventMsg'])) {
				return $result[$buildingName]['eventMsg'];
			} else {
				return "";
			}
			
		}
		
		static public function getConfigResources() {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->find(
				array("config_resources" =>
					array('$exists' => true))
			);
			$oid = $result->getNext()["config_resources"];
			
			$result = $mongoClient->selectCollection("gamedb", "cconfig")->findOne(
				array('_id' => new MongoId($oid)),
				array('_id' => false)
			);
			
			return $result;
		}
		
		/*
		*			_PLANETS
		*/
		static public function insertPlanet(array $data) {
			self::insertDocument("gamedb", "cplanets", $data);
		}
		
		static public function getPlanetSats($planetId) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->find(
				array('id' => $planetId),
				array('sats' => true)
			);
			
			return $result->getNext()['sats'];
		}
		
		static public function clearSatellites($planetid) {
         Debug::echo_info("Clearing satellites");
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetid),
				array('$set' =>
					array('sats' => []))
			);
		}
		
		static public function getPlanetProperty($planetid, $property) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->findOne(
				array('id' => $planetid),
				array('_id' => false, $property => true)
			);
			
			return $result[$property];
		}
		
		static public function addSatelliteToPlanet($planetid, $satellite) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetid),
				array('$push' => array("sats" => $satellite))
			);
		}
		
		static public function getPlanetsInfo(array $planets, array $return) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->find(
				array('id' =>
					array('$in' => $planets)),
				$return
			);
			return iterator_to_array($result);
		}
		
		static public function getSystemOfPlanet($planetId) {
			
			// TODO: add 'sys' property to cplanets documents
			
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->find(
				array('id' => $planetId),
				array('_id' => false, 'sys' => true)
			);
			
			return $result->getNext()['sys'];
		}
		
		static public function getFreePlanet($systemid) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->findOne(
				array('free' => true),
				array('id' => true)
			);
			
			return $result['id'];
		}
		
		static public function getMapOfPlanet($planetid) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->findOne(
				array('id' => $planetid),
				array('_id' => false, 'map' => true)
			);
			
			return $result['map'];
		}
		
		static public function getResources($planetid, array $return) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->find(
				array('id' => $planetid),
				$return
			);
			
			return $result->getNext()['resources'];
		}
		
		static public function getJobsProperty($planetid, $prop) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->findOne(
				array('id' => $planetid),
				array('_id' => false, ('jobs.'.$prop) => true)
			);
			
			return $result['jobs'][$prop];
		}
		
		static public function getPlanetFlag($planetid, $prop) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->findOne(
				array('id' => $planetid),
				array('_id' => false, 'flags.'.$prop => true)
			);
			
			return $result['flags'][$prop];
		}
		
		static public function setPlanetArray($planetid, array $new) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetid),
				array('$set' => $new)
			);
		}
		
		static public function updateMapOfPlanet($planetid, $x, $y, $value) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetid),
				array('$set' =>
					array(('map.'.$x.'.'.$y) => $value))
			);
		}
		
		static public function updatePlanetJobs($planetid, $buildingid, $amount) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetid),
				array('$inc' =>
					array(('jobs.'.$buildingid) => $amount))
			);
		}
		/* data = associative array in format, resource: amount */
		static public function incPlanetResources($planetid, array $data) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetid),
				array('$inc' => $data)
			);
		}
		/*
		// TODO find out how this could work OMG !!! WTF!!! not working because an array, instead of associative object list?
		static public function getIdOfField($planetid, $x, $y) {
			$mapIndex = 'map.5.5';
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->find(
				array('id' => $planetid),
				array('_id' => false, $mapIndex => true)
			);
			var_dump(iterator_to_array($result));
			//return ['map'][4][4];
		}
		*/
		
		static public function addUnitsToPlanetOrbit($planetID, $userID, array $units) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetID),
				array('$addToSet' =>
					array('unit.'.$userID =>
						array('$each' => $units)))
			);
		}
		
		static public function removeUnitsFromPlanetOrbit($planetID, $userID, array $units) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetID),
				array('$pull' =>
					array('unit.'.$userID =>
						array('$in' => $units)))
			);
			
			/*
			$incArray = array();
			
			foreach ($units as $key => $value) {
				$incArray['unit.'.$userID.'.'.$key] = ($value * -1);
			}
			
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplanets")->findAndModify(
				array('id' => $planetID),
				array('$inc' => $incArray)
			);
			*/
		}
		
		static public function getUnitsOfPlanet($planetID, $userID) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplanets")->findOne(
				array('id' => $planetID),
				array('_id' => false, ('unit.'.$userID) => true)
			);
			
			$strUserID = $userID->{'$id'};
			
			if (!isset($result["unit"])) {
				return [];
			}
			if (!isset($result["unit"][$strUserID])) {
				return [];
			}
			
			return $result["unit"][$strUserID];
		}
		
		/*
		*			_PLAYER
		*/
		static public function insertPlayer(array $data) {
			self::insertDocument("gamedb", "cplayer", $data);
		}
		
		static public function insertProcess(array $data) {
			self::insertDocument("gamedb", "cprocesses", $data);
		}
		
		static public function getUserHashByEmail($email) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplayer")->find(
				array('email' => $email)
			);
			
			$oid = $result->getNext()['_id'];
			return $oid;
		}
		
		static public function insertSinglePlayerObject($userhash, $objectid, $object) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplayer")->findAndModify(
				array('_id' => $userhash),
				array('$push' =>
					array('objectlist' =>
						array('objname' => $objectid, 'object' => $object)))
			);
		}
				
		static public function insertPlayerObjects($userhash, array $objects) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplayer")->findAndModify(
				array('_id' => $userhash),
				array('$push' =>
					array('objectlist' =>
						array('$each' => $objects)))
			);
		}
		
		static public function getUserLoginData($email) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplayer")->find(
				array('email' => $email),
				array('_id' => true, 'name' => true, 'pwhash' => true, 'active' => true)
			);
			
			return $result->getNext();
		}
		
		static public function getPlayerObject($userhash, $objectName) {
			$mongoClient = self::getMongo();
			$cursor = $mongoClient->selectCollection("gamedb", "cplayer")->find(
				array('_id' => $userhash),
				array('_id' => false, 'objectlist' =>
					array('$elemMatch' =>
						array('objname' => $objectName)))
			);
			
			$object = "";
			if ($cursor->hasNext()) {
				$next = $cursor->getNext();
				if (isset($next['objectlist'])) {
					$object = $next['objectlist'][0]['object'];
				}
			} else {
				Debug::echo_info("MongoInterface getPlayerObject " . $objectName
				. " of " . $userhash . ": cursor has no next");
			}
			
			return $object;
		}
		
		static public function dropPlayerObjects($userhash) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplayer")->findAndModify(
				array('_id' => $userhash),
				array('$set' =>
					array('objectlist' => []))
			);
		}
		
		static public function dropSinglePlayerObject($userhash, $objectName) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplayer")->findAndModify(
				array('_id' => $userhash),
				array('$pull' =>
					array('objectlist' =>
							array('objname' => $objectName)))
			);
		}
		
		static public function getFirstPlanetOfPlayer($userhash) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplayer")->findOne(
				array('_id' => $userhash),
				array('_id' => false, 'planets' => true)
			);
			
			return $result['planets'][0];
		}
		
		static public function getPlayerProperty($userhash, $property) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cplayer")->findOne(
				array('_id' => $userhash),
				array('_id' => false, $property => true)
			);
			
			return $result[$property];
		}
		
		static public function getUserCount($email) {
			$mongoClient = self::getMongo();
			return $mongoClient->selectCollection("gamedb", "cplayer")->count(
				array('email' => $email)
			);
		}
		
		static public function updatePlayerObject($userhash, $objname, $object) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplayer")->findAndModify(
				array('_id' => $userhash, 'objectlist.objname' => $objname),
				array('$set' =>
					array('objectlist.$.object' => $object))
			);
		}
		
		static public function setPlayerProperty($userhash, $property, $value) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "cplayer")->findAndModify(
				array('_id' => $userhash),
				array('$set' =>
					array($property => $value))
			);
		}
		
		/*
		*			_SYSTEMS
		*/
		static public function insertSystem(array $data) {
			self::insertDocument("gamedb", "csystems", $data);
		}
		
		static public function addSatelliteToSystem($systemId, $planetId) {
			$mongoClient = self::getMongo();
			$mongoClient->selectCollection("gamedb", "csystems")->findAndModify(
				array('id' => $systemId),
				array('$push' => array("sats" => $planetId))
			);
		}
		
		static public function getSystemSats($systemId) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "csystems")->find(
				array('id' => $systemId),
				array('sats' => true)
			);
			
			return $result->getNext()['sats'];
		}
		
		/*
		*			_UNITS
		*/
		static public function getUnitByOID($OID) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cunits")->find(
				array('_id' => new MongoId($OID))
			);
			
			return $result->getNext();
		}
		
		static public function getUnitByName($name) {
			$mongoClient = self::getMongo();
			$result = $mongoClient->selectCollection("gamedb", "cunits")->find(
				array('name' => $name)
			);
			
			return $result->getNext();
		}
	}
?>