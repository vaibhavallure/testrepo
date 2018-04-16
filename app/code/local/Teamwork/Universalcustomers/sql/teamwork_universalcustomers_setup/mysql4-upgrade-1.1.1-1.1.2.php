<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer_address', Teamwork_Universalcustomers_Model_Address::$twUcAddressType, array(
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'UC Address Type',
    'visible'           => false,
    'required'          => false,
    'sort_order'        => 100,
    'is_visible'        => 0,
    'is_user_defined'   => 1,
    'used_in_forms'     => array()
));

$installer->endSetup();