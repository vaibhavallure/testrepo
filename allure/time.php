<?php

$array = array();

$lower = 0;
$upper = 24;

$step = 0.5;

foreach ( range( $lower, $upper, $step ) as $time ) {
	$array[] = array('value' => $time, 'label' => sprintf("%02d:%02d", $time % 60, ($time * 60) % 60 ));
}

var_dump($array);

 ?>
