<?php

function uiHeader($title) {?>
<html>
<head><title><?= $title;?></title></head>
<body>
<?php
}

function footer() {?>
</body>
</html>
<?php
}

function multipleAvailableSuperlinks($availableSuperlinks, $superlink) {
	uiHeader('Multiple Available Superlinks for - '.$superlink);
?>
	<p> The system has detected multiple options for you to chose from: </p>
	<ul>
<?php 
	foreach ($availableSuperlinks as $row) {?>
		<li>
			<a href='<?= $row['target'];?>'><?= $row['comment'] == '' ? $row['target'] : $row['comment'];?></a>
		</li>
<?php
	}
?>
	</ul>
<?php footer();
}

function messageNoSuperLinkRequested() {
	http_response_code(404);
	uiHeader('Error - no superlink requested');
	echo('Error - no superlink requested');
	footer();
}

function messageNoSuperLinkFound($superlink) {
	http_response_code(404);
	uiHeader('Error - no superlink found');
	echo('Error - no superlink found for:' . $superlink);
	echo('<pre>');
	var_dump($_GET);
	echo('</pre>');
	footer();
}

?>
s/([[:space:]]+)(0-9)+([[:space:]]+;[[:space::]]+Serial.*$)/echo "\1$((\2+1))\3"/ge
