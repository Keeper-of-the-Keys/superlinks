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

function dropdownPermissionLevel($row = '') {
	if ($row !== '') {
		$selected = $row['permission_level'];
		$name = 'edit['.$row['sid'].'][permission_level]';
	} else {
		$selected = '';
		$name = 'permission_level';
	}?>
		<label>
			Permission level:
			<select name='<?= $name;?>'>
				<option value='private' <?= $selected === 'private' ? 'selected=\'true\'' : '';?>>private</option>
				<option value='domain' <?= $selected === 'domain' ? 'selected=\'true\'' : '';?>>domain</option>
				<option value='public' <?= $selected === 'public' ? 'selected=\'true\'' : '';?>>public</option>
			</select>
		</label>
<?php
}

function formAddSuperlink($query_parts) {
	uiHeader('Add Superlink');?>
	<h1>Add Superlink - <?php echo $query_parts['superlink'];?></h1>
	<form action='<?= 'https://'.$_SERVER['SERVER_NAME'].'/add/'.$query_parts['superlink'];?>' method='post'>
		<label>
			Target: 
			<input type='text' name='target' />
		</label>
		<label>
			Comment:
			<input type='text' name='comment' />
		</label>
<?php	dropdownPermissionLevel();?>
		<input type='submit' value='Add' />
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
	<h1>Edit Superlinks - <?= $sl;?></h1>
	<form action='<?= 'https://'.$_SERVER['SERVER_NAME'].'/edit/'.$sl;?>' method='post'>
<?php
	foreach ($existingSuperlinks as $row) {?>
		<input type='hidden' name='edit[<?=$row['sid'];?>][sid]' value='<?= $row['sid'];?>' />
		<label>
			Target: 
			<input type='text' name='edit[<?=$row['sid'];?>][target]' value='<?= $row['target'];?>'/>
		</label>
		<label>
			Comment:
			<input type='text' name='edit[<?=$row['sid'];?>][comment]' value='<?= $row['comment'];?>'/>
		</label>
<?php	dropdownPermissionLevel($row);?>
		<br />
<?php }?>
		<input type='submit' value='Edit' />
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
