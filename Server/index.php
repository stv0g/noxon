<?php

require 'init.php';

echo '<?xml version="1.0" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Noxon Remote Radio Database Manager 1.0</title>
		<link rel="stylesheet" type="text/css" href="stylesheet.css">
	</head>
	<body>
		<h2>Noxon Remote Radio Database Manager</h2>';

$result = mysql_query('SELECT * FROM (SELECT child.id, child.node_id, COUNT(*)-1 AS level, ROUND((child.rgt - child.lft - 1) / 2) AS offspring FROM tree AS child, tree AS parent WHERE child.lft BETWEEN parent.lft AND parent.rgt GROUP BY child.lft ORDER BY child.lft) tmptree INNER JOIN nodes ON nodes.id = tmptree.node_id', $site['db']['connection']);
//$result = mysql_query('SELECT * FROM (SELECT id, node_id FROM tree WHERE rgt = lft + 1 ORDER BY lft) tmptree LEFT JOIN nodes ON nodes.id = tmptree.node_id', $site['db']['connection']);



echo '<table>
				<tr class="tree_table_head"><td>Name</td><td>Level</td><td>Substations</td><td>Actions</td></tr>';
while ($row = mysql_fetch_assoc($result)) {
	echo '<tr><td>';
	for ($i = 0; $i < $row['level'] + 1; $i++)
	echo '&nbsp;&nbsp;';

	echo '<img src="' . $row['type'] . '.png" />&nbsp;' . $row['name'] . '</td><td>' . $row['level'] . '</td><td>' . $row['offspring'] . '</td><td style="text-align: right;"l>' . (($row['type'] == 'station') ? '<a href="' . $row['url'] . '"><img class="button" alt="play" src="control_play.png" /></a>' : '') . '<a href="del.php?id=' . $row['id'] . '"><img class="button" alt="delete" src="delete.png" /></a><a href="edit.php?id=' . $row['node_id'] . '"><img class="button" alt="edit" src="edit.png" /></a></td>';
		
	echo '<tr class="add_space" onclick="window.location = \'add.php?after=' . $row['id'] . '\';"><td colspan="4"></td></tr>' ;
}
echo '</table>';

echo '<a href="del.php?id=all">delete all</a>';

echo '</body>
</html>'

?>