<?php

class AmpacheConnectorRemote {

	/**
		VAR
	**/
	private $_isConnected	= false;
	private	$_errorStr		= null;
	private	$_key 			= null;
	private	$_auth			= null;
	private	$_serverUrl		= null;

	/**
			CONSTRUCTOR
	**/

	public function __construct($serverUrl, $key) {

		$this->_serverUrl	= $serverUrl;
		$this->_key			= $key;

		$xml				= $this->_requestServer("handshake");

		// valid login ?
		if (isset($xml->auth) === false) {
			$this->_setError("BAD_LOGIN");
			return;
		}

		// Yeah, we are connected !
		$this->_isConnected	= true;
		// we keep the authorized key
		$this->_auth		= $xml->auth;
	}

	/**
		PUBLIC FUNCTIONS
	**/

	public function artists($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("artists", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name"));
		}
		return false;
	}

	public function artist_albums($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("artist_albums", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name", "artist", "year", "tracks", "disk", "art"));
		}
		return false;
	}

	public function artist_songs($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("artist_songs", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
		}
		return false;
	}

	public function albums($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("albums", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name", "artist", "year", "tracks", "disk", "art"));
		}
		return false;
	}

	public function album_songs($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("album_songs", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
		}
		return false;
	}

	public function genres($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("genres", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name", "songs", "albums", "artists"));
		}
		return false;
	}

	public function genre_artists($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("genre_artists", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name"));
		}
		return false;
	}

	public function genre_albums($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("genre_albums", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name", "artist", "year", "tracks", "disk", "art"));
		}
		return false;
	}

	public function genre_songs($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("genre_songs", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
		}
		return false;
	}

	public function songs($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("songs", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
		}
		return false;
	}

	public function playlists($filter = null, $offset = null, $limit = null) {
		if ($xml = $this->_requestServer("playlists", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("name", "owner", "items"));
		}
		return false;
	}

	public function playlist_songs($filter = null, $offset = null, $limit = null) {

		if ($xml = $this->_requestServer("playlist_songs", $filter, $offset, $limit)) {
			return $this->_XML2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
		}
		return false;
	}

	public function isConnected() {
		return $this->_isConnected;
	}

	public function getError() {
		return $this->_errorStr;
	}

	/**
		PRIVATE FUNCTIONS
	**/

	private function _requestServer( $action, $filter = null, $offset = null, $limit = null ) {

		if ($action == "handshake") {

			$timestamp			= time();
			$passphrase 		= md5($timestamp . $this->_key);
			$url				= $this->_serverUrl."?action=handshake&auth=$passphrase&timestamp=$timestamp";
		}
		else {

			$filter				= ( $filter )	? "&filter=".intval( $filter )		: "";
			$offset				= ( $offset )	? "&offset=".intval( $offset )		: "";
			$limit				= ( $limit )	? "&limit=".intval( $limit )		: "";

			$url				= $this->_serverUrl."?action=".$action."&auth=".$this->_auth.$filter.$offset.$limit;
		}

		// URL corrects ?
		$xmlData			= @file_get_contents($url);
		if ($xmlData === false) {
			$this->_setError("BAD_URL");
			return false;
		}

		// XML Data ?
		$xml 				= @simplexml_load_string($xmlData);
		if ($xml === false) {
			$this->_setError("BAD_XML");
			return false;
		}

		// valid request ?
		if (isset($xml->error) === true) {
			$this->_setError("INVALID_REQUEST");
			return false;
		}

		return ($xml);
	}

	private function _XML2Array($xml, $key, $fieldsList) {

		$arr				= array();
		foreach ($xml as $val) {
			$tmp			= array();
			foreach ($fieldsList as $field) {
				$tmp[$field]	= strval($val->$field);
			}
			$arr[strval($val[$key])]	= $tmp;
		}

		return ($arr);
	}

	private function _setError($errorStr) {
		$this->_errorStr		= $errorStr;
	}
}

?>