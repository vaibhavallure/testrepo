<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->addAttribute("quote_address_item", "is_separate_ship", array(
    'type'          => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));
