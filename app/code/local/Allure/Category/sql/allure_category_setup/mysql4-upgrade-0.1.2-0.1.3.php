<?php

$installer = $this;
$installer->startSetup();


$installer->addAttribute('catalog_category', 'default_length', array(
    'group'             => 'MT',
    'label'             => 'Default Post Lengths',
    'default'           => false,
    'type'              => 'int',    //backend_type
    'input'             => 'select', //frontend_input
    'frontend_class'    => '',
    'backend'           => 'eav/entity_attribute_backend_array',
    'frontend'          => '',
    "source" => "allure_category/System_Config_Source_Category",
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false,
    'used_in_product_listing'   => false
));

$installer->endSetup();

?>