<?php

require 'init.php';

if($_POST) {
	mysql_query('UPDATE nodes SET
					name = \'' . $_POST['name'] . '\',
					type = \'' . $_POST['type'] . '\',
					description = \'' . $_POST['description'] . '\',
					bitrate = ' . (int) $_POST['bitrate'] . ',
					url = \'' . $_POST['url'] . '\',
					mime_type = \'' . $_POST['mime_type'] . '\',
					location = \'' . $_POST['location'] . '\',
					bookmark = \'' . $_POST['bookmark'] . '\'
				WHERE id = ' . (int) $_GET['id'], $site['db']['connection']);
	echo mysql_error();
	echo 'Node edited successfully!<br />
					<a href="' . $_SERVER['PHP_SELF'] . '">back</a>';
}
else {
	$result = mysql_query('SELECT * FROM nodes WHERE id = ' . (int) $_GET['id'], $site['db']['connection']);
	$row = mysql_fetch_assoc($result);
		
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?cmd=edit&id=' . (int) $_GET['id'] . '">
			<table>
				<tr><td>Type</td><td><select size="1" name="type">
										<option ' . (($row['type'] == 'directory') ? 'selected="selected" ' : '') . 'value="directory">Directory</option>
										<option ' . (($row['type'] == 'station') ? 'selected="selected" ' : '') . 'value="station">Station</option>
										<option ' . (($row['type'] == 'display') ? 'selected="selected" ' : '') . 'value="display">Display text</option>
									</select></td></tr>
				<tr><td>Name</td><td><input type="text" name="name" value="' . $row['name'] . '" /></td></tr>
				<tr><td>Description</td><td><input type="text" name="description" value="' . $row['description'] . '" /></td></tr>
				<tr><td>Bitrate</td><td><input type="text" name="bitrate" value="' . $row['bitrate'] . '" /></td></tr>
				<tr><td>URL</td><td><input type="text" name="url" value="' . $row['url'] . '" /></td></tr>
				<tr><td>Mime Type</td><td><select size="1" name="mime_type">
											<option ' . (($row['mime_type'] == 'm3u') ? 'selected="selected" ' : '') . 'value="mp3">MP3</option>
											<option ' . (($row['mime_type'] == 'wma') ? 'selected="selected" ' : '') . 'value="wma"> Windows Media Audio</option>
										</select></td></tr>
				<tr><td>Location</td><td><input type="text" name="location" value="' . $row['location'] . '" /></td></tr>
				<tr><td>Bookmark</td><td><input type="text" name="bookmark" value="' . $row['bookmark'] . '" /></td></tr>
			</table>
			<input type="submit" value="Edit" />
		</form>';
}

?>