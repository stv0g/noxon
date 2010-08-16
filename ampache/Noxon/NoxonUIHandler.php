<?php

require_once("AmpacheConnectorRemote.php");
require_once("AmpacheConnectorLocal.php");

require_once("xml/RnDir.php");
require_once("xml/RnListOfItems.php");
require_once("xml/RnStation.php");
require_once("NoxonConfig.php");
require_once("Speller.php");
require_once("internat.php");

/**
 * Noxon UI Handler for Noxon Ampache Installation
 *
 * @author Manfred Dreese / TTEG
 */
class NoxonUIHandler {
	private $HTTPEvent;
	private $amcConnector = null;
	private $strBaseURL="";
	private $mySpeller;

	/** Construction */
	function NoxonUIHandler () {

		if ( $_REQUEST['token'] != NoxonConfig::getToken() ) {
				throw new Exception("NoxonUIHandler::AmpacheConnector::ConnectionFailed");
		}

		if ( NoxonConfig::useAmpacheConfig() ) {
			$this->amcConnector = new AmpacheConnectorLocal();
		}
		else {
			$this->amcConnector = new AmpacheConnectorRemote(NoxonConfig::$ampacheUrl."/server/xml.server.php", NoxonConfig::$passphrase);
		}
		if (!$this->amcConnector->isConnected()) {
			throw new Exception("NoxonUIHandler::AmpacheConnector::ConnectionFailed");
		}

		$this->mySpeller = new Speller(NoxonConfig::$spellerCriticalMass);

	}

	function isConnected() {
		return $this->amcConnector->isConnected();
	}
	
	private function initGETData(&$inArray=null, $index, $inDefault) {
			if ($inArray!=null)
			if (!isset($inArray[$index]) || $inArray[$index]==null) {
				$inArray[$index]=$inDefault;
			}
	}

	/** Standard Receiver for GETData */
	function processRequest($rhs) {
		// Any base URL provided ?
		if (isset($rhs["script.baseURL"])) {
			$this->strBaseURL = $rhs["script.baseURL"];

			$result = new RnListOfItems();
			foreach (Array("filter","action","speller_filter","dlang") as $ele) {
				$this->initGETData($rhs, $ele, "");	
			}
						
			/*
			 * SX: Substitute the Country code for an Element of the 
			 * Internationalization Set.
			 * 
			 * "Belangrijke" Fix for Noxon3.7 bug : Device reports "jpn" 
			 * when device language set to dutch.
			 */			
			$rhs["dlang"] = Internat::substituteCountryCode(($rhs["dlang"]=="jpn")?"NL" : $rhs["dlang"]);
	
			switch ($rhs["action"]) {

				case "artists" :
				case "speller" :
					$artists = $this->amcConnector->artists($rhs["filter"]);

					/*
					 * The Speller is used when :
					 * - it is enabled in the configuration AND the user selected the speller menuitem
					 * - in any case if the list length is above the devices critical mass
					 */

					if ( (NoxonConfig::$blUseSpeller && $rhs["action"]=="speller") || sizeof($artists) > 400 ) {
						// If Artist Count is above Noxons Critical mass, ignore the user setting
						// and use the speller anyway.

						// Start Speller
						if ( $this->mySpeller->isBelowCriticalMass($rhs["speller_filter"],$artists,"name") ) {
							// If current scope is below critical mass
							foreach ($this->mySpeller->getFilteredSubset() as $id => $artist ) {
								$result->addToList($this->AmpElement2DirElement($id,
								$artist['name'] , "artist_albums"));
							}
						}
						else {
							// Our Subset is above the critical mass. Prepare another run.
							foreach ($this->mySpeller->getNextRunElements() as $id => $dummy ) {
								$result->addToList($this->AmpElement2DirElement("",
								$id."..." , "speller","&speller_filter=".urlencode($id)) );
							}
						}
					} // Speller activated ?
					else{
						// No Speller
						foreach ($artists as $id => $artist ) {
							$result->addToList($this->AmpElement2DirElement($id,
							$artist['name'] , "artist_albums"));
						}
					} // No Speller

					break;
				case "artist_albums":
					$albums= $this->amcConnector->artist_albums($rhs["filter"]);
					foreach ($albums as $id => $album ) {
						$result->addToList($this->AmpElement2DirElement($id,
						$album['name'] , "album_songs"));
					}
					break;

				case "playlist_songs":
					$songs = $this->amcConnector->playlist_songs($rhs["filter"]);
					// fall through
						
				case "similar_songs":
					// Check whether it is a fall-through or not
					if (!isset ($songs)) {
						$songs = $this->amcConnector->genre_songs($rhs["filter"]);
						// Create a subset if structure is too large
						$arrSize = sizeof($songs);
						if ($arrSize >= NoxonConfig::$maxSimilarSongItems ) {
							shuffle($songs);
							$arrSize = NoxonConfig::$maxSimilarSongItems;
							$songs = array_slice($songs,0,$arrSize);
						}
					} // fi isset songs
					// fall through

				case "album_songs":
					// Check whether it is a fall-through or not
					if (!isset ($songs)) {
						$songs = $this->amcConnector->album_songs($rhs["filter"]);
					} // fi isset songs

					foreach ($songs as $id => $song ) {
						$strBkm = "&aid="."0"."&gid=".$song["genre"];
						if (isset($rhs["server.httpHostname"])
						&& NoxonConfig::$remapStreamingURLs==true) {
							$result->addToList($this->AmpSongToStationElement($id,
							$song , $rhs["server.httpHostname"], $strBkm) );
						}
						else {
							$result->addToList($this->AmpSongToStationElement($id,
							$song, null, $strBkm  ));
						}
					}
					break;
					
				case "song_smiley":
					$menuItems = $this->getMenu("cna_song",$rhs["dlang"]);
					foreach ($menuItems as $index => $MenuItem) {
						$result->addToList($this->AmpElement2DirElement($rhs["gid"],
						$MenuItem["caption"], $index , $MenuItem["getdata"] ));
					}
					break;

				case "genres":
					$genres = $this->amcConnector->genres($rhs["filter"]);
					foreach ($genres as $id => $playlist ) {
						$result->addToList($this->AmpElement2DirElement($id,
						$playlist['name']." (".$playlist['artists']." Artists)" , "genre_artists"));
					}
					break;

				case "genre_artists":
					$artists = $this->amcConnector->genre_artists($rhs["filter"]);
					foreach ($artists as $id => $artist ) {
						$result->addToList($this->AmpElement2DirElement($id,
						$artist['name'] , "artist_albums"));
					}
					break;



				case "playlists":
					$playlists = $this->amcConnector->playlists($rhs["filter"]);
					foreach ($playlists as $id => $playlist ) {
						$result->addToList($this->AmpElement2DirElement($id,
						$playlist['name'] , "playlist_songs"));
					}
					break;

				default:
					// Present Main Menu;
					$menuItems = $this->getMenu("root",$rhs["dlang"]);
					foreach ($menuItems as $index => $MenuItem) {
						$result->addToList($this->AmpElement2DirElement("",  $MenuItem["caption"], $index , $MenuItem["getdata"] ));
					}
					break;

			} // esac action
			return ($result->toString());

		}
		else  {
			//TODO : Throw Exception here
		}
	}

	/**
	 * Returns the Ampache Menus
	 *
	 * @param string $inMnuID
	 * @param string $inLang
	 * @return menu Array
	 */
	function getMenu($inMnuID, $inLang) {
		$result = Array();
		if ($this->isConnected()) {
			switch ($inMnuID) {
				case "root":
					$result["artists"]["caption"] = Internat::getString($inLang,"artistalbums");
					$result["artists"]["getdata"] = "";
					// Add the Speller menuentry when activated in config
					if (NoxonConfig::$blUseSpeller) {
						$result["speller"]["caption"] = Internat::getString($inLang,"artistssearch");
						$result["speller"]["getdata"] = "";
					}
					$result["genres"]["caption"] = Internat::getString($inLang,"genres");
					$result["genres"]["getdata"] = "";
					$result["playlists"]["caption"] = Internat::getString($inLang,"playlists");
					$result["playlists"]["getdata"] = "";
					break;
						
				case "cna_song":
					$result["genre_artists"]["caption"] = Internat::getString($inLang,"similar_artists");
					$result["genre_artists"]["getdata"] = "";
					$result["similar_songs"]["caption"] = Internat::getString($inLang,"similar_titles");
					$result["similar_songs"]["getdata"] = "";
					break;

			} // esac
				
		} // fi isConnected ?
		return $result;
	}

	function AmpElement2DirElement ($inId, $inName ,$inFilialAction ,$getData="") {
		$result = new RnDir();
		$result->Title = utf8_decode($inName);
		$result->Url = $this->strBaseURL."?action=".$inFilialAction."&filter=".$inId.$getData."&token=".NoxonConfig::getToken();
		return $result;
	}

	function AmpSongToStationElement ($inId, $inSong, $inRemap=null, $inBookmarkGETData=null) {
		$result = new RnStation();
		// Remap URL to non-Local Address if Ampache Server is on local machine
		if ($inRemap) {
			$url = str_replace("http://127.0.0.1", $inRemap, $inSong['url']);
		}
		else $url = $inSong['url'];
		$result->Name = utf8_decode($inSong['title']);
		$result->Url = $url;
		if ( true || $inBookmarkGETData!=null) {
			$result->Bookmark = $this->strBaseURL."?action=song_smiley&".$inBookmarkGETData."&token=".NoxonConfig::getToken();
		}
		return $result;
	}

	static function AmpElement2MessageElement ($inMessage) {
		$result = new RnDir();
		$result->Title = $inMessage;
		return $result;
	}

	static function getNoxonError ($inStrErrorMsg) {
		$result = new RnListOfItems();
		$result->addToList(NoxonUIHandler::AmpElement2MessageElement($inStrErrorMsg));
		return ($result->toString());
	}


}

?>