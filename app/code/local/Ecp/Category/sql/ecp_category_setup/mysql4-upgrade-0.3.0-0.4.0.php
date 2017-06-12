<?php

$installer = $this;

$installer->startSetup();

$installer->run("");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->removeAttribute('catalog_category','separated_jewelry');
$setup->addAttribute('catalog_category', 'separated_jewelry', array(
    'group' => 'MT',
    'input' => 'select',
    'type' => 'int',
    'label' => 'menu separated?',
    'visible' => 1,
    'required' => 1,
    'user_defined' => 0,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source' => 'eav/entity_attribute_source_boolean'
));

$installer->endSetup();
