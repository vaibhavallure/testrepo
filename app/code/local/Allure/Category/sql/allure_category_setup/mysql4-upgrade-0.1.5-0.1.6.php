<?php

$installer = $this;
$installer->startSetup();


$installer->addAttribute('catalog_category', 'set_virtual_child', array(
    'group'             => 'MT',
    'label'             => 'Set Virtual Children',
    'default'           => false,
    'note'  => 'You can add children to display in menu, Enter category id comma separated',
    'type'              => 'varchar',    //backend_type
    'input'             => 'text', //frontend_input
    'frontend_class'    => '',
    'backend'           => '',
    'frontend'          => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false,
    'used_in_product_listing'  => false
));

$installer->endSetup();
