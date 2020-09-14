<?php

$installer = $this;
$installer->startSetup();
//Including category attribute : for MT-1408 : VIP category show Pre-Order instead of Add to Bag button

    $this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'hide_filter', array(
        'type'                       => 'int',
        'label'                      => 'Hide Filter',
        'input'                      => 'select',
        'source'                     => 'eav/entity_attribute_source_boolean',
        'default'                    => '0',
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'group'                      => 'General Information',
        'visible'                    => true,
        'required'                   => false,
        'visible_on_front'           => false,
        'unique'                    => false,
        'user_defined'              => false,
        'is_user_defined'           => false,
        'used_in_product_listing'   => true,
        'sort_order'                => 12
    ));

$installer->endSetup();


?>