<?php
$this->startSetup();
$this->addAttribute('quote_item', 'base_currency', array(
    'type'          => 'varchar',
    'label'         => 'Base Currency',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote_item', 'current_currency', array(
    'type'          => 'varchar',
    'label'         => 'Current Currency',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote_item', 'current_country', array(
    'type'          => 'varchar',
    'label'         => 'Current Country',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));


$this->endSetup();
?>
