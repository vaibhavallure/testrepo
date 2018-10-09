<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();


$catalogSetup = new Mage_Eav_Model_Entity_Setup('core_setup');

if (!$catalogSetup->getAttribute('catalog_product', 'order', 'attribute_id')) {
    $catalogSetup->addAttribute('catalog_product', 'order', array(
        'group'           => 'General',
        'label'           => 'Position',
        'input'           => 'text',
        'type'            => 'int',
        'required'        => 0,
        'visible_on_front'=> 1,
        'filterable'      => 0,
        'searchable'      => 0,
        'default'         => 0,
        'comparable'      => 0,
        'user_defined'    => 1,
        'is_configurable' => 0,
        'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'note'            => '',
    ));
}

if(!$catalogSetup->getAttribute('catalog_product', 'gtin_number', 'attribute_id')){
    $catalogSetup->addAttribute('catalog_product', 'gtin_number', array(
        'group'           => 'MT',
        'label'           => 'GTIN Number',
        'input'           => 'text',
        'type'            => 'varchar',
        'required'        => 0,
        'visible_on_front'=> 1,
        'filterable'      => 0,
        'searchable'      => 0,
        'default'         => '',
        'comparable'      => 0,
        'user_defined'    => 1,
        'is_configurable' => 0,
        'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'note'            => '',
    ));
}

$setup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );

if (!$setup->getAttribute('customer', 'is_teamwork_customer', 'attribute_id')) {
    $setup->addAttribute('customer', 'is_teamwork_customer', array(
        'type' => 'int',
        'input' => 'text',
        'label' => 'Is Teamwork Customer',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 0,
        'default' => 0,
        'visible_on_front' => 1,
        'source' =>   NULL
    )); 
}


$orderSetup = new Mage_Sales_Model_Resource_Setup('core_setup');
$orderSetup->addAttribute('quote', 'teamwork_receipt_id', array(
    'type'          => 'varchar',
    'label'         => 'Teamwork Receipt Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order', 'teamwork_receipt_id', array(
    'type'          => 'varchar',
    'label'         => 'Teamwork Receipt Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote', 'other_sys_currency', array(
    'type'          => 'varchar',
    'label'         => 'Other Sys Currency',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order', 'other_sys_currency', array(
    'type'          => 'varchar',
    'label'         => 'Other Sys Currency Symbol',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote', 'other_sys_currency_symbol', array(
    'type'          => 'varchar',
    'label'         => 'Other Sys Currency Symbol',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order', 'other_sys_currency_symbol', array(
    'type'          => 'varchar',
    'label'         => 'Other Sys Currency Symbol',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote', 'other_sys_currency_code', array(
    'type'          => 'varchar',
    'label'         => 'Other Sys Currency Code',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order', 'other_sys_currency_code', array(
    'type'          => 'varchar',
    'label'         => 'Other Sys Currency Code',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote_item', 'other_sys_qty', array(
    'type'          => 'varchar',
    'label'         => 'Other System Qty',
    'visible'       => true,
    'required'      => false,
    'default'		=> '0'
));

$orderSetup->addAttribute('order_item', 'other_sys_qty', array(
    'type'          => 'varchar',
    'label'         => 'Other System Qty',
    'visible'       => true,
    'required'      => false,
    'default'		=> '0'
));


$installer->endSetup();
