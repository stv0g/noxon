<?php

require 'init.php';

if ($_GET['id'] == 'all') {
	mysql_query('TRUNCATE tree', $site['db']['connection']);
	mysql_query('TRUNCATE nodes', $site['db']['connection']);
	echo 'Everything has been deleted!<br />';
}
else {
	$result = mysql_query('SELECT lft, rgt, node_id FROM tree WHERE id = ' . (int) $_GET['id'] . ' LIMIT 1', $site['db']['connection']);
	$row = mysql_fetch_assoc($result);
		
	if (mysql_num_rows($result) < 2)
		mysql_query('DELETE FROM nodes WHERE id = ' . (int) $row['node_id'], $site['db']['connection']);
	mysql_query('DELETE FROM tree WHERE lft BETWEEN ' . $row['lft'] . ' AND ' . $row['rgt'], $site['db']['connection']);
	mysql_query('UPDATE tree SET lft=lft-ROUND((' . $row['rgt'] . ' - ' . $row['lft'] . ' + 1)) WHERE lft > ' . $row['rgt'], $site['db']['connection']);
	mysql_query('UPDATE tree SET rgt=rgt-ROUND((' . $row['rgt'] . ' - ' . $row['lft'] . ' + 1)) WHERE rgt > ' . $row['rgt'], $site['db']['connection']);
		
	echo 'Node successfully deleted!<br />';
}

echo '<a href="javascript:history.back()">back</a>';

?>