<?php
$this->startSetup();
$this->addAttribute('order_item', 'base_currency', array(
    'type'          => 'varchar',
    'label'         => 'Base Currency',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('order_item', 'current_currency', array(
    'type'          => 'varchar',
    'label'         => 'Current Currency',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('order_item', 'current_country', array(
    'type'          => 'varchar',
    'label'         => 'Current Country',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));
$this->addAttribute('order_item', 'conversion_rate', array(
    'type'          => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'label'         => 'Conversion Rate',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$this->endSetup();

