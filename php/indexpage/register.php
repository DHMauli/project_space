<?php
	require_once '../autoloader.php';
	spl_autoload_register(array('Autoloader', 'load'));

	if (isset($_POST["nickname"]) and
		isset($_POST["email1"]) and isset($_POST["email2"]) and
		isset($_POST["pass1"]) and isset($_POST["pass2"])) {

		$username = $_POST["nickname"];

		$email1 = $_POST["email1"];
		$email2 = $_POST["email2"];

		$pass1 = $_POST["pass1"];
		$pass2 = $_POST["pass2"];

		$exitscript = false;

		if (strlen($username) > 0 and strlen($email1) > 0 and strlen($email2) > 0 and
			strlen($pass1) > 0 and strlen($pass2) > 0) {

			if ($email1 != $email2) {
				echo "E-Mail Angaben sind nicht identisch.<br />";
				$exitscript = true;
			}
			
			// TODO: regex for nickname and password
			
			$regEx = "/^(?:[a-z0-9\\.\\-])*@((?:[a-z0-9\\-])*\\.[a-z]{2,3}){1,3}$/i";
			
			if (preg_match($regEx, $email1) == 0) {
				echo "E-Mail Adresse wird nicht angenommen.<br />";
				$exitscript = true;
			}
			
			if ($pass1 != $pass2) {
				echo "Passwörter sind nicht identisch.<br />";
				$exitscript = true;
			}
			
			if ($exitscript == true) {
				exit;
			}
			
			$menge = MongoInterface::getUserCount($email1);
			
			if ($menge == 0) {
				$mdPassword = new ModulePassword;
				$strPassword = $mdPassword->bcrypt_encode($email1, $pass1);
				
				$unixtime = time();
				
				$freePlanet = MongoInterface::getFreePlanet('s001');
				
				$userdata = array(
					"name"				=> $username,
					"email"				=> $email1,
					"pwhash"			=> $strPassword,
					"unixtime"			=> $unixtime,
					"logout"			=> $unixtime,
					"active"			=> false,
					"planets"			=> array($freePlanet),
					"objectlist" 		=> [],
					"firsttimelogin" 	=> true
				);
				
				$success = MongoInterface::insertDocument("gamedb", "cplayer", $userdata);
				
				MongoInterface::setPlanetArray($freePlanet, array(
					'owner' => $userdata['_id'],
					'free' => false,
					/* HACK */
					'resources' => array(
						'population' 		=> 0,
						'metal'				=> 5000,
						'crystal'			=> 3000,
						'methane'			=> 1000,
						'water'				=> 1000,
						'food'				=> 10000,
						'energy'			=> 0,
						'energyproduction'	=> 0,
						'energyconsumption'	=> 0,
						'employed'			=> 0,
						'unemployed'		=> 0
					),
					'jobs' => array(
						'totaljobs'			=> 0,
						'metalmine'			=> 0,
						'crystalmine'		=> 0,
						'methanerefinery'	=> 0,
						'farm'				=> 0,
						'greenhouse'		=> 0,
						'powerplantcoal'	=> 0
					),
					'jobsalloc' => array(
						'metalmine'			=> 0,
						'crystalmine'		=> 0,
						'methanerefinery'	=> 0,
						'farm'				=> 0,
						'greenhouse'		=> 0,
						'powerplantcoal'	=> 0
					)
				));
				
    			if ($success == true) {
					sendMail();
    				echo "Registrierung erfolgreich.<br />";
    			} else {
    				echo "Es ist ein Fehler aufgetreten.<br />";
    				echo "<a href=\"test_index.php\">Startseite</a>";
    			}
			} else {
				$conn->close();
				echo "Benutzername ist bereits vergeben.<br />";
				echo "<a href=\"test_index.php\">Startseite</a>";
			}
		} else {
			$conn->close();
			echo "Fehlende Angaben.<br />";
			echo "<a href=\"test_index.php\">Startseite</a>";
		}
	}
	
	function sendMail() {
		/*
		$linkraw = "http://mauli.colossusart.com/activate.php?value=".$hash;
		$link = "<a href=\"".$linkraw."\">".$linkraw."</a>";

		$empfaenger = $email1;
		$betreff = "Anmeldungsbestätigung";
		$from = "From: Maximilian Mauerer <maulii93@gmail.com>\n";
		$from .= "Content-Type: text/html\n";
		$text = "Hallo ".$_SESSION['nickname'].",<br />".
				"willkommen bei |||||||||||. Bitte bestätige deine Anmeldung
				 durch Klick auf den folgenden Link:<br />".$link;

		mail($empfaenger, $betreff, $text, $from);
		*/
	}
?>