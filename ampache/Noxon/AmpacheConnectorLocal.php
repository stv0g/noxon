<?php

class AmpacheConnectorLocal {

	// Error String
	private $_errorStr			= "";

	/**
			CONSTRUCTOR
	**/

	public function __construct() {

		define('NO_SESSION','1');
		require_once '../../lib/init.php';
	}

	/**
		PUBLIC FUNCTIONS
	**/

	public function artists($filter = null, $offset = null, $limit = null) {

		Browse::reset_filters();
		Browse::set_type('artist');
		Browse::set_sort('name','ASC');

		if ($filter) {
			Browse::set_filter('alpha_match',$filter);
		}

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);

		$artists 	= Browse::get_objects();
		$xml		= xmlData::artists($artists);

		return $this->_XMLStr2Array($xml, "id", array("name"));
	}

	public function artist_albums($filter = null, $offset = null, $limit = null) {

		$artist = new Artist($filter);

		$albums = $artist->get_albums();

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::albums($albums);

		return $this->_XMLStr2Array($xml, "id", array("name", "artist", "year", "tracks", "disk", "art"));
	}

	public function artist_songs($filter = null, $offset = null, $limit = null) {

		$artist = new Artist($filter);
		$songs = $artist->get_songs();

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::songs($songs);

		return $this->_XMLStr2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
	}

	public function albums($filter = null, $offset = null, $limit = null) {

		Browse::reset_filters();
		Browse::set_type('album');
		Browse::set_sort('name','ASC');

		if ($filter) {
			Browse::set_filter('alpha_match',$filter);
		}
		$albums = Browse::get_objects();

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::albums($albums);

		return $this->_XMLStr2Array($xml, "id", array("name", "artist", "year", "tracks", "disk", "art"));
	}

	public function album_songs($filter = null, $offset = null, $limit = null) {

		$album = new Album($filter);
		$songs = $album->get_songs();

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::songs($songs);

		return $this->_XMLStr2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
	}

	public function genres($filter = null, $offset = null, $limit = null) {

		Browse::reset_filters();
		Browse::set_type('genre');
		Browse::set_sort('name','ASC');

		if ($filter) {
			Browse::set_filter('alpha_match',$filter);
		}
		$genres = Browse::get_objects();

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::genres($genres);

		return $this->_XMLStr2Array($xml, "id", array("name", "songs", "albums", "artists"));
	}

	public function genre_artists($filter = null, $offset = null, $limit = null) {

		$genre = new Genre($filter);
		$artists = $genre->get_artists();

		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::artists($artists);

		return $this->_XMLStr2Array($xml, "id", array("name"));
	}

	public function genre_albums($filter = null, $offset = null, $limit = null) {

		$genre = new Genre($filter);
		$albums = $genre->get_albums();

		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::albums($albums);

		return $this->_XMLStr2Array($xml, "id", array("name", "artist", "year", "tracks", "disk", "art"));
	}

	public function genre_songs($filter = null, $offset = null, $limit = null) {

		$genre = new Genre($filter);
		$songs = $genre->get_songs();

		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::songs($songs);

		return $this->_XMLStr2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
	}

	public function songs($filter = null, $offset = null, $limit = null) {

		Browse::reset_filters();
		Browse::set_type('song');
		Browse::set_sort('title','ASC');

		if ($filter) {
			Browse::set_filter('alpha_match',$filter);
		}
		$songs = Browse::get_objects();

		// Set the offset
		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::songs($songs);

		return $this->_XMLStr2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
	}

	public function playlists($filter = null, $offset = null, $limit = null) {

		Browse::reset_filters();
		Browse::set_type('playlist');
		Browse::set_sort('name','ASC');

		if ($filter) {
			Browse::set_filter('alpha_match',$filter);
		}

		$playlist_ids = Browse::get_objects();

		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::playlists($playlist_ids);
		return $this->_XMLStr2Array($xml, "id", array("name", "owner", "items"));
	}

	public function playlist_songs($filter = null, $offset = null, $limit = null) {

		$playlist = new Playlist($filter);
		$items = $playlist->get_items();

		foreach ($items as $object) {
			if ($object['type'] == 'song') {
				$songs[] = $object['object_id'];
			}
		} // end foreach

		xmlData::set_offset($offset);
		xmlData::set_limit($limit);
		$xml	= xmlData::songs($songs);

		return $this->_XMLStr2Array($xml, "id", array("title", "artist", "album", "genre", "track", "time", "url"));
	}

	public function isConnected() {
		return true;
	}

	/**
	 * Error management
	 */
	public function _setError( $str ) {
		$this->_errorStr		= "<br>".$str;
	}

	public function getError() {
		return $this->_errorStr;
	}

	/**
		PRIVATE FUNCTIONS
	**/

	private function _XMLStr2Array($xmlStr, $key, $fieldsList) {
		// Repair a possibly broken XML String when the config Item is set
		if (NoxonConfig::$repairXML) {
			$xmlStr = str_replace("]]</","]]></",$xmlStr);
		}
		//SX:End of QnD Fix

		// XML Data ?
		$xml 				= @simplexml_load_string($xmlStr);
		if ($xml === false) {
			$this->_setError("BAD_XML");
			return false;
		}

		// valid request ?
		if (isset($xml->error) === true) {
			$this->_setError("INVALID_REQUEST");
			return false;
		}

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

}

?>