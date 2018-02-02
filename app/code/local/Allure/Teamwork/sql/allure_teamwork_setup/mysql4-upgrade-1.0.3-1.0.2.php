<?php
$this->startSetup();

$this->addAttribute('order', 'temp_customer_id', array(
    'type'          => 'int',
    'label'         => 'Temp Customer Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->addAttribute('order', 'temp_email', array(
    'type'          => 'varchar',
    'label'         => 'Temp Email',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$this->endSetup();

?>
