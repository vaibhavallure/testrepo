<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'giftcard_amount_min', array(
        'group'             => 'Prices',
        'type'              => 'decimal',
        'backend'           => 'catalog/product_attribute_backend_price',
        'frontend'          => '',
        'label'             => 'Open Amount Min Value',
        'input'             => 'price',
        'class'             => 'validate-number',
        'source'            => '',
//        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'teamwork_cegiftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => 230,
    ));

$installer->addAttribute('catalog_product', 'giftcard_amount_max', array(
        'group'             => 'Prices',
        'type'              => 'decimal',
        'backend'           => 'catalog/product_attribute_backend_price',
        'frontend'          => '',
        'label'             => 'Open Amount Max Value',
        'input'             => 'price',
        'class'             => 'validate-number',
        'source'            => '',
//        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'teamwork_cegiftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => 240,
    ));

$installer->addAttribute('catalog_product', 'giftcard_open_amount', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Allow Open Amount',
        'input'             => 'boolean',
        'source'            => 'eav/entity_attribute_source_boolean',
//        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '1',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'teamwork_cegiftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => 220,
    ));

$installer->endSetup();
