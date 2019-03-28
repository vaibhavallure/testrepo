<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute(
    "catalog_category", "assigned_lengths",
    array(
        "type" => "text",
        "backend" => "eav/entity_attribute_backend_array",
        'group' => 'MT',
        "label" => "Assigned Post Lengths",
        'note'  => 'You can add addtional lengths to category_postlengths attribute',
        "input" => "multiselect",
        "source" => "allure_category/System_Config_Source_Category",
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'is_visible' => 1,
        'required' => 0,
        'searchable' => 0,
        'filterable' => 0,
        'unique' => 0,
        'comparable' => 0,
        'visible_on_front' => 0,
        'user_defined' => 1,
    )
    );
$installer->endSetup();
?>