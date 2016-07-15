<?php
	// VIEWPORT SIZE
	define('VIEWPORT_WIDTH', 8);
	define('VIEWPORT_HEIGHT', 5);

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$dirPath = __DIR__;
	$slashPos = strrpos(__DIR__, "\\");
	
	if (!$slashPos) {
		$slashPos = strpos(__DIR__, "/");
	}
	
	$autoloaderPath = substr($dirPath, 0, $slashPos) . "/autoloader.php";
	$autoloaderPath = str_replace("\\", "/", $autoloaderPath);
	
	require_once $autoloaderPath;
	spl_autoload_register(array('Autoloader', 'load'));
	
	include_once substr($dirPath, 0, $slashPos) . '/game/utility.php';
	
	class PlanetMap {
		
		static public function getHtmlMap($offsetX, $offsetY, $planetid) {
			$map = PlanetManager::getMapSquare($offsetX, $offsetY, VIEWPORT_WIDTH, VIEWPORT_HEIGHT, $planetid);
			$mapDimensions = PlanetManager::getMapDimensions($planetid);
			
			$offsetX = max(0, $offsetX);
			$offsetY = max(0, $offsetY);
			
			$offsetX = min($mapDimensions[0], $offsetX + VIEWPORT_WIDTH) - VIEWPORT_WIDTH;
			$offsetY = min($mapDimensions[1], $offsetY + VIEWPORT_HEIGHT) - VIEWPORT_HEIGHT;
			
			$html = "";
			
			for ($y = $offsetY; $y < $offsetY + VIEWPORT_HEIGHT; $y++) {
				for ($x = $offsetX; $x < $offsetX + VIEWPORT_WIDTH; $x++) {
					$id = "x".$x."-"."y".$y;
					switch ($map[$x-$offsetX][$y-$offsetY]) {
						// TODO: get database of id-String-Expressions
						case 0: /*empty*/
						{
							$html .= "<div class=\"tile empty\" id=\"".$id."\"></div>";
							break;
						}
						case 1: /* grass */
						{
							$html .= "<div class=\"tile image grass\" id=\"".$id."\"></div>";
							break;
						}
						case 2: /* rock */
						{
							$html .= "<div class=\"tile image rock\" id=\"".$id."\"></div>";
							break;
						}
						case 49: /*constructionsite*/
						{
							$html .= "<div class=\"tile image constructionsite\" id=\"".$id."\"></div>";
							break;
						}
						case 50: /*metalmine*/
						{
							$html .= "<div class=\"tile image metalmine\" id=\"".$id."\"></div>";
							break;
						}
						case 51: /*crystalmine*/
						{
							$html .=  "<div class=\"tile image crystalmine\" id=\"".$id."\"></div>";
							break;
						}
						case 52: /*methanerefinery*/
						{
							$html .=  "<div class=\"tile image methanerefinery\" id=\"".$id."\"></div>";
							break;
						}
						case 53: /*farm*/
						{
							$html .=  "<div class=\"tile image farm\" id=\"".$id."\"></div>";
							break;
						}
						case 54: /*city*/
						{
							$html .=  "<div class=\"tile image city\" id=\"".$id."\"></div>";
							break;
						}
						case 55: /*solarpanels*/
						{
							$html .=  "<div class=\"tile image solarpanels\" id=\"".$id."\"></div>";
							break;
						}
						case 56: /*greenhouse*/
						{
							$html .=  "<div class=\"tile image greenhouse\" id=\"".$id."\"></div>";
							break;
						}
						default:
						{
							$html .=  "<div class=\"tile empty\" id=\"".$id."\"></div>";
							break;
						}
					}
				}
				$html .=  "<div style=\"clear: both;\"></div>";
			}
			
			return $html;
		}
	}
?>