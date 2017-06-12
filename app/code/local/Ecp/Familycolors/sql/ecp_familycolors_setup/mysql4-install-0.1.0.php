<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$eav =  Mage::getResourceModel('eav/entity_attribute');
$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('ecp_familycolors')} (
  `colorfamily_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `color` text NOT NULL default '',
  `description` text NOT NULL default '',
  `price` DECIMAL(12,4) NULL default NULL,
  PRIMARY KEY (`colorfamily_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

if (!($eav->getIdByCode('catalog_product', 'color_family'))) {
    $setup->addAttribute('catalog_product', 'color_family',array(
    		'group' => 'MT',
    		'type' => 'int',
                'label' => 'Color family',
                'input' => 'multiselect',
                'user_defined' => true,
                'is_user_defined' => true,
                'required' => false,
                'apply_to' => array('simple'),
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'visible_on_front' => true,
                'searchable' => true,
                'filterable' => true,
                'is_filterable_in_search' => true,
                'used_for_sort_by' => true,
                'used_in_product_listing' => true,
                'comparable' => true,
                'default' => 1,
                'source'   => 'ecp_familycolors/familycolors',
    ));

}

$installer->endSetup();