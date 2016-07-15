<?php
	interface ISerializeChild {
		function serializeChild(array &$serializingArray);
		function unserializeChild(array $arrData);
	}
?>