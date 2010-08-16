<?php

require_once 'Noxon/RegisterMyNoxon.php';

class AmpacheNoxon {

	public $name			= 'Noxon';
	public $description		= 'Listen to Ampache on your Noxon !';
	public $url				= 'http://www.my-noxon.com';
	public $version			= '000002';
	public $min_ampache		= '340000';
	public $max_ampache		= '349999';

	// Error String
	private $_errorStr			= "";

	// These are internal settings used by this class, run this->load to
	// fill em out
	private $user;
	private $pass;
	private $serverCaption;
	private $strAmpacheServerURL;
	private $strAmpacheServerLANURL;

	/**
	 * Constructor
	 * This function does nothing...
	 */
	public function __construct() {

		return true;
	} // PluginNoxon

	/**
	 * install
	 * This is a required plugin function it inserts the required preferences
	 * into Ampache
	 */
	public function install() {

		Preference::insert('noxon_user','Noxon Username','','25','string','plugins');
		Preference::insert('noxon_pass','Noxon Password','','25','string','plugins');
		Preference::insert('noxon_serverCaption','Noxon Server Caption','My ampache Server','25','string','plugins');
		Preference::insert('noxon_strAmpacheServerURL','Noxon Ampache URL',$this->getLocalNetworkURL(),'25','string','plugins');
		Preference::insert('noxon_strAmpacheServerLANURL','Noxon Ampache LAN URL (optional)',$this->getLocalNetworkURL(),'25','string','plugins');
	} // install

	/**
	 * uninstall
	 * This is a required plugin function it removes the required preferences from
	 * the database returning it to its origional form
	 */
	public function uninstall() {

		Preference::delete('noxon_user');
		Preference::delete('noxon_pass');
		Preference::delete('noxon_serverCaption');
		Preference::delete('noxon_strAmpacheServerURL');
		Preference::delete('noxon_strAmpacheServerLANURL');
	} // uninstall

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
	 * load
	 * This loads up the data we need into this object, this stuff comes from the "Server Config plugins"
	 * it's passed as a key'd array
	 */
	public function load() {

		if (! $this->user = Config::get('noxon_user')) {
			$this->_setError( "Error Noxon plugin: User Config not set" );
			return false;
		}

		if (! $this->pass = Config::get('noxon_pass')) {
			$this->_setError( "Error Noxon plugin: Password Config not set" );
			return false;
		}

		if (! $this->serverCaption = ereg_replace( "[^0-9a-zA-Z_]", " ", Config::get('noxon_serverCaption'))) {
			$this->_setError( "Error Noxon plugin: Server Caption Config not set" );
			return false;
		}

		if (! $this->strAmpacheServerURL = Config::get('noxon_strAmpacheServerURL')) {
			$this->_setError( "Error Noxon plugin: Ampache Server URL Config not set" );
			return false;
		}

		/* optional */

		$this->strAmpacheServerLANURL = Config::get('noxon_strAmpacheServerLANURL');

		return true;
	}

	public function register() {

		if ($this->load()) {

			$register	= new RegisterMyNoxon($this->user, $this->pass, $this->strAmpacheServerURL, $this->strAmpacheServerLANURL, $this->serverCaption);

			if (! $ret = $register->register()) {
				$this->_setError( $register->getError() );
			}

			return $ret;
		}
		return false;
	}

	public function unregister() {

		if ($this->load()) {

			$register	= new RegisterMyNoxon($this->user, $this->pass, $this->strAmpacheServerURL, $this->strAmpacheServerLANURL, $this->serverCaption);

			if (! $ret = $register->unregister()) {
				$this->_setError( $register->getError() );
			}

			return $ret;
		}
		return false;
	}

	/**
	 * SX: Get the expected URL of the Ampache Server in order to get a probably valid default
	 */
	private function getLocalNetworkURL() {
		$strFileName = explode("admin/modules.php",$_SERVER['SCRIPT_NAME']);
		return "http://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'].$strFileName[0]."modules/plugins/Noxon_xml.server.php";
	}

} // end AmpacheNoxon

?>