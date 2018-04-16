<?php
$this->startSetup();

$this->addAttribute('order', 'counterpoint_cust_no', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint Cust No',
    'visible'       => true,
    'required'      => false,
    'default'		=> NULL
));

$setup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );

$setup->addAttribute('customer', 'counterpoint_cust_no', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Counterpoint Cust No',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => NULL,
    'visible_on_front' => 1,
    'source' =>   NULL
)); 

$setup->addAttribute('customer', 'temp_email', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Temp Email',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => NULL,
    'visible_on_front' => 1,
    'source' =>   NULL
)); 

$setup->addAttribute('customer', 'cust_note', array(
    'type' => 'text',
    'input' => 'text',
    'label' => 'Customer Note',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => NULL,
    'visible_on_front' => 1,
    'source' =>   NULL
));

$this->endSetup();

?>
