<?php
	class Redirect {
		static public function to($location = null) {
			if ($location) {
				header("Location: " . $location);
				exit();
			}
		}
	}
?>