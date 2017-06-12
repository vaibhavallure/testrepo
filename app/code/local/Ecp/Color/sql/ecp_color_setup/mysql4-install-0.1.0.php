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
 * @package     Ecp_Color
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('ecp_color')};
CREATE TABLE {$this->getTable('ecp_color')} (
  `color_id` int(11) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL default '',
  `order` int(11) NOT NULL default 0,
  `eav_id` int(11) NOT NULL default 0,
  `hex` varchar(6) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
//$eav =  Mage::getResourceModel('eav/entity_attribute');

$setup->removeAttribute('catalog_product', 'color');

$setup->addAttribute('catalog_product', 'color', array(
        'group'             => 'MT',
        'label'             => 'Color',
        'note'              => '',
        'default'           => '',
        'type'              => 'int',    //backend_type
        'input'             => 'select', //frontend_input
        'frontend_class'    => '',
        'source'            => 'ecp_color/attribute_source_color',
        'backend'           => '',
        'frontend'          => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined' => true,
        'required'          => true,
        'visible_on_front'  => true,
        'apply_to'          => '',
        'is_configurable'   => true,
        'used_in_product_listing'   => true,
        'sort_order'        => 5,
    ));

$installer->endSetup(); 
