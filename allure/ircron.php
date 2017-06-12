<?php

$module = null;
$function = null;

if ( strcmp(php_sapi_name(),'cli') == 0 ) {
	$parameters = array(
			//'n' => 'noparam',
			'm:' => 'mod:',
			'f:' => 'func:',
	);
	
	$args = getopt(implode('', array_keys($parameters)), $parameters);

	//print_r($args);
	$module = $args['m'];
	$function = $args['f'];
} else {
	$module = $_REQUEST['mod'];
	$function = $_REQUEST['func'];
}

if ($module && $function) {
	require_once 'app/Mage.php';
	Mage::app();
	try {
		$model = Mage::getModel($module);
		$model->$function();
	} catch (Exception $e) {
		die('FAILURE:'.$e->getMessage());
	}
	die('SUCCESS');
} else {
	die('FAILURE');
}