<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

/**
 * add attribute to customer i.e salesforce contact id
 */
$customerSetup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );
$customerSetup->addAttribute('customer', 'salesforce_contact_id', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'Salesforce Contact Id',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 0,
    'default'           => null,
    'visible_on_front'  => 1,
    'source'            =>   NULL,
    'comment'           => 'Salesforce Contact Id'
));


$this->endSetup();




