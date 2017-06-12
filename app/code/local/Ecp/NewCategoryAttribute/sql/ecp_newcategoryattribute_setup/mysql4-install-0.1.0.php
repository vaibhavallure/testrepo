<?php

$installer = $this;
$installer->startSetup();

/////CREATING THE NEW CATEGORY ATTRIBUTE "RETURN POLICY"/////

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = 4; //$installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'cat_return_policy', array(
    'type' => 'text',
    'label' => 'Category Return Policy',
    'input' => 'textarea',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => null
));
$installer->addAttributeToGroup(
        $entityTypeId, $attributeSetId, $attributeGroupId, 'new_cat_attrb', '11'                    //last Magento's attribute position in General tab is 10
);
$attributeId = $installer->getAttributeId($entityTypeId, 'cat_return_policy');
$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_text')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");
//this will set data of your custom attribute for root category
Mage::getModel('catalog/category')
        ->load(1)
        ->setImportedCatId(0)
        ->setInitialSetupFlag(true)
        ->save();
//this will set data of your custom attribute for default category
Mage::getModel('catalog/category')
        ->load(2)
        ->setImportedCatId(0)
        ->setInitialSetupFlag(true)
        ->save();

/////CREATING STATIC BLOCKS "JEWELRY CARE" & "RETURN POLICY"/////

$staticBlock = array(
    'title' => 'Jewelry Care',
    'identifier' => 'jewelry-care',
    'content' => '<h1>Jewelry Care Title</h1><p>Jewelry Care Content Of CMS Block</p>',
    'is_active' => 1,
    'stores' => array(0)
);

Mage::getModel('cms/block')->setData($staticBlock)->save();

$staticBlock = array(
    'title' => 'Return Policy',
    'identifier' => 'return-policy',
    'content' => '<h1>Return Policy Title</h1><p>Return Policy Content Of CMS Block</p>',
    'is_active' => 1,
    'stores' => array(0)
);

Mage::getModel('cms/block')->setData($staticBlock)->save();

/////CREATING PRODUCT ATTRIBUTES "RETURN POLICY" & "JEWELRY CARE"/////

if (!function_exists('createAttribute')) {

    function createAttribute($code, $label, $attribute_type, $product_type) {
        $_attribute_data = array(
            'attribute_code' => $code,
            'is_global' => '1',
            'frontend_input' => $attribute_type, //'boolean',
            'default_value_text' => '',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => '0',
            'is_required' => '0',
            'apply_to' => array($product_type), //array('grouped')
            'is_configurable' => '0',
            'is_searchable' => '0',
            'is_visible_in_advanced_search' => '0',
            'is_comparable' => '0',
            'is_used_for_price_rules' => '0',
            'is_wysiwyg_enabled' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'frontend_label' => array($label)
        );
        $model = Mage::getModel('catalog/resource_eav_attribute');
        if (!isset($_attribute_data['is_configurable'])) {
            $_attribute_data['is_configurable'] = 0;
        }
        if (!isset($_attribute_data['is_filterable'])) {
            $_attribute_data['is_filterable'] = 0;
        }
        if (!isset($_attribute_data['is_filterable_in_search'])) {
            $_attribute_data['is_filterable_in_search'] = 0;
        }
        if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
            $_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
        }
        $defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
        if ($defaultValueField) {
            $_attribute_data['default_value'] = '';
        }
        $model->addData($_attribute_data);
        $model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
        $model->setIsUserDefined(1);
        try {
            $model->save();
        } catch (Exception $e) {
            echo '<p>Sorry, error occured while trying to save the attribute. Error: ' . $e->getMessage() . '</p>';
        }

        return true;
    }
}

createAttribute('jewelry_care', 'Jewelry Care', 'textarea', 'simple,grouped,configurable,virtual,bundle,downloadable');
createAttribute('return_policy', 'Return Policy', 'textarea', 'simple,grouped,configurable,virtual,bundle,downloadable');

$installer->endSetup();