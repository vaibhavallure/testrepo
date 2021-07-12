<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', 'brownthomas_barcode', array(
    'label'           => 'Brownthomas Barcode',
    'input'           => 'text',
    'type'            => 'varchar',
    'required'        => 0,
    'visible_on_front'=> 0,
    'filterable'      => 0,
    'searchable'      => 0,
    'comparable'      => 0,
    'user_defined'    => 1,
    'is_configurable' => 0,
    'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'            => '',
));

$installer->endSetup();


?>