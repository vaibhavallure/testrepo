<?php


$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer_address', 'email_shipping ', array(
    'type'             => 'varchar',
    'input'            => 'text',
    'label'            => 'email_shipping ',
    'multiline_count'  => 1,
    'global'           => 1,
    'visible'          => 1,
    'required'         => 0,
    'user_defined'     => 1,
    'is_system'        => 0,
    'visible_on_front' => 1
));



$tabla = Mage::getModel('eav/entity_attribute')->getCollection();


foreach($tabla as $rows){
		$id = $rows->getAttributeId();
}

$tablequote = $this->getTable('sales/quote_address');

$tableorder = $this->getTable('sales/order_address');

$installer ->run ("

INSERT INTO {$this->getTable('eav_entity_attribute')} 
(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`)
 VALUES ('2', '2', '2', '".$id."', '0');

 ALTER TABLE  " .$tablequote . " ADD  `email_shipping` varchar(255) NOT NULL;
 
 ALTER TABLE  ". $tableorder . " ADD  `email_shipping` varchar(255) NOT NULL;
 
");


Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'email_shipping')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();


	
$installer->endSetup();
