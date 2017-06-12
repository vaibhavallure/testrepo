<?php

$installer = $this;

$installer->startSetup();

$installer->run("");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_category', 'category_display', array(
    'group' => 'General',
    'input' => 'select',
    'type' => 'varchar',
    'label' => 'Category Display Products',
    'backend' => 0,
    'visible' => 1,
    'required' => 1,
    'user_defined' => 0,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source' => 'ecp_category/entity_attribute_source_category_display'
));

$installer->endSetup();
