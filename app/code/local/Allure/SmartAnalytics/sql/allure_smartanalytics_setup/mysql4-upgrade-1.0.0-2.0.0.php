<?php
$installer = $this;

$installer->startSetup();

$sales_setup =  new Mage_Sales_Model_Mysql4_Setup('sales_setup');

// A
$attribute  = array(
	'type'          => 'varchar',
	'label'         => 'Google Cookie',
	'default'       => '',
	'visible'       => false,
	'required'      => false,
	'user_defined'  => true,
	'searchable'    => false,
	'filterable'    => false,
	'comparable'    => false );

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order'),
    'google_cookie',
    'varchar(255) NULL DEFAULT NULL'
);

$sales_setup->addAttribute('order', 'google_cookie', $attribute);


// B
$attribute  = array(
	'type'          => 'int',
	'label'         => 'Sent Data To Google',
	'default'       => '',
	'visible'       => false,
	'required'      => false,
	'user_defined'  => true,
	'searchable'    => false,
	'filterable'    => false,
	'comparable'    => false );

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order'),
    'sent_data_to_google',
    'int'
);

$sales_setup->addAttribute('order', 'sent_data_to_google', $attribute);


// C
$attribute  = array(
	'type'          => 'varchar',
	'label'         => 'Google Traffic Source Cookie',
	'default'       => '',
	'visible'       => false,
	'required'      => false,
	'user_defined'  => true,
	'searchable'    => false,
	'filterable'    => false,
	'comparable'    => false );

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order'),
    'google_ts_cookie',
    'varchar(255) NULL DEFAULT NULL'
);

$installer->run("
	update {$installer->getTable('sales_flat_order')} set sent_data_to_google=1;
");

$sales_setup->addAttribute('order', 'google_ts_cookie', $attribute);


// D
$attribute  = array(
	'type'          => 'varchar',
	'label'         => 'Google Category',
	'default'       => '',
	'visible'       => false,
	'required'      => false,
	'user_defined'  => true,
	'searchable'    => false,
	'filterable'    => false,
	'comparable'    => false
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order_item'),
    'google_category',
    'varchar(255) NULL DEFAULT NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_quote_item'),
    'google_category',
    'varchar(255) NULL DEFAULT NULL'
);

$sales_setup->addAttribute('quote_item', 'google_category', $attribute);
$sales_setup->addAttribute('order_item', 'google_category', $attribute);

$installer->endSetup();
