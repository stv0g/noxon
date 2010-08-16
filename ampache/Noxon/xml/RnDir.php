<?php

	class RnDir {
		public $Title, $Url, $BackUrl, $BookmarkShow;
		public $NoAudioContent = "true";
		public $Type = "Dir";

		public function __toString() {
			return "<ItemType>".$this->Type."</ItemType>\r\n<Title>".$this->Title."</Title>\r\n<UrlDir>".$this->Url."</UrlDir>\r\n<NoAudioContent>".$this->NoAudioContent."</NoAudioContent>\r\n<UrlDirBackUp>".$this->BackUrl."</UrlDirBackUp>\r\n<BookmarkShow>".$this->BookmarkShow."</BookmarkShow>\r\n";
		}

	}

?>