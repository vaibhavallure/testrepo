<?php
$this->startSetup();
$this->addAttribute('order', 'wholesale_pay_option', array(
    'type'          => 'varchar',
    'label'         => 'Wholesaler Pay Option',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('order', 'is_ready_to_ship', array(
    'type'          => 'int',
    'label'         => 'Is Ready to ship',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));


$this->addAttribute('quote', 'wholesale_pay_option', array(
    'type'          => 'varchar',
    'label'         => 'Wholesaler Pay Option',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote', 'is_ready_to_ship', array(
    'type'          => 'int',
    'label'         => 'Is Ready to ship',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->endSetup();
?>
