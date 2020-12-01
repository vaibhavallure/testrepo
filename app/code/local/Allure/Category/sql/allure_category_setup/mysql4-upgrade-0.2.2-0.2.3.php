<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', 'searchspring_script', array(
    'label'           => 'Searchspring Script',
    'input'           => 'text',
    'type'            => 'text',
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