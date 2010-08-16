<?php
/*
 * HTTP Service Receiver for Noxon Ampache Service
 *
 * @author Manfred Dreese / TTEG
 */
// Include
require_once("Noxon/NoxonUIHandler.php");

// Variables
/** Array to be passed to UIHandler */
$NoxonUIRequest = Array();

// Utility functions
/**
 * SX:Read a Value from the GET request
 * inStrGETIdx = Index of PHP GET
 * inArrTarget = Reference to target array (PGP CbR)
 */
function readGETData( $inStrGETIdx , &$inArrTarget ) {
	if ( isset($inArrTarget) ) {

		// Does the index exist ?
		if (isset($_GET[$inStrGETIdx])) {

			// Any fancy stuff ? In this case, drop the data
			if ( $_GET[$inStrGETIdx] == mysql_escape_string($_GET[$inStrGETIdx]) ) {
				$inArrTarget[$inStrGETIdx] = $_GET[$inStrGETIdx];
			}
		}
	} // fi Target exists?
	return null;
} // readGETData

/*
 * Create NoxonUIHandler Object
 */
/** Create Instance of NoxonUIHandler */
try {
	$myNoxon = new NoxonUIHandler();
}
catch (Exception $e) {
	die(NoxonUIHandler::getNoxonError("Connection to Ampache failed!"));
}

/*
 * HTTP Request Handler Code
 */

// Read base URL for this script. Please feel free to overwrite this value in case of an advanced VHost Configuration
// Luc : Changed from SCRIPT_URL to SCRIPT_NAME
$NoxonUIRequest["script.baseURL"] =  "http://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'];

// Read called Hostname of this server to remap Streaming URLs to this server when this script is run on the Ampache Server
$NoxonUIRequest["server.httpHostname"] = "http://".$_SERVER['HTTP_HOST'];

// Read Data from HTTP Request
$strExpectedGetData = Array ("action","filter","speller_filter","token","spellerCMass","aid","gid","token","dlang");

foreach ($strExpectedGetData as $expectedGet)  {
	readGETData ($expectedGet, $NoxonUIRequest);
}

echo ($myNoxon->processRequest($NoxonUIRequest));

?>