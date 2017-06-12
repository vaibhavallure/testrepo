<?php
$this->startSetup();
$this->addAttribute('quote', 'order_type', array(
    'type'          => 'varchar',
    'label'         => 'Order Type',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('order', 'order_type', array(
    'type'          => 'varchar',
    'label'         => 'Order Type',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));


$this->endSetup();
?>
