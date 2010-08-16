<?php

require 'init.php';

$dom = new DOMDocument('1.0', 'iso-8859-1');
$stationDb = $dom->createElement('station_db');

$databaseInfo = $dom->createElement('database_info');
$databaseInfo->appendChild($dom->createElement('format', $config['rsdb']['format_version']));
$databaseInfo->appendChild($dom->createElement('name', $config['rsdb']['name']));
$databaseInfo->appendChild($dom->createElement('server_url', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
$databaseInfo->appendChild($dom->createElement('service', $config['rsdb']['service']));

$stationList = new StationList($dom);

$stationDb->appendChild($databaseInfo);
$stationDb->appendChild($stationList->getNode());


$result = mysql_query('SELECT child.id, child.node_id, COUNT(*)-1 AS level FROM tree AS child, tree AS parent WHERE child.lft BETWEEN parent.lft AND parent.rgt GROUP BY child.lft ORDER BY child.lft', $site['db']['connection']);

$directoryList = $dom->createElement('directory_list');


$dom->appendChild($stationDb);

echo $dom->saveXML();

class Station {
	public $dom;
	public $id = 0;
	public $name;
	public $description;
	public $bitrate;
	public $url;
	public $mime_type;
	
	function Station($dom, $id, $name, $description, $bitrate, $url, $mime_type) {
		$this->dom = $dom;
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->bitrate = $bitrate;
		$this->url = $url;
		$this->mime_type = $mime_type;
	}
	
	function getNode() {
		$station = $this->dom->createElement('station');
		$station->appendChild($this->dom->createElement('id', $this->id));
		$station->appendChild($this->dom->createElement('station_name', $this->name));
		$station->appendChild($this->dom->createElement('description', $this->description));
		$station->appendChild($this->dom->createElement('bw', $this->bitrate));
		$station->appendChild($this->dom->createElement('url', $this->url));
		$station->appendChild($this->dom->createElement('mime_type', $this->mime_type));
		
		return $station;
	}
}

class StationList {
	public $stations = array();
	private $dom;
	
	function StationList($dom) {
		$this->dom = $dom;
	}
	
	function getStations() {
		global $config;
		global $site;
		
		$stations = array();
		
		$result = mysql_query('SELECT * FROM ' . $config['db']['tables']['nodes'] . ' WHERE type = \'station\'', $site['db']['connection']);
		
		while ($row = mysql_fetch_assoc($result)) {
			array_push($stations, new Station($this->dom, $row['id'], $row['name'], $row['description'], $row['bitrate'], $row['url'], $row['mime_type']));
		}
		return $stations;
	}
	
	function getNode() {
		$stationList = $this->dom->createElement('station_list');
		
		foreach ($this->getStations() as $station) {
			$stationList->appendChild($station->getNode());
		}
		
		return $stationList;
	}
	
}
?>