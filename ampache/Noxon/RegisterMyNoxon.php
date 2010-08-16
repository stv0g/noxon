<?php

//SX: Load NoxonConfig in order to have access to Token seed
require_once("NoxonConfig.php");

class RegisterMyNoxon {

	private $_errorStr			= "";

	// Static strings
	private $strApplication		= "AMPACHE00";
	private $inStrBFishKey		= "7657BCD78698DE875FFF572481ABD626";
	private $inStrBFishIV		= "264912875DEC9658";
	private $inStrToken			= "aAhcDg";
	private $strRegistrationURL	= "http://gatekeeper.my-noxon.net/remote/UDSRegistration/RegisterUDS.php";

	/**
	 * Constructor
	 */
	public function __construct($user, $pass, $strAmpacheServerURL, $strAmpacheServerLANURL, $serverCaption) {

		$this->user						= $user;
		$this->pass						= $pass;
		$this->strAmpacheServerURL		= $strAmpacheServerURL;
		$this->strAmpacheServerLANURL	= $strAmpacheServerLANURL;
		$this->serverCaption			= $serverCaption;
	}

	/**
	 * register to the my-noxon site
	 */
	public function register() {
		$ret		= $this->_request_my_noxon("add");
		return $ret;
	}

	/**
	 * unregister to the my-noxon site
	 */
	public function unregister() {
		$ret		= $this->_request_my_noxon("remove");
		return $ret;
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
	 * private function to request the my-noxon site
	 */
	private function _request_my_noxon($strAction) {

		// include needed class
		require_once 'UDSAuth.php';

		if ( ! in_array( $strAction, array( "add", "remove" ) ) ) {
			$this->_setError( "Error RegisterMyNoxon class: action must be 'add' or 'remove'" );
			return false;
		}

		// Create a new Instance of Auth with Blowfish Keys for Ampache
		$myHash = new UDSAuth($this->inStrBFishKey, $this->inStrBFishIV, $this->inStrToken);
		// Encrypt username and password
		$encryptedHash = $myHash->encryptHash($this->user, UDSAuth::createPasswordHash($this->pass, $this->user) );

		//SX: Retrieve the Token
		$strGETToken = "?token=".NoxonConfig::getToken();

		if (! $this->strAmpacheServerLANURL) {
			$this->strAmpacheServerLANURL	= $this->strAmpacheServerURL;
		}

		$request = $this->strRegistrationURL."?action=".$strAction
											."&AuthHash=".$myHash->hex2string($encryptedHash)
											."&destinationURL=".urlencode($this->strAmpacheServerURL.$strGETToken)
											."&destinationLANURL=".urlencode($this->strAmpacheServerLANURL.$strGETToken)
											."&displayname=".urlencode($this->serverCaption)
											."&family=".urlencode($this->strApplication);
											
		echo 'Request URL: ' . $request;



		// URL corrects ?
		$xmlData			= @file_get_contents($request);
		
		if ($xmlData === false) {
			$this->_setError( "Error RegisterMyNoxon class: URL not corrects" );
			return false;
		}

		// XML Data ?
		$xml 				= @simplexml_load_string($xmlData);
		if ($xml === false) {
			$this->_setError( "Error RegisterMyNoxon class: Response is not XML" );
			return false;
		}

		// valid request ?
		if (strval($xml->ActionResult) != 'true') {
			$this->_setError( "Error RegisterMyNoxon class: Invalid request : '".strval($xml->ErrorCode)."'" );
			return false;
		}

		return true;
	}

}

?>