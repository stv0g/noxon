<?php

/**
 * Config Class for Noxon Ampache Installation
 * Please configure the behaviour of your Ampache NOXON Plugin here
 *
 * @author Manfred Dreese / TTEG
 */
class NoxonConfig {
	/******************************************************
	 * Basic Configuration
	 ******************************************************/
	/**
	 * The token protects your server against guessed URLs.
	 * Please enter up to 256 here.
	 * 
	 * Example :
	 * static $token	= "My ampache is my Ampache";
	 */
	static $token	= "secret token";
	
	/**
	 * www.my-noxon.com User
	 */
	static $myNoxonUser = "user@example.com";

	/**
	 * www.my-noxon.com Password
	 */
	static $myNoxonPass = "userpw";

	/**
	 * Service Caption on the Noxon
	 */
	static $serverCaption = "Test";

	/**
	 * URL for the XML Server
	 * 
	 * $XMLUrl		= URL of your Ampache server on the internet
	 * 					i.E. http://lalala.dyndns.org/ampache/....Noxon_xml.server.php
	 * 
	 * $XMLLANUrl	= URL of your Ampache server on your LAN (optional)
	 * 					i.E. http://192.168.241.78/ampache/....Noxon_xml.server.php
	 */
	static $XMLUrl		= "http://192.168.1.31/workspace/Noxon/xml_server.php";
	static $XMLLANUrl	= "http://192.168.1.31/workspace/Noxon/xml_server.php";
	
	// End of user configurable section
	
	
	/******************************************************
	 * Functions
	 ******************************************************
	 * Please do not touch anything below this line.
	 */

	/**
	 * Returns the Token, mangled with some Serverdata to provide a basic
	 * security solution
	 * SHR13 the Unix timestamp to make the token change every 8192 sec
	 *
	 * @param $data : String : Some data to modify the result
	 */
	public static function getToken($data="") {
		return md5($data.NoxonConfig::$token.$_SERVER["DOCUMENT_ROOT"] );
	}

}

?>