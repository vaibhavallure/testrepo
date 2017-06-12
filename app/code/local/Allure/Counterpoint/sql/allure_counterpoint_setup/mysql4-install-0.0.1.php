<?php
$this->startSetup();
  $this->addAttribute('order', 'create_order_method', array(
		'type'          => 'int',
		'label'         => 'Order Method',
		'visible'       => true,
		'required'      => false,
		'default'		=> 0
));

$this->addAttribute('quote', 'create_order_method', array(
		'type'          => 'int',
		'label'         => 'Order Method',
		'visible'       => true,
		'required'      => false,
		'default'		=> 0
));  


$this->addAttribute('order', 'counterpoint_order_id', array(
		'type'          => 'varchar',
		'label'         => 'Counter point Id',
		'visible'       => true,
		'required'      => false,
		'default'		=> ''
));

$this->addAttribute('quote', 'counterpoint_order_id', array(
		'type'          => 'varchar',
		'label'         => 'Counter point Id',
		'visible'       => true,
		'required'      => false,
		'default'		=> ''
)); 

//$this->run("Alter table `{$this->getTable('customer_entity')}` add column customer_type int default 0");


$setup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );
//add counterpoint flag
$setup->addAttribute('customer', 'customer_type', array(
		'type' => 'int',
		'input' => 'text',
		'label' => 'Customer Type',
		'global' => 1,
		'visible' => 1,
		'required' => 0,
		'user_defined' => 0,
		'default' => 0,
		'visible_on_front' => 1,
		'source' =>   NULL,
		'comment' => 'Customer Registered from'
)); 

$this->endSetup();


?>
