<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'giftcard_amount', array(
        'group'             => 'Prices',
        'type'              => 'decimal',
        'backend'           => 'catalog/product_attribute_backend_price',
        'frontend'          => '',
        'label'             => 'Amount',
        'input'             => 'price',
        'class'             => 'validate-number',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
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
        'sort_order'        => 210,
        //'sort_order'        => -1,
    ));

$installer->addAttribute('catalog_product', 'giftcard_type', array(
        'group'             => 'Gift Card Information',
        //'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Card Type',
        'input'             => 'select',
        //'class'             => '',
        'source'            => 'teamwork_cegiftcards/catalog_product_type_giftcard_attribute_source_giftcardtype',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '0',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'teamwork_cegiftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
    ));

$installer->addAttribute('catalog_product', 'giftcard_allow_message', array(
        'group'             => 'Gift Card Information',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Allow Message',
        'input'             => 'boolean',
       // 'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
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
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'giftcard_email_template', array(
        'group'             => 'Gift Card Information',
//        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Email Template',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'teamwork_cegiftcards/catalog_product_type_giftcard_attribute_source_giftcardemailtemplate',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => 'teamwork_cegiftcards_general_email_template',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'teamwork_cegiftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'giftcard_email_template_uc', array(
        'group'             => 'Gift Card Information',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Email Template',
        'input'             => 'boolean',
       // 'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
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
        'is_configurable'   => false
    ));

    $installer->addAttribute('catalog_product', 'giftcard_lifetime', array(
        'group'             => 'Gift Card Information',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Lifetime (days)',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
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
        'is_configurable'   => false
    ));


$applyTo = $installer->getAttribute('catalog_product', 'weight', 'apply_to');
if ($applyTo) {
    $applyTo = explode(',', $applyTo);
    if (!in_array('teamwork_cegiftcard', $applyTo)) {
        $applyTo[] = 'teamwork_cegiftcard';
        $installer->updateAttribute('catalog_product', 'weight', 'apply_to', join(',', $applyTo));
    }
}

$fieldList = array(
    'cost',
);

// make these attributes not applicable to gift card products
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (in_array('teamwork_cegiftcard', $applyTo)) {
        foreach ($applyTo as $k => $v) {
            if ($v == 'teamwork_cegiftcard') {
                unset($applyTo[$k]);
                break;
            }
        }
        $installer->updateAttribute('catalog_product', $field, 'apply_to', join(',', $applyTo));
    }
}

$installer->endSetup();
