<?php
	class Debug {
		
		static public $ENABLED = true;
		
		static public function echo_info($data) {
			echo "__A__" . $data . "__Z__";
		}
		
		static public function echo_error($data) {
			echo "__E__" . $data . "__F__";
		}
		
		static public function echo_internal_error($data) {
			echo "__IE__" . $data . "__IF__";
		}
		
		static public function setDebugging($value) {
			$ENABLED = $value;
		}
	}
?>