<?php
include_once('functions.php');

if(isset($_GET['q']) && $_GET['q'] !== '' && $_GET['q'] !== NULL) {
	try {
		$query_parts = split_query($_GET['q']);
	} catch (Exception $e) {
		die('Caught exception: '.$e->getMessage()."\n");
	}
}

/*****
 * The user code assumes Apache authentication (in my case - mod_auth_mellon using SAML) at this time,
 * it is possible to make this more modular in the future.
 *****/
if(isset($_SERVER['REMOTE_USER']) && $_SERVER['REMOTE_USER'] !== '' && $_SERVER['REMOTE_USER'] !== NULL) {
	try {
		$user_info = split_email($_SERVER['REMOTE_USER']);
	} catch (Exception $e) {
		die('Caught exception: '.$e->getMessage()."\n");
	}
}

// Stop execution if data is missing
if($user_info['username'] == '') {
	die('No username - can\'t continue.');
}
if($user_info['domain'] == '') {
	die('No domain - can\'t continue.');
}

global $config;
include_once('config.php');
include_once('ui-functions.php');

try {
	$dbh = new PDO($config['dsn'], $config['dbUser'], $config['dbPass'], array(PDO::ATTR_PERSISTENT => true));
	$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	return 'Connection failed: ' . $e->getMessage();
}

if (isset($query_parts['superlink']) && !isset($query_parts['operation'])) {
	$findAvailableSuperlinks = $dbh->prepare('select `sid`, `target`, `user`, `domain`, `comment` from `superlinks` where binary `superlink` = :superlink and '.
									'( (`user` = :username and `domain` = :domain) or '.
									'(`domain` = :domain and `permission_level` = \'domain\') or '.
									'`permission_level` = \'public\')');

	$findAvailableSuperlinks->bindValue(':superlink', $query_parts['superlink'], PDO::PARAM_STR);
	$findAvailableSuperlinks->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
	$findAvailableSuperlinks->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);

	$findAvailableSuperlinks->execute();

	$availableSuperlinks = $findAvailableSuperlinks->fetchAll();

	switch (count($availableSuperlinks)) {
		case 0:
			formAddSuperlink($query_parts);
			break;
		case 1:
			header('Location: '.$availableSuperlinks[0]['target']);
		default:
			multipleAvailableSuperlinks($availableSuperlinks, $query_parts, $user_info);
			break;
	}
} else if (isset($query_parts['superlink']) && $query_parts['operation'] === 'add') {
	if (isset($_POST['target']) && isset($_POST['comment']) && isset($_POST['permission_level'])) {
		if (!preg_match('/^http(s|):\/\//', $_POST['target'])) {
			$_POST['target'] = 'https://'.$_POST['target'];
		}

		$addSuperlink = $dbh->prepare('insert into `superlinks` set `superlink` = :superlink, `target` = :target, '.
										'`user` = :username, `domain` = :domain, `permission_level` = :permission_level, `comment` = :comment');
		$addSuperlink->bindValue(':superlink', $query_parts['superlink'], PDO::PARAM_STR);
		$addSuperlink->bindValue(':target', $_POST['target'], PDO::PARAM_STR);
		$addSuperlink->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
		$addSuperlink->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);
		$addSuperlink->bindValue(':permission_level', $_POST['permission_level'], PDO::PARAM_STR);
		$addSuperlink->bindValue(':comment', $_POST['comment'], PDO::PARAM_STR);
		$addSuperlink->execute();

		header('Location: https://'.$_SERVER['SERVER_NAME'].'/'.$query_parts['superlink']);
	} else {
		$checkSuperlinkExistance = $dbh->prepare('select count(*) as \'cnt\' from `superlinks` where binary `superlink` = :superlink and `user` = :username and `domain` = :domain');
		$checkSuperlinkExistance->bindValue(':superlink', $query_parts['superlink'], PDO::PARAM_STR);
		$checkSuperlinkExistance->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
		$checkSuperlinkExistance->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);
		$checkSuperlinkExistance->execute();

		$existingSuperlinks = $checkSuperlinkExistance->fetchAll();

		switch ($existingSuperlinks[0]['cnt']) {
			case 0:
				formAddSuperlink($query_parts);
				break;
			default:
				//Allow override (since the database supports it) but also signify this is not offical behavior and not part of the API
				if (isset($_GET['force'])) {
					formAddSuperlink($query_parts);
				} else {
					header('Location: https://'.$_SERVER['SERVER_NAME'].'/edit/'.$query_parts['superlink']);
				}
				break;
		}
	}
} else if (isset($query_parts['superlink']) && $query_parts['operation'] === 'edit') {
	if(isset($_POST) && !empty($_POST['edit']) ) {
		$updateSuperlink = $dbh->prepare(	'update `superlinks` set `target` = :target, '.
							'`permission_level` = :permission_level, `comment` = :comment where `sid` = :sid and `user` = :username and `domain` = :domain');
		foreach($_POST['edit'] as $row) {
			$updateSuperlink->bindValue(':target', $row['target'], PDO::PARAM_STR);
			$updateSuperlink->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
			$updateSuperlink->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);
			$updateSuperlink->bindValue(':permission_level', $row['permission_level'], PDO::PARAM_STR);
			$updateSuperlink->bindValue(':comment', $row['comment'], PDO::PARAM_STR);
			$updateSuperlink->bindValue(':sid', $row['sid'], PDO::PARAM_INT);

			$updateSuperlink->execute();
		}
		header('Location: https://'.$_SERVER['SERVER_NAME'].'/'.$query_parts['superlink']);
	} else {
		$getUserSuperlinks = $dbh->prepare('select * from `superlinks` where binary `superlink` = :superlink and `user` = :username and `domain` = :domain');
		$getUserSuperlinks->bindValue(':superlink', $query_parts['superlink'], PDO::PARAM_STR);
		$getUserSuperlinks->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
		$getUserSuperlinks->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);
		$getUserSuperlinks->execute();

		$existingSuperlinks = $getUserSuperlinks->fetchAll();

		formEditSuperlinks($query_parts, $existingSuperlinks);
	}
} else if (isset($query_parts['superlink']) && $query_parts['operation'] === 'editlink') {
	if(is_numeric($query_parts['superlink'])) {
		$getSuperlink = $dbh->prepare('select * from `superlinks` where `sid` = :sid and `user` = :username and `domain` = :domain');
		$getSuperlink->bindValue(':sid', $query_parts['superlink'], PDO::PARAM_INT);
		$getSuperlink->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
		$getSuperlink->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);
		$getSuperlink->execute();

		$existingSuperlinks = $getSuperlink->fetchAll();

		if (count($existingSuperlinks) === 1) {
			formEditSuperlinks($query_parts, $existingSuperlinks);
		} else {
			die('No link suitable to edit found.');
		}
	} else {
		die('wrong input');
	}
} else if (isset($query_parts['superlink']) && $query_parts['operation'] === 'delete') {
	if(is_numeric($query_parts['superlink'])) {
		$deleteSuperlink = $dbh->prepare('delete from `superlinks` where `sid` = :sid and `user` = :username and `domain` = :domain');
		$deleteSuperlink->bindValue(':sid', $query_parts['superlink'], PDO::PARAM_INT);
		$deleteSuperlink->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
		$deleteSuperlink->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);
		$deleteSuperlink->execute();

		if ($deleteSuperlink->rowCount() === 1) {
			die('Superlink has been deleted.');
		} else if ($deleteSuperlink->rowCount() > 1) {
			die('Something may have gone wrong please contact the system administrator.');
		} else {
			die('No link suitable to delete found.');
		}
	} else {
		die('wrong input');
	}
} else if ($query_parts['superlink'] == 'own' && $query_parts['operation'] == 'list') {
	$listSuperlinks = $dbh->prepare('select `sid`, `target`, `user`, `domain`, `comment` from `superlinks` where '.
									'(`user` = :username and `domain` = :domain)');

	$listSuperlinks->bindValue(':username', $user_info['username'], PDO::PARAM_STR);
	$listSuperlinks->bindValue(':domain', $user_info['domain'], PDO::PARAM_STR);

	$listSuperlinks->execute();

	$availableSuperlinks = $listSuperlinks->fetchAll();

	switch (count($availableSuperlinks)) {
		case 0:
			echo 'You have no superlinks :(';
			break;
		default:
			multipleAvailableSuperlinks($availableSuperlinks, $query_parts, $user_info);
			break;
	}
}


?>
