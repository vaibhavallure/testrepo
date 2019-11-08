<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$entities = array("quote", "order", "quote_address");
$attributes = array("is_contain_backorder");

foreach ($entities as $entity){
    foreach ($attributes as $attribute){
        $installer->addAttribute($entity, $attribute,
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'visible' => true,
                'required' => false,
                'default' => 0
            )
        );
    }
}
