<?php  
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$installer->addAttribute('catalog_product', 'show_special_instruction', array(
            'group'           => 'General',
            'label'           => 'Show Special Instructions',
            'input'           => 'select',
            'type'            => 'int',
            'required'        => 0,
            'visible_on_front'=> 1,
            'source'            => 'eav/entity_attribute_source_boolean',
            'filterable'      => 0,
            'searchable'      => 0,
            'default'         => 1,
            'comparable'      => 0,
            'user_defined'    => 1,
            'is_configurable' => 0,
            'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'note'            => '',
));
$installer->endSetup();
?>