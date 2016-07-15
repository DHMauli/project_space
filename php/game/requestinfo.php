<?php
	session_start();
	
	require_once __DIR__ . "/utility.php";
	
	if (!isset($_SESSION['email'])) {
		exit;
	}
	
   $intelType = filter_input(INPUT_POST, 'intelType');
	// TODO: safety check on POST via filter_input
	switch ($intelType) {
		case "getFieldId":
		{
			getFieldId($_POST['pos_x'], $_POST['pos_y']);
			break;
		}
		case "getBuildOptions":
		{
			getBuildOptions($_POST['pos_x'], $_POST['pos_y']);
			break;
		}
		case "getBuildOptionsSys":
		{
			if (checkPlanetOwnership($_POST['planetid']) == false) {
				break;
			}
			getBuildOptionsSys();
			break;
		}
		case "getStructureBuildOptions":
		{
			getStructureBuildOptions($_POST['type']);
			break;
		}
		case "getResource":
		{
			$resourceType = $_POST['resourceType'];
			getResource($resourceType);
			break;
		}
		case "getAllResources":
		{
			getAllResources();
			break;
		}
		case "getBuildingList":
		{
			getBuildingList();
			break;
		}
		case "getFieldTypeList":
		{
			getFieldTypeList();
			break;
		}
		case "getProcessTime":
		{
			getProcessTime($_POST['processId']);
			break;
		}
		case "getActiveProcesses":
		{
			getActiveProcesses($_POST['processCategory']);
			break;
		}
		case "getProcessState":
		{
			getProcessState($_POST['processId']);
			break;
		}
		case "checkProcessState":
		{
			checkProcessState($_POST['processId']);
			break;
		}
		case "queryEvents":
		{
			queryEvents($_POST['qualifier']);
			break;
		}
		case "getPopAlloc":
		{
			getPopulationAllocation();
			break;
		}
		case "getJobsAll":
		{
			getJobsAll();
			break;
		}
		case "getMapDimensions":
		{
			getMapDimensions();
			break;
		}
		case "getRowY":
		{
			getMapSquare($_POST['left'], $_POST['top'], 8, 1);
			break;
		}
		case "getRowX":
		{
			getMapSquare($_POST['left'], $_POST['top'], 1, 5);
			break;
		}
		case "getSystemSats":
		{
			getSatellitesOfSystem($_POST['sys']);
			break;
		}
		case "getPlanetsSats":
		{
			$planetId = $_POST['planetId'];
			getSatellitesOfPlanet($planetId);
			break;
		}
		case "getPlanetUnits":
		{
			$planetId = $_POST['planetId'];
			getPlanetUnits($planetId);
			break;
		}
		case "getCurrentPlanetId":
		{
			echo $_SESSION['selecplanet'];
			break;
		}
		default:
		{
			break;
		}
	}
?>