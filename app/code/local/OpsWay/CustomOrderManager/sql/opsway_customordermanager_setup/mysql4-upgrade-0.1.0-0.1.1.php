<?php 
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'backorder_time' attribute for entities
 */
$entities = array(
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'backorder_time', $options);
}
$installer->endSetup();