<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */

$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);

$installer->addAttribute('quote_address_item', 'purchased_from', $options);
$installer->endSetup();
?>
