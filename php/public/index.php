<?php
include_once('ui-functions.php');

if(isset($_GET['q']) && $_GET['q'] !== '' && $_GET['q'] !== NULL) {
	$superlink = $_GET['q'];
} else {
	messageNoSuperLinkRequested();
}

global $config;
include_once('config.php');

try {
	$dbh = new PDO($config['dsn'], $config['dbUser'], $config['dbPass'], array(PDO::ATTR_PERSISTENT => true));
	$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	return 'Connection failed: ' . $e->getMessage();
}

if (isset($superlink)) {
	$findAvailableSuperlinks = $dbh->prepare('select `target`, `comment` from `view_public` where binary `superlink` = :superlink');
	$findAvailableSuperlinks->bindValue(':superlink', $superlink, PDO::PARAM_STR);
	$findAvailableSuperlinks->execute();

	$availableSuperlinks = $findAvailableSuperlinks->fetchAll();

	switch (count($availableSuperlinks)) {
		case 0:
			messageNoSuperLinkFound($superlink);
			break;
		case 1:
			header('Location: '.$availableSuperlinks[0]['target']);
		default:
			multipleAvailableSuperlinks($availableSuperlinks, $superlink);
			break;
	}
}
?>
