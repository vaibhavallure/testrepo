<?php
$this->startSetup();
$this->addAttribute('order', 'counterpoint_orig_tkt_no', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint Orignal Tkt No',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote', 'counterpoint_orig_tkt_no', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint Orignal Tkt No',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('order', 'counterpoint_order_type', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint Order Type',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote', 'counterpoint_order_type', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint Order Type',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));


$this->endSetup();

?>
