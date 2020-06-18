<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$entities = array(
    "quote",
    "order"
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'default'  => 0,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $tableName = $installer->getTable("sales/{$entity}");
    if (!$installer->getConnection()->tableColumnExists($tableName, 'is_processed')) {
        $installer->addAttribute($entity, 'is_processed', $options);
    }
    
    if (!$installer->getConnection()->tableColumnExists($tableName, 'is_skip_to_signifyd')) {
        $installer->addAttribute($entity, 'is_skip_to_signifyd', $options);
    }
}
