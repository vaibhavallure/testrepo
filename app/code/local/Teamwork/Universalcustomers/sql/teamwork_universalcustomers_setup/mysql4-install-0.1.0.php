<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer', Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid, array(
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'UC Guid',
    'visible'           => false,
    'required'          => false,
    'sort_order'        => 90,
    'is_visible'        => 0,
    'is_user_defined'   => 1,
    'used_in_forms'     => array()
));

$installer->endSetup();