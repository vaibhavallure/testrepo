<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$orderSetup = new Mage_Sales_Model_Resource_Setup('core_setup');


$orderSetup->addAttribute('quote_item', 'tw_item_id', array(
    'type'          => 'varchar',
    'label'         => 'Tw Item Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order_item', 'tw_item_id', array(
    'type'          => 'varchar',
    'label'         => 'Tw Item Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('quote', 'other_sys_extra_info', array(
    'type'          => 'text',
    'label'         => 'Other Sys Extra Info',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order', 'other_sys_extra_info', array(
    'type'          => 'text',
    'label'         => 'Other Sys Extra Info',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$installer->endSetup();
