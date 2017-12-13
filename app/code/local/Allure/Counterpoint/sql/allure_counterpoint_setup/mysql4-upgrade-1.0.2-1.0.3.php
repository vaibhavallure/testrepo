<?php
$this->startSetup();

$setup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );

$setup->addAttribute('customer', 'teamwork_customer_id', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Teamwork Customer Id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => '',
    'visible_on_front' => 1,
    'source' =>   NULL,
    'comment' => 'Teamwork Customer Id'
)); 

$setup->addAttribute('customer', 'sugarcrm_customer_id', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Sugarcrm Customer Id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => '',
    'visible_on_front' => 1,
    'source' =>   NULL,
    'comment' => 'Sugarcrm Customer Id'
)); 

$this->endSetup();

?>
