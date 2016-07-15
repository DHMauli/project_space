<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));
	
	session_start();

	require_once __DIR__ . "/utility.php";
	
	if (!isset($_SESSION['email'])) {
		exit;
	}

	$action = $_POST['action'];
	// TODO: safety check on POST
	switch ($action) {
		case "build":
		{
			build($_POST['pos_x'], $_POST['pos_y'], $_POST['bdgId']);
			break;
		}
		case "buildInSpace":
		{
			buildInSpace($_POST['bdgId']);
			break;
		}
		case "buildUnit":
		{
			buildUnit($_POST['unitType']);
			break;
		}
		case "logout":
		{
			destroySession();
			break;
		}
		case "allocateJobs":
		{
			allocateJobs($_POST['data']);
			break;
		}
		case "resetAccount":
		{
			resetAccountData();
			break;
		}
		case "openPlanetView":
		{
			$planetid = null;
			if (isset($_POST['planetid'])) {
				$planetid = $_POST['planetid'];
			} else {
				$planetid = $_SESSION['selecplanet'];
			}
			openPlanetView($_POST['offsetX'], $_POST['offsetY'], $planetid);
			break;
		}
		case "sendUnits":
		{
			$unitArray = $_POST['units'];
			$start = $_POST['start'];
			$target = $_POST['target'];
			
			sendUnits($unitArray, $start, $target);
			break;
		}
		default:
		{
			break;
		}
	}
?>