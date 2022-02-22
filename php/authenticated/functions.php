<?php
/*****
 * Split input string into parts based
 *
 * Inputs:
 * - string
 * - delimiting character
 *
 * Output:
 * - explode(delimiter, string)
 * - if empty string: array()
 *****/
function sl_explode($delimiter, $string) {
	if($string !== '' && $string !== NULL) {
		return explode($delimiter, $string);
	} else {
		return array();
	}
}

/*****
 * Function to split e-mail address into username and domain part.
 * Primarily meant for settings where the username is provided as an e-mail address.
 *
 * Please note this function assumes simple e-mail formats - user@domain
 * user+uniqid@domain will use user+uniqid as the username.
 *
 * Inputs:
 * - e-mail address
 *
 * Returns:
 * - array('username' => e-mail[0], 'domain' => email[1])
 *
 * Errors:
 * - Throws an error if the input string splits into more than 2 parts.
 *****/
function split_email($email) {
	$parts = sl_explode('@', $email);

	if(count($parts) == 2) {
		return array(	'username'	=> $parts[0],
				'domain'	=> $parts[1],
			   );
	} else {
		throw new Exception('Failed to parse e-mail address count: '.count($parts));
	}
}

function not_empty($var) {
	return !empty($var);
}

/*****
 * Function to process the query string.
 * Assumes formats:
 * - operation/superlink
 * - superlink
 *
 * Inputs:
 * - query string (_GET['q'])
 *
 * Returns:
 * - array('operation' => query[0], 'superlink' => query[1]
 * - array('superlink' => query)
 *
 * Errors:
 * - Throws an exception if count is out of supported range (1 or 2)
 *
 * Please note this function does *not* evaluate if 'operation' is a valid value.
 *****/
function split_query($query) {
	$parts = sl_explode('/', $query);

	array_filter($parts, "not_empty");

	switch (count($parts)) {
		case 1:
			return array('superlink' => $parts[0]);
		case 2:
			return array(	'operation' => $parts[0],
					'superlink' => $parts[1],
				   );
		default:
			throw new Exception('Query parts out of range[1-2], count: '.count($parts));
	}
}
/*****
 * Function for simple debugging of variables.
 *****/
function debug_var($var, $title = '') {
	if ($title !== '') {
		echo '<h3>'.$title.'</h3>';
	}
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}
?>
