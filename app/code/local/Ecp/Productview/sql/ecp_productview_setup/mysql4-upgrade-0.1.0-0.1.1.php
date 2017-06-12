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
 * @category    Entrepids
 * @package     Entrepids_Slideshow
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();



$installer->addAttribute('catalog_product', 'second_video', array(
	        'group'             => 'Video group',
	        'label'             => 'Second Url Video',
	        'note'              => '',
	        'default'           => '',
	        'type'              => 'varchar',    //backend_type
	        'input'             => 'text', //frontend_input
	        'frontend_class'    => '',
	        'backend'           => '',
	        'frontend'          => '',
	        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	        'required'          => false,
	        'visible_on_front'  => false,
	        'apply_to'          => '',
	        'is_configurable'   => false,
	        'used_in_product_listing'   => false,
	        'sort_order'        => 5,
	    ));

$installer->addAttribute('catalog_product', 'second_video_thumb', array(
	        'group'             => 'Video group',
	        'label'             => 'Second Thumbnail',
	        'note'              => '',
	        'default'           => '',
	        'type'              => 'varchar',    //backend_type
	        'input'             => 'image', //frontend_input
	        'frontend_class'    => '',
	        'backend'           => 'ecp_productview/product_attribute_backend_image',
	        'frontend'          => '',
	        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	        'required'          => false,
	        'visible_on_front'  => true,
	        'apply_to'          => '',
	        'is_configurable'   => false,
	        'used_in_product_listing'   => false,
	        'sort_order'        => 5,
	    ));


$installer->endSetup();