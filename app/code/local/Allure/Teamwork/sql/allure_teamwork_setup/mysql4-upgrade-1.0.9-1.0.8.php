<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$setup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );
if (!$setup->getAttribute('customer', 'tw_accept_marketing', 'attribute_id')) {
    $setup->addAttribute('customer', 'tw_accept_marketing', array(
        'type' => 'int',
        'input' => 'select',
        'label' => 'Accept Marketing',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 0,
        'default' => 0,
        'visible_on_front' => 1,
        'source' =>   'eav/entity_attribute_source_boolean',
        'sort_order' => 140,
        'position'   => 140
    ));
    
    $acceptMarketingAttributeCode = Mage::getSingleton('eav/config')
    ->getAttribute('customer', "tw_accept_marketing");
    
    $acceptMarketingAttributeCode->setData('used_in_forms', array(
        'customer_account_create',
        'customer_account_edit',
        'adminhtml_customer',
    ));
    $acceptMarketingAttributeCode->save();
}

if (!$setup->getAttribute('customer', 'tw_accept_transactional', 'attribute_id')) {
    $setup->addAttribute('customer', 'tw_accept_transactional', array(
        'type' => 'int',
        'input' => 'select',
        'label' => 'Accept Transactional',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 0,
        'default' => 0,
        'visible_on_front' => 1,
        'source' =>   'eav/entity_attribute_source_boolean',
        'sort_order' => 150,
        'position'   => 150
    ));
    
    $acceptMarketingAttributeCode = Mage::getSingleton('eav/config')
    ->getAttribute('customer', "tw_accept_transactional");
    
    $acceptMarketingAttributeCode->setData('used_in_forms', array(
        'customer_account_create',
        'customer_account_edit',
        'adminhtml_customer',
    ));
    $acceptMarketingAttributeCode->save();
}


$installer->endSetup();
