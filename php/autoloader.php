<?php
	class Autoloader {
		public static function load($classname) {
			$path = __DIR__ . '/classes/' . strtolower($classname) . '.php';
			if (file_exists($path)) {
				require_once $path;
				return true;
			} else {
				return false;
			}
		}
	}
?>