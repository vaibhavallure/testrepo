<?php
$installer = $this;
 
$installer->startSetup();
//$installer->removeAttribute('catalog_product', 'backorder_time'); //removing previously set attribute

$installer->addAttribute('catalog_product', 'backorder_time', array(
    'group'             => 'General',
    'type'              => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Backorder Time',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => true,
    'filterable'        => true,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,configurable,virtual,bundle,grouped,downloadable,customproduct,giftcards',
    'is_configurable'   => false
));

$installer->endSetup();