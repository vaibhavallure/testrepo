<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$orderSetup = new Mage_Sales_Model_Resource_Setup('core_setup');

$orderSetup->addAttribute('quote', 'teamwork_orig_receipt_id', array(
    'type'          => 'varchar',
    'label'         => 'Teamwork Orignal Receipt Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order', 'teamwork_orig_receipt_id', array(
    'type'          => 'varchar',
    'label'         => 'Teamwork Orignal Receipt Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote_item', 'exchange_qty', array(
    'type'          => 'varchar',
    'label'         => 'Exchange Qty',
    'visible'       => true,
    'required'      => false,
    'default'		=> '0'
));

$orderSetup->addAttribute('order_item', 'exchange_qty', array(
    'type'          => 'varchar',
    'label'         => 'Exchange Qty',
    'visible'       => true,
    'required'      => false,
    'default'		=> '0'
));


$orderSetup->addAttribute('quote_item', 'teamwork_orig_receipt_id', array(
    'type'          => 'varchar',
    'label'         => 'Orignal Receipt Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order_item', 'teamwork_orig_receipt_id', array(
    'type'          => 'varchar',
    'label'         => 'Orignal Receipt Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote_item', 'teamwork_reason', array(
    'type'          => 'varchar',
    'label'         => 'Reason',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order_item', 'teamwork_reason', array(
    'type'          => 'varchar',
    'label'         => 'Reason',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$orderSetup->addAttribute('quote_item', 'teamwork_reason_code', array(
    'type'          => 'varchar',
    'label'         => 'Reason Code',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order_item', 'teamwork_reason_code', array(
    'type'          => 'varchar',
    'label'         => 'Reason Code',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$installer->endSetup();
