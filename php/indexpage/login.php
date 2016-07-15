<?php
	session_start();

	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));
	
	if (isset($_POST["email1"]) and isset($_POST["pass1"])) {
		/*
		$dbservername 	= "localhost";
		$dbusername 	= "root";
		$dbpassword 	= "rootpassword";
		$dbname 		= "testdata";

		$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		}
		//$verbindung = mysql_connect("176.28.12.152", "webmauli" , "#W4uJ!%!5#0nL!n3")
		//	or die("Verbindung zur Datenbank konnte nicht hergestellt werden");
		//mysql_select_db("maindb") or die ("Datenbank konnte nicht ausgewählt werden");
		*/
		$email1 = $_POST['email1'];
		$pass1 = $_POST['pass1'];

		if (isset($_SESSION['email'])) {
			echo "Bereits angemeldet.<br />";
			echo "<a href=\"../index.htm\">Startseite</a>";
			exit;
		}

		if (strlen($email1) > 0 and strlen($pass1) > 0) {
			
			if (MongoInterface::getUserCount($email1) == 0) {
				echo "Benutzer mit E-Mail " . $email1 . " wurde nicht gefunden.<br />";
				echo "<a href=\"test_index.php\">Startseite</a><br />";
				exit;
			}
			
			$loginData = MongoInterface::getUserLoginData($email1);
			
			if (!$loginData['active']) {
				echo "Benutzer wurde noch nicht authorisiert.<br />";
				exit;
			}

			$mdPassword = new ModulePassword();

			if ($mdPassword->bcrypt_check($email1, $pass1, $loginData['pwhash'])) {
				$_SESSION[ 'email'		] 	= $email1;
				$_SESSION[ 'name'		] 	= $loginData['name'];
				$_SESSION[ 'login'		] 	= time();
				$_SESSION[ 'objectid'	]	= $loginData['_id'];
				
				$firstTimeLogin = MongoInterface::getPlayerProperty($loginData['_id'], "firsttimelogin");
				
				if ($firstTimeLogin) {
					$objects = array();
					$objects[] = array('objname' => "processmng", 'object' => serialize(new ProcessManager()));
					$objects[] = array('objname' => "eventrepo", 'object' => serialize(new EventRepository()));
					$objects[] = array('objname' => "resourcecounter", 'object' => serialize(new ResourceCounter()));
					MongoInterface::insertPlayerObjects($loginData['_id'], $objects);
					MongoInterface::setPlayerProperty($loginData['_id'], "firsttimelogin", false);
				}
				
				$_SESSION[ 'selecplanet']	= MongoInterface::getFirstPlanetOfPlayer($loginData['_id']);
				
				Redirect::to("../../htm/main.php");
			} else {
				echo "Falsches Passwort.<br />";
				echo "<a href=\"test_index.php\">Startseite</a><br />";
			}
		}
	}
?>