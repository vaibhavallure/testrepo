<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$orderSetup = new Mage_Sales_Model_Resource_Setup('core_setup');

$orderSetup->addAttribute('quote', 'teamwork_gift_amount', array(
    'type'          => 'decimal',
    'label'         => 'Teamwork Gift Amount',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$orderSetup->addAttribute('order', 'teamwork_gift_amount', array(
    'type'          => 'decimal',
    'label'         => 'Teamwork Gift Amount',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$orderSetup->addAttribute('quote', 'teamwork_deposit_amount', array(
    'type'          => 'decimal',
    'label'         => 'Teamwork Deposit Amount',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$orderSetup->addAttribute('order', 'teamwork_deposit_amount', array(
    'type'          => 'decimal',
    'label'         => 'Teamwork Deposit Amount',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$orderSetup->addAttribute('quote_item', 'teamwork_gift_deposit_data', array(
    'type'          => 'text',
    'label'         => 'Teamwork Git Deposit Data',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order_item', 'teamwork_gift_deposit_data', array(
    'type'          => 'text',
    'label'         => 'Teamwork Git Deposit Data',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));


$installer->endSetup();
