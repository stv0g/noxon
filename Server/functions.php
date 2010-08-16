<?php

function init_mysql() {
	global $site;
	global $config;
	
	$site['db']['connection'] = mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['pw']);
	mysql_select_db($config['db']['db'], $site['db']['connection']);
}

?>