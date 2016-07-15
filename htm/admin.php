<!DOCTYPE HTML>

<html lang="de">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<!-- Add real name -->
		<meta name="author" content="Mauli">
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		
		<meta http-equiv="language" content="de">
		<meta http-equiv="content-language" content="de">
		
		<title>Admin-Schnittstelle</title>
	</head>
	
	<body>
		<div id="content">
			<form action="../php/classes/planetmanager.php" method="post">
				<label for="planetid">Einzigartige ID des Planeten</label><br>
				<input type="text" name="planetid"><br><br>
				<label for="planetname">Label des Planeten</label><br>
				<input type="text" name="planetname"><br><br>
				<label for="inputname">Systemzugehörigkeit</label><br>
				<input type="text" name="systemname"><br><br>
				<label for="template">Template</label><br>
				<input type="text" name="template"><br>
				<input type="submit" value="create">
			</form>
			<br><br>
			<form action="../php/classes/processmanager.php" method="post">
				<label for="email">Spieler E-Mail</label><br>
				<input type="text" name="email"><br>
				<input type="submit" value="Clear Processes">
			</form>
			<br><br>
			<form action="../php/classes/planetmanager.php" method="post">
				<label for="planetid">PlanetID</label><br>
				<input type="text" name="planetid"><br>
				<input type="submit" value="Clear Satellites">
			</form>
			<!--
			<form action="../php/classes/planetmanager.php" method="post">
				<label>Koordinaten</label><br>
				<input type="text" name="pos-x">
				<input type="text" name="pos-y"><br>
				<input type="submit" value="Query">
			</form>
			-->
		</div>
	</body>
</html>