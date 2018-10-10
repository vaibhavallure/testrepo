<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

/**
 * add field to order table i.e. salesforce_order_id
 */
$orderSetup = new Mage_Sales_Model_Resource_Setup('core_setup');
$orderSetup->addAttribute('order', 'salesforce_uploaded_doc_id', array(
    'type'          => 'varchar',
    'label'         => 'Salesforce uploaded doc Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));



$this->endSetup();




