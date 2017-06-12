<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Tryon
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();


$installer->removeAttribute('catalog_product', 'tryon');
$installer->removeAttribute('catalog_product', 'categorytryon');
$installer->removeAttribute('catalog_product', 'tryonregion');



$installer->addAttribute('catalog_product', 'available_on_try_on_studio', array(
	        'group'             => 'MT',
	        'label'             => 'Aviable on try on studio',
	        'note'              => '',
	        'default'           => '',
	        'type'              => 'int',    //backend_type
	        'input'             => 'select', //frontend_input
	        'frontend_class'    => '',
	        'backend'           => '',
	        'frontend'          => '',
                'source' => 'eav/entity_attribute_source_boolean',
	        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	        'required'          => false,
	        'apply_to'          => '',
	        'is_configurable'   => false,
	        'visible_on_front'  => true,
	        'used_in_product_listing'   => false,
	        'sort_order'        => 5,
	    ));


$installer->addAttribute('catalog_category', 'categorytryon', array(
	        'group'             => 'MT',
	        'label'             => 'Tryon group',
	        'note'              => '',
	        'default'           => '',
	        'type'              => 'int',    //backend_type
	        'input'             => 'select', //frontend_input
	        'frontend_class'    => '',
	        'backend'           => '',
	        'frontend'          => '',
                'source' => 'eav/entity_attribute_source_boolean',
	        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	        'required'          => false,
	        'visible_on_front'  => true,
	        'apply_to'          => '',
	        'is_configurable'   => false,
	        'used_in_product_listing'   => false,
	        'sort_order'        => 5,
	    ));

$installer->addAttribute('catalog_product', 'tryonregion', array(
	        'group'             => 'MT',
	        'label'             => 'Tryon Regions',
	        'note'              => '',
	        'default'           => '',
	        'type'              => 'varchar',    //backend_type
	        'input'             => 'multiselect', //frontend_input
	        'frontend_class'    => '',
	        'backend'           => 'eav/entity_attribute_backend_array',
	        'frontend'          => '',
                'source' => 'ecp_tryon/entity_attribute_source_regions',
	        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	        'required'          => false,
	        'visible_on_front'  => true,
	        'apply_to'          => '',
	        'is_configurable'   => false,
	        'used_in_product_listing'   => false,
	        'sort_order'        => 5,
	    ));

$installer->endSetup(); 