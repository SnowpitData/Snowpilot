<?php


	define('DRUPAL_ROOT', '/var/www/html/linode1.snowpilot.org/public_html');
	require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
	drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
	global $user;
	
	if ( user_is_logged_in() ){
		$user_msg = "logged in as ".$user->name;
	}else{
		$user_msg = "not logged in. <a href='https://snowpilot.org/user/login/?destination=/realtime-map.php'>Login</a> to use the realtime tool.";

	}
	
	include ( 'realtime-tools/SnowPitOnMap.php');

?>