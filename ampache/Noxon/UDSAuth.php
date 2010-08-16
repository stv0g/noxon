<?php

/**
 * Crypto Backend for UDS Authentication
 * Shared Client/Server Library, Keys are set during instanciation.
 *
 * @author Manfred Dreese / TTEG
 */
class UDSAuth {
	private $strBFishKey = "";
	private $strBFishIV = "";
	private $strToken = "";
	private $blValidToken = false;

	/**
	 * Hex to String conversion
	 */
	function hex2string($str)
	{
	  if (trim($str)!="")
	  {
		$hex="";
		$length=strlen($str);
		for ($i=0; $i<$length; $i++)
		{
		  $hex.=str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT);
		}
		return $hex;
	  }
	}

	/**
	 * String to Hex Conversion
	 * ( "FF" -> \0xf \0xf
	 */
	function string2hex($str) {
		$ret="";
		for($i=0;$i<strlen($str);$i+=2) {
			$h=chr(hexdec(substr($str,$i,2)));
			$ret.=$h;
		}
		return $ret;
	}

	/**
	 * Data destruction function for password.
	 *
	 * This is supposed for open-source CPAs or any other CPA where the
	 * Blowfish keys are stored in cleartext. This function creates a
	 * hash from the password which is not reversible to the original data.
	 *
	 * The handler will try to compare the hashes of the passwords when
	 * cleartext comparism failed.
	 *
	 * To provide a safe encryption of short passwords without
	 * having issues with wordbook/rainbowtable attacks,
	 * a dual-md5 is used.
	 */
	static function createPasswordHash ($inStrPassword, $inStrUserName="") {
		return md5(md5($inStrPassword)+$inStrUserName);
	}

	/**
	 * Constructor
	 * Is usually called from the Handler with Keys loaded
	 * from the database according to the CPA.
	 */
	function UDSAuth ( $inStrBFishKey, $inStrBFishIV, $inStrToken) {
		$this->strBFishKey = $this->string2hex($inStrBFishKey);
		$this->strBFishIV = $this->string2hex($inStrBFishIV);
		$this->strToken = $inStrToken;
	} // construct

	function isValidToken() {
		return $this->blValidToken;
	}

	function encryptHash ($inStrUser, $inStrPassword, $inBlDestroyPassword = false ) {
		$strUnencryptedHash = $inStrUser
		.chr(10).chr(13).$inStrPassword
		.chr(10).chr(13).$this->strToken
		.chr(10).chr(13);

		$strEncHash = mcrypt_cbc(MCRYPT_BLOWFISH,$this->strBFishKey, $strUnencryptedHash,MCRYPT_ENCRYPT,$this->strBFishIV);
		return $strEncHash;
	}

} // class UDSAuth
?>