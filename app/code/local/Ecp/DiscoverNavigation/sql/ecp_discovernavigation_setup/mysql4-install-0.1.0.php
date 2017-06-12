<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('ecp_discover_mariatash_navigation_menu')}`(
  `discover_mariatash_id` int(11) unsigned NOT NULL auto_increment,
  `category_name` varchar(255) NOT NULL default '',
  `category_id` int(3) NOT NULL default '0',
  `url` varchar(255) NULL default '',
  `type` int(1) NOT NULL default '0',
  `sort_order` int(3) NOT NULL default '0',
  PRIMARY KEY (`discover_mariatash_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

//$installer->removeAttribute('catalog_category','discover_mt_navigation');

$installer->addAttribute('catalog_category', 'discover_mt_navigation', array(
    'group'             => 'MT',
    'label'             => 'Include in Discover Mariatash Menu',
    'note'              => 'Select Home if this is the parent category of the menu or page if is a submenu of the category',
    'default'           => false,
    'type'              => 'int',    //backend_type
    'input'             => 'select', //frontend_input
    'frontend_class'    => '',
    'backend'           => 'eav/entity_attribute_backend_array',
    'frontend'          => '',
    'source'            => 'ecp_discovernavigation/entity_attribute_source_options',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false,
    'used_in_product_listing'   => false    
));

$installer->endSetup(); 