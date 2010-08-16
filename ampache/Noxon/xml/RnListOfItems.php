<?php

class RnListOfItems {
	
	private $items = Array();
	private $NoCache = false;
	
	function RnListOfItems() {
		
	}
	
	function addToList ($rhs) {
	if(in_array(get_class($rhs), Array("RnDir","RnStation","RnMessage","RnDisplay"))) {
				array_push($this->items,$rhs);
			}
	}
	
	function toString() {
		$elements = Array();
			foreach($this->items as $element) {
				array_push($elements,"<Item>\r\n".$element->__toString()."</Item>");
			}
			$cache = ($this->NoCache) ? "<NoCache>Yes</NoCache>\r\n" : "";
			return "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\r\n<ListOfItems>\r\n".$cache.implode("\r\n",$elements)."\r\n</ListOfItems>\r\n";
	}
	
}

?>