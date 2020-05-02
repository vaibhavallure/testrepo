<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add PLU to order_item and quote_item
 */

$entities = array(
    "quote_item",
    "order_item"
);


$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'default'  => null,
    'visible'  => true,
    'required' => false
);

foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'plu', $options);
}


$installer->endSetup();
