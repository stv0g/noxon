<?php

require 'init.php';

if ($_POST) {
	$result = mysql_query('SELECT lft, rgt FROM tree WHERE id = ' . (int) $_GET['after'] . ' LIMIT 1', $site['db']['connection']);
	$row = mysql_fetch_assoc($result);
	mysql_query('UPDATE tree SET rgt=rgt+2 WHERE rgt >= ' . $row['rgt'] , $site['db']['connection']);
	mysql_query('UPDATE tree SET lft=lft+2 WHERE lft > ' . $row['rgt'] , $site['db']['connection']);
	mysql_query('INSERT INTO  nodes SET
					name = \'' . $_POST['name'] . '\',
					type = \'' . $_POST['type'] . '\',
					description = \'' . $_POST['description'] . '\',
					bitrate = ' . (int) $_POST['bitrate'] . ',
					url = \'' . $_POST['url'] . '\',
					mime_type = \'' . $_POST['mime_type'] . '\',
					location = \'' . $_POST['location'] . '\',
					bookmark = \'' . $_POST['bookmark'] . '\'', $site['db']['connection']);
	mysql_query('INSERT INTO tree (node_id, lft, rgt) VALUES (' . mysql_insert_id() . ', ' . $row['rgt'] . ', ' . $row['rgt'] . ' + 1)' , $site['db']['connection']);
	echo 'Node added successfully!<br />
					<a href="javascript:history.go(-2)">back</a>';
}
else {
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?after=' . (int) $_GET['after'] . '">
			<table>
				<tr><td>Type</td><td><select size="1" name="type">
										<option value="directory">Directory</option>
										<option value="station" >Station</option>
										<option value="display" >Display text</option>
									</select></td></tr>
				<tr><td>Name</td><td><input type="text" name="name" /></td></tr>
				<tr><td>Description</td><td><input type="text" name="description" /></td></tr>
				<tr><td>Bitrate</td><td><input type="text" name="bitrate" /></td></tr>
				<tr><td>URL</td><td><input type="text" name="url" value="http://" /></td></tr>
				<tr><td>Mime Type</td><td><select size="1" name="mime_type">
											<option value="m3u">MP3</option>
											<option value="wma"> Windows Media Audio</option>
										</select></td></tr>
				<tr><td>Location</td><td><input type="text" name="location" /></td></tr>
				<tr><td>Bookmark</td><td><input type="text" name="bookmark" /></td></tr>
			</table>
			<input type="submit" value="Add" />
		</form>';
}
?>