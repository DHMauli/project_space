<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));

	class Flag {
		
		static public function setFlag($flagName, $flagValue) {
			MongoInterface::setPlanetArray($_SESSION['selecplanet'], array('flags.'.$flagName => $flagValue));
		}
		
		static public function getFlag($flagName) {
			$value = MongoInterface::getPlanetFlag($_SESSION['selecplanet'], $flagName);
			return $value;
		}
	}
?>