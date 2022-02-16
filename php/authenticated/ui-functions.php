<?php

function uiHeader($title) {?>
<html>
<head>
  <title><?= $title;?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
<div class="w-50 p-3 mx-auto" style="background-color: #eee;">
<?php
}

function footer() {?>
</div>
</body>
</html>
<?php
}

function dropdownPermissionLevel($row = '') {
	if ($row !== '') {
		$selected = $row['permission_level'];
		$name = 'edit['.$row['sid'].'][permission_level]';
	} else {
		$selected = '';
		$name = 'permission_level';
	}?>
	<div class="form-group">
		<label for="permission_level" class="form-label">Permission level:</label>
		<select id="permission_level" class="form-control" name='<?= $name;?>'>
			<option value='private' <?= $selected === 'private' ? 'selected=\'true\'' : '';?>>private</option>
			<option value='domain' <?= $selected === 'domain' ? 'selected=\'true\'' : '';?>>domain</option>
			<option value='public' <?= $selected === 'public' ? 'selected=\'true\'' : '';?>>public</option>
		</select>
	</div>
<?php
}

function formAddSuperlink($query_parts) {
	uiHeader('Add Superlink');?>
	<h3>Add Superlink - <?php echo $query_parts['superlink'];?></h3>
	<form action='<?= 'https://'.$_SERVER['SERVER_NAME'].'/add/'.$query_parts['superlink'];?>' method='post'>
		<div class="mb-3">
			<label for="target" class="form-label">Target:</label>
			<input id="target" aria-describedby="targetHelp" class="form-control" type='text' name='target'>
			<div id="targetHelp" class="form-text">Traget url which people will be redirected to.</div>
		</div>
		<div class="mb-3">
			<label for="comment" class="form-label">Comment:</label>
			<input id="comment" class="form-control" type='text' name='comment'/>
		</div>
<?php	dropdownPermissionLevel();?>
<br />
<div class="text-center">
			<input class="btn btn-primary" type='submit' value='Add' />
		</div>
	</form>
<?php
	footer();
}

function formEditSuperlinks($query_parts, $existingSuperlinks) {
	if(is_numeric($query_parts['superlink']) && $query_parts['superlink'] !== $existingSuperlinks[0]['superlink']) {
		$sl = $existingSuperlinks[0]['superlink'];
	} else {
		$sl = $query_parts['superlink'];
	}
	uiHeader('Edit Superlinks for - '.$sl);?>
	<h3>Edit Superlinks - <?= $sl;?></h3>
	<form action='<?= 'https://'.$_SERVER['SERVER_NAME'].'/edit/'.$sl;?>' method='post'>
<?php
	foreach ($existingSuperlinks as $row) {?>
		<input type='hidden' name='edit[<?=$row['sid'];?>][sid]' value='<?= $row['sid'];?>' />
		<div class="mb-3">
			<label for="target" class="form-label">Target:</label>
			<input id="target" aria-describedby="targetHelp" class="form-control" type='text' name='edit[<?=$row['sid'];?>][target]' value='<?= $row['target'];?>'/>
			<div id="targetHelp" class="form-text">Traget url which people will be redirected to.</div>
		</div>
		<div class="mb-3">
			<label for="comment" class="form-label">Comment:</label>
			<input id="comment" class="form-control" type='text' name='edit[<?=$row['sid'];?>][comment]' value='<?= $row['comment'];?>'/>
		</div>
<?php	dropdownPermissionLevel($row);?>
		<br />
<?php }?>
		<div class="text-center">
			<input class="btn btn-primary" type='submit' value='Save Changes' />
		</div>
	</form>
<?php
	footer();
}

function multipleAvailableSuperlinks($availableSuperlinks, $query_parts, $user_info) {
	uiHeader('Multiple Available Superlinks for - '.$query_parts['superlink']);
?>
	<p> The system has detected multiple options for you to chose from: </p>
	<ul>
<?php 
	foreach ($availableSuperlinks as $row) {?>
		<li>
			<a href='<?= $row['target'];?>'><?= $row['comment'] == '' ? $row['target'] : $row['comment'];?></a> owned by: <?= $row['user'].'@'.$row['domain'];?>
<?php
	if($row['user'] === $user_info['username'] && $row['domain'] === $user_info['domain']) {?>
		<a href='<?= 'https://'.$_SERVER['SERVER_NAME'].'/editlink/'.$row['sid'];?>'>Edit</a>
		<a href='<?= 'https://'.$_SERVER['SERVER_NAME'].'/delete/'.$row['sid'];?>'>Delete</a>
<?php
	}?>
		</li>
<?php
	}
?>
	</ul>
</body>
</html>
<?php
}
?>