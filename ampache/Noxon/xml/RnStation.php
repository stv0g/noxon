<?php

	class RnStation {

		public $Id, $Name, $Url, $Description, $Format, $Location, $Bandwidth, $Mine, $Bookmark;
		public $Type = "Station";

		public function __toString() {
			return "<ItemType>".$this->Type."</ItemType>\r\n<StationId>".$this->Id."</StationId>\r\n<StationName>".$this->Name."</StationName>\n<StationUrl>".$this->Url."</StationUrl>\r\n<StationDesc>".$this->Description."</StationDesc>\r\n<StationFormat>".$this->Format."</StationFormat>\r\n<StationLocation>".$this->Location."</StationLocation>\r\n<StationBandWidth>".$this->Bandwidth."</StationBandWidth>\r\n<StationMime>".$this->Mine."</StationMime>\r\n<Bookmark>".$this->Bookmark."</Bookmark>\r\n";
		}

	}

?>