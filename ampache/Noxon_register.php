<?php

require_once 'Noxon/NoxonConfig.php';

/**
 * Noxon Config File : Read config into NoxonConfig.php and use the RegisterMyNoxon.php class
 */
require_once 'Noxon/RegisterMyNoxon.php';
$noxon	= new RegisterMyNoxon(	NoxonConfig::$myNoxonUser,
NoxonConfig::$myNoxonPass,
NoxonConfig::$XMLUrl,
NoxonConfig::$XMLLANUrl,
NoxonConfig::$serverCaption
);


if ($noxon->register()) {
	echo "Registration Successful";
}
else {
	echo "Registration Failed :<br>";
	echo $noxon->getError();
}

?>