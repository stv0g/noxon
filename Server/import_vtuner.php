<?php

require 'config.php';

$site['db']['connection'] = mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['pw']);
mysql_select_db($config['db']['db'], $site['db']['connection']);

echo '<?xml version="1.0" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>VTuner.com Importer</title>
		<link rel="stylesheet" type="text/css" href="stylesheet.css">
	</head>
	<body>
		<h2>VTuner.com Importer</h2>';

//$rsdb_xml = file_get_contents($config['vtuner']['url'] . '?mac=' . $config['noxon']['mac'] . '&uid=' . $config['vtuner']['uid'] . '&lang=' . $config['vtuner']['lang']);
$rsdb_xml = file_get_contents('../full_rsdb.xml');

$rsdb_dom = new DOMDocument();
$rsdb_dom->preserveWhiteSpace = false;
$rsdb_dom->loadXML($rsdb_xml);

if ($_GET['cmd'] == 'import') {
	// Stations
	$stationList = $rsdb_dom->getElementsByTagName('station_list')->item(0);
	echo $stationList->getElementsByTagName('station')->length . ' Stationen';

	$counter = 0;
	$sql_pre = 'REPLACE INTO nodes (id, type, name, description, bitrate, url, mime_type) VALUES ' . "\n";

	foreach ($stationList->getElementsByTagName('station') as $station) {
	if($counter % 50 == 0) {
	$sql = substr($sql, 0, -3);
	mysql_query($sql, $site['db']['connection']);
	echo '.'; flush();
	$sql = $sql_pre;
	}
	$sql .= '(' . (int) $station->getElementsByTagName('id')->item(0)->nodeValue . ', \'station\', \'' . mysql_real_escape_string($station->getElementsByTagName('station_name')->item(0)->nodeValue) . '\', \'' . mysql_real_escape_string($station->getElementsByTagName('station_description')->item(0)->nodeValue) . '\', ' . (int) $station->getElementsByTagName('bw')->item(0)->nodeValue . ', \'' . mysql_real_escape_string($station->getElementsByTagName('url')->item(0)->nodeValue) . '\', \'' . mysql_real_escape_string($station->getElementsByTagName('mime_type')->item(0)->nodeValue) . '\'), ' . "\n";
	$counter++;
	}

	if(mysql_affected_rows() > 0) {
	echo '<p>' . $counter . ' stations successfully imported!</p>';
	}
	else {
	echo '<p>Sorry we had a problem during the importing process:<br />' . mysql_error() . '</p>';
	echo '<pre>' . $sql . '</pre>';
	}

	//Structure
	$dirList = $rsdb_dom->getElementsByTagName('directory_list')->item(0);

	$sql = 'REPLACE INTO tree (node_id, lft, rgt) VALUES ' . "\n";

	$curNode = $dirList;
	$counter = 0;
	$level = 0;
	$lft[$level] = 0;

	while($curNode) {
		switch ($curNode->nodeName) {
			case 'dir':
				mysql_query('REPLACE INTO nodes (type, name) VALUES (\'directory\', \'' . mysql_real_escape_string($curNode->attributes->getNamedItem('name')->nodeValue) . '\')');
				$id[$level] = mysql_insert_id();
					
				//echo 'found dir: ' . $curNode->getAttribute('name') . ' and added to nodes with id: ' . mysql_insert_id() . '<br />';
					
				break;
			case 'station':
				$id[$level] = (int) $curNode->nodeValue;
					
				//echo 'found station: ' . $curNode->nodeValue . '<br />';
					
				break;
			default:
				break;
		}

		if ($curNode->hasChildNodes() && strpos($curNode->nodeName, 'dir') !== false) {
			$curNode = $curNode->firstChild;
			$level++;
			$lft[$level] = $lft[$level - 1] + 1;
				
			//echo 'entering subtree (level: ' . $level . ')<br />';
		}
		else {
			$rgt[$level] = $lft[$level] + 1;
			$sql .= '(' . $id[$level] . ', ' . $lft[$level] . ', ' . $rgt[$level] . '), ' . "\n";
			echo 'next node (level: ' . $level . '): (' . $id[$level] . ', ' . $lft[$level] . ', ' . $rgt[$level] . ') <br />';
			$lft[$level] = $rgt[$level] + 1;
				
			if ($curNode->nextSibling) {
				$curNode = $curNode->nextSibling;
			}
			else {
				do {
					$level--;
					$rgt[$level] = $rgt[$level + 1] + 1;
					$sql .= '(' . $id[$level] . ', ' . $lft[$level] . ', ' . $rgt[$level] . '), ' . "\n";
					echo 'leave subtree (level: ' . $level . '): (' . $id[$level] . ', ' . $lft[$level] . ', ' . $rgt[$level] . ') <br />';
					$lft[$level] = $rgt[$level] + 1;
					
					if ($level == 1) break 2;

					$curNode = $curNode->parentNode;
				} while (!$curNode->nextSibling);

				$curNode = $curNode->nextSibling;
			}
		}


		/*if($counter % 50 == 0 && $sql != '') {
			$sql = substr($sql, 0, -3);
			mysql_query($sql, $site['db']['connection']);
			echo '.'; flush();
			$sql = $sql_pre;
			echo mysql_error();
		}*/
		$counter++;
	}

	$sql = substr($sql, 0, -3);
	mysql_query($sql, $site['db']['connection']);

	if(mysql_affected_rows() > 0) {
		echo '<p>' . $counter . ' nodes successfully imported!</p>';
	}
	else {
		echo '<p>Sorry we had a problem during the importing process:<br />' . mysql_error() . '</p>';
	}
	
	echo '<pre>' . $sql . '</pre>';

}
else {
	echo '<h4> Database Info</h4>
		<table>
			<tr><td>Version</td><td>' . $rsdb_dom->firstChild->attributes->getNamedItem('version')->nodeValue . '</td></tr>
			<tr><td>Stationen</td><td>' . $rsdb_dom->firstChild->attributes->getNamedItem('station_count')->nodeValue . '</td></tr>
			<tr><td>Format Version</td><td>' . $rsdb_dom->firstChild->attributes->getNamedItem('format_version')->nodeValue . '</td></tr>
			<tr><td>Server URL</td><td>' . $rsdb_dom->getElementsByTagName('database_info')->item(0)->getElementsByTagName('server_url')->item(0)->nodeValue . '</td></tr>
			<tr><td>Name</td><td>' . $rsdb_dom->getElementsByTagName('database_info')->item(0)->getElementsByTagName('name')->item(0)->nodeValue . '</td></tr>
			<tr><td>Service</td><td>' . $rsdb_dom->getElementsByTagName('database_info')->item(0)->getElementsByTagName('service')->item(0)->nodeValue . '</td></tr>
		</table>
		<p>Do you really want to import ' . $rsdb_dom->firstChild->attributes->getNamedItem('station_count')->nodeValue . ' Station to your DB?<br/>
		<a href="' . $_SERVER['PHP_SELF'] . '?cmd=import">YES</a></p>';
}

echo '</body>
</html>';

?>