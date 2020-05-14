<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$installer->addAttribute('catalog_product', "is_post_length_product", array(
    'group'      => 'General',
    'type'       => 'int',
    'input'      => 'select',
    'label'      => 'is post length product',
    'required'   => false,
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'apply_to' => 'simple',
    'used_for_promo_rules'=>1,
    'source' => 'eav/entity_attribute_source_boolean'
));


$installer->endSetup();
