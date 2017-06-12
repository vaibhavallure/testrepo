<?php

$installer = $this;

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup->removeAttribute('catalog_category', 'category_color_title');
$setup->addAttribute('catalog_category', 'category_color_title', array(
    'group'         => 'MT',
    'type'          => 'varchar',
    'label'         => 'Color Title',
    'required'      => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => 1,
    //'input_renderer'=> 'ecp_category/adminhtml_color',
));

$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$attr = $connection->fetchOne('select * from eav_attribute where attribute_code = "category_color_title"');
$connection->query('UPDATE catalog_eav_attribute SET frontend_input_renderer = \'ecp_category/adminhtml_color\' where attribute_id ='.$attr);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$setup->removeAttribute('catalog_category', 'category_color_breadcrumb');
$setup->addAttribute('catalog_category', 'category_color_breadcrumb', array(
    'group'         => 'MT',
    'type'          => 'varchar',
    'label'         => 'Color Breadcrumb body',
    'required'      => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => 1,
    //'input_renderer'=> 'ecp_category/adminhtml_color',
));

$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$attr = $connection->fetchOne('select * from eav_attribute where attribute_code = "category_color_breadcrumb"');
$connection->query('UPDATE catalog_eav_attribute SET frontend_input_renderer = \'ecp_category/adminhtml_color\' where attribute_id ='.$attr);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$setup->removeAttribute('catalog_category', 'category_color_breadcrumb_cur');
$setup->addAttribute('catalog_category', 'category_color_breadcrumb_cur', array(
    'group'         => 'MT',
    'type'          => 'varchar',
    'label'         => 'Color Breadcrumb Current',
    'required'      => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => 1,
    //'input_renderer'=> 'ecp_category/adminhtml_color',
));

$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$attr = $connection->fetchOne('select * from eav_attribute where attribute_code = "category_color_breadcrumb_cur"');
$connection->query('UPDATE catalog_eav_attribute SET frontend_input_renderer = \'ecp_category/adminhtml_color\' where attribute_id ='.$attr);
