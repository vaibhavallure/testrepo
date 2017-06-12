<?php
/**
 * File for database configuration
 * 
 * @category    Ecp
 * @package     Ecp_Sizechart
 */

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('ecp_sizechart')} (
    `sizechart_id` int(11) unsigned NOT NULL auto_increment,
    `title` varchar(255) NOT NULL default '',
    `content_size_chart` text NOT NULL default '',
    `status` smallint(6) NOT NULL default '1',
    `created_time` datetime NULL,
    `update_time` datetime NULL,
    PRIMARY KEY (`sizechart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

//$installer->removeAttribute('catalog_product', 'size_chart');
$installer->addAttribute('catalog_product', 'size_chart', array(
        'group'             => 'Size chart',
        'label'             => 'Size chart info',
        'note'              => '',
        'default'           => '',
        'type'              => 'int',    //backend_type
        'input'             => 'select', //frontend_input
        'frontend_class'    => '',
        'source'            => 'ecp_sizechart/attribute_source_sizechart',
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

$installer->endSetup(); 