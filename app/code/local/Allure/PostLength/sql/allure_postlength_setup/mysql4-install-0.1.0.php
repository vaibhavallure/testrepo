<?php

$installer = $this;
$installer->startSetup();

function getAttribute($label)
{
    return array(
        'group' => 'Post Length',
        'label' => $label,
        'input' => 'text',
        'type' => 'varchar',
        'required' => 0,
        'visible_on_front' => 0,
        'filterable' => 0,
        'searchable' => 0,
        'default' => '',
        'comparable' => 0,
        'user_defined' => 1,
        'apply_to' => 'simple',
        'is_configurable' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'note' => '',
    );
}

$installer->addAttribute('catalog_product', 'five_mm_sku', getAttribute('5mm simple product SKU'));
$installer->addAttribute('catalog_product', 'six_point_five_mm_sku', getAttribute('6.5mm simple product SKU'));
$installer->addAttribute('catalog_product', 'eight_mm_sku', getAttribute('8mm simple product SKU'));
$installer->addAttribute('catalog_product', 'nine_point_five_mm_sku', getAttribute('9.5mm simple product SKU'));



$installer->addAttribute('catalog_product', "default_postlength", array(
    'group' => 'Post Length',
    'type'       => 'varchar',
    'input'      => 'select',
    'label'      => 'select default post length',
    'required'   => false,
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'apply_to' => 'simple',
    'option'     => array (
        'values' => array(
            1 => '5MM',
            2 => '6.5MM',
            3 => '8MM',
            4=>'9.5MM'
        )
    ),

));



$installer->endSetup();

?>