<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'allowed_group', array(
    'group'             => 'general',
    'label'             => 'Allowed Group',
    'default'           => 'all',
    'type'              => 'text',    //backend_type
    'input'             => 'multiselect', //frontend_input
    'frontend_class'    => '',
    'backend'           => 'eav/entity_attribute_backend_array',
    'frontend'          => '',
    "source"            => "allure_category/system_config_source_groups",
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false,
    'used_in_product_listing'   => false
));
$installer->endSetup();
?>