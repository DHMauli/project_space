<!DOCTYPE html>
<html lang="de">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<!-- TODO add real name -->
		<meta name="author" content="Mauli">
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">

		<meta http-equiv="language" content="de">
		<meta http-equiv="content-language" content="de">
		
		<!-- <link rel="stylesheet" type="text/css" href="../css/perfect-scrollbar.min.css"> -->
		<link rel="stylesheet" type="text/css" href="../css/perfect-scrollbar.css">
		
		<link rel="stylesheet" type="text/css" href="../css/game.css">
		<link rel="stylesheet" type="text/css" href="../css/game_mediaqueries.css">
		<link rel="stylesheet" type="text/css" href="../css/animations.css">
		<link rel="stylesheet" type="text/css" href="../css/loading.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		
		<script src="../js/perfect-scrollbar.jquery.min.js"></script>
		
		<script src="../js/classes.js"></script>
		<script src="../js/panel.js"></script>
		<script src="../js/system.js"></script>
		
		<script src="../js/buildingpanel.js"></script>
		<script src="../js/mapscroll.js"></script>
		
		<script src="../js/php_debug_handler.js"></script>
		
		<script src="../js/game.js"></script> 

		<title>Ipsum Lorem Game</title>
	</head>

	<body>
		<header>
			<div id="resbar">
				<ul id="resbar">
					<li><ul class="stylenone">
						<li>Metall: <span id="label-metal"></span></li>
						<li>Produktionsrate: <span id="label-metal-productionrate"></span></li>
					</ul></li>
					<li><ul class="stylenone">
						<li>Kristall: <span id="label-crystal"></span></li>
						<li>Produktionsrate: <span id="label-metal-productionrate"></span></li>
					</ul></li>
					<li><ul class="stylenone">
						<li>Methan: <span id="label-methane"></span></li>
						<li>Produktionsrate: <span id="label-metal-productionrate"></span></li>
					</ul></li>
					<li><ul class="stylenone">
						<li>Nahrung: <span id="label-food"></span></li>
						<li>Produktionsrate: <span id="label-metal-productionrate"></span></li>
					</ul></li>
					<li><ul class="stylenone">
						<li>Wasser: <span id="label-water"></span></li>
						<li>Produktionsrate: <span id="label-metal-productionrate"></span></li>
					</ul></li>
					<li><ul class="stylenone">
						<li>Energieverbrauch: <span id="label-energyconsumption"></span></li>
						<li>Energieproduktion: <span id="label-energyproduction"></span></li>
					</ul></li>
					<li><ul class="stylenone">
						<li>Bevölkerung:  <span id="label-population"></span></li>
						<li>Beschäftigte:  <span id="label-employed"></span></li>
						<li>Arbeitslose:  <span id="label-unemployed"></span></li>
					</ul></li>
				</ul>
			</div>
		</header>

		<div id="content">
			<div id="column-left">
				<div id="sidemenu-left">
					Generic Menu<br>
					<input id="btnLogout" type="button" value="Logout" name="btnLogout">
				</div>
				<input type="button" class="directionbutton" id="btnleft" value="<" name="btnLeft">
			</div>
			
			<div id="column-middle">
				<input type="button" class="directionbutton" id="btnup" value="^" name="btnUp">
				<div id="gamescreen">
				</div>
				<div id="systemview">
					<div id="wrapper">
					</div>
				</div>
				<input type="button" class="directionbutton" id="btndown" value="v" name="btnDown">
			</div>
			
			<div id="column-right">
				<input type="button" class="directionbutton" id="btnright" value=">" name="btnRight">
				<div id="sidemenu-right">
					Selektiertes Feld: <span id="labelselected">none</span><br>
					Feldtyp: <span id="labelfieldtype">none</span><br>
					<div class="clickmenu borderTopBottom" id="disappear" style="background-color:#fff;">
						<span id="clickmenulabel"><strong>Building Options</strong></span>
					</div>
					<div class="clickmenu borderTopBottom" id="disappear" style="background-color:#fff;">
						IPSUM LOREM
					</div>
				</div>
			</div>
		</div>
		<!-- -------------------- PANEL CONTEXT MENU --------------------------- -->
		<div class="container" id="panelcontextmenu">
			<div class="panelcenter">
				<input class="btnClose" type="button" value="">
				CONTEXT PANEL<br>
				Verbleibende Arbeiter <span id="labelUnemployment">-</span>
				<ul class="tablist">
					<li class="buildingtab">
						Metallmine<br>
						<div>
							<label>Totale Anzahl <span id="labelMetalmineTotal">0</span></label><br>
							<input class="long" id="sliderMetalmine" type="range" min="0" max="100" value="0" step="1" data-field="fieldMetalmine" data-allocoption="metalmine" data-labeltotal="labelMetalmineTotal">
							<input class="short" id="fieldMetalmine" type="text" value="0" data-slider="sliderMetalmine"><br>
						</div>
					</li>
					<li class="buildingtab">
						Kristallmine<br>
						<div>
							<label>Totale Anzahl <span id="labelCrystalmineTotal">0</span></label><br>
							<input class="long" id="sliderCrystalmine" type="range" min="0" max="100" value="0" step="1" data-field="fieldCrystalmine" data-allocoption="crystalmine" data-labeltotal="labelCrystalmineTotal">
							<input class="short" id="fieldCrystalmine" type="text" value="0" data-slider="sliderCrystalmine"><br>
						</div>
					</li>
					<li class="buildingtab">
						Methanraffinerie<br>
						<div>
							<label>Totale Anzahl <span id="labelMethaneRefineryTotal">0</span></label><br>
							<input class="long" id="sliderMethaneRefinery" type="range" min="0" max="100" value="0" step="1" data-field="fieldMethaneRefinery" data-allocoption="methanerefinery" data-labeltotal="labelMethaneRefineryTotal">
							<input class="short" id="fieldMethaneRefinery" type="text" value="0" data-slider="sliderMethaneRefinery"><br>
						</div>
					</li>
					<li class="buildingtab">
						Bauernhof<br>
						<div>
							<label>Totale Anzahl <span id="labelFarmTotal">0</span></label><br>
							<input class="long" id="sliderFarm" type="range" min="0" max="100" value="0" step="1" data-field="fieldFarm" data-allocoption="farm" data-labeltotal="labelFarmTotal">
							<input class="short" id="fieldFarm" type="text" value="0" data-slider="sliderFarm"><br>
						</div>
					</li>
				</ul>
				
				<input type="button" id="btnSubmitAllocation" class="btnStandard" value="Button Submit">
			</div>
		</div>
		<!-- ----------------------- PANEL ERROR ------------------------------- -->
		<div class="container" id="panelerror">
			<div class="panelcenter">
				<input class="btnClose" type="button" value="">
				<h3>ERROR PANEL</h3>
				<span id="errormessage"> - </span>
			</div>
		</div>
		<div class="cssload-triangles">
			<div class="cssload-tri cssload-invert"></div>
			<div class="cssload-tri cssload-invert"></div>
			<div class="cssload-tri"></div>
			<div class="cssload-tri cssload-invert"></div>
			<div class="cssload-tri cssload-invert"></div>
			<div class="cssload-tri"></div>
			<div class="cssload-tri cssload-invert"></div>
			<div class="cssload-tri"></div>
			<div class="cssload-tri cssload-invert"></div>
		</div>
		<footer>
			<input type="button" id="populationallocation" value="">
			<input type="button" id="systemView" value="SystemView">
			<input type="button" id="resetAccount" value="ResetAccount">
		</footer>
	</body>
</html>