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
	
	class ConfigRepository {
		
		/**
		 *	Building-Options Keywords
		 *	all - alles kann gebaut werden
		 *	none - nichts kann gebaut werden
		 */
		// test test
		
		static public function SomeFunction($text) {
			return $text;
		}
		
		static public function getStringIdExpression($fieldKey) {
			return MongoInterface::getConfigProperty("config_fieldids", $fieldKey)['id'];
		}
		
		// TODO: adapt for all graphics
		static public function getGraphicsPath($fieldKey) {
			return MongoInterface::getConfigProperty("config_fieldids", $fieldKey)['graphicspath'];
		}
		
		static public function getFieldBuildOptions($fieldId) {
			$fieldKey = self::getIdStringExpression($fieldId);
			$buildingOptions = MongoInterface::getConfigProperty("config_fieldids", $fieldKey);
			
			if (!isset($buildingOptions['buildingoptions'])) {
				return null;
			}
			
			return $buildingOptions['buildingoptions'];
		}
		
		static public function getFieldBuildInfo($fieldId, $buildingKey) {
			$fieldKey = self::getIdStringExpression($fieldId);
			
			$info = array();
			
			$buildingOptions = MongoInterface::getConfigProperty("config_fieldids", $fieldKey);
			if (isset($buildingOptions['buildingoptions'])) {
				$info['buildingoptions'] = $buildingOptions['buildingoptions'];
			}
			
			$buildingInfo = MongoInterface::getConfigProperty("config_buildings", $buildingKey);
			if (isset($buildingInfo['startconditions'])) {
				$info['startconditions'] = $buildingInfo['startconditions'];
			}
			
			return $info;
		}
		
		static public function getIdStringExpression($fieldId) {
			$configFieldIds = MongoInterface::getConfigDocument("config_fieldids");
			
			foreach ($configFieldIds as $key => $value) {
				if ($value['id'] == $fieldId) {
					return $key;
				}
			}
			
			return "notfound";
		}
		
		static public function getFieldTypeList() {
			$typeList = array();
			
			$configFieldIds = MongoInterface::getConfigDocument("config_fieldids");
			foreach ($configFieldIds as $key => $value) {
				$typeList[] = $key;
			}
				
			return $typeList;
		}
	}
?>