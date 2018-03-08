<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');
$setup->startSetup();

$setup->addAttribute('quote', 'old_store_id', array(
    'type'          => 'int',
    'label'         => 'Old Store Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$setup->addAttribute('order', 'old_store_id', array(
    'type'          => 'int',
    'label'         => 'Old Store Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$setup->addAttribute('quote_item', 'old_store_id', array(
    'type'          => 'int',
    'label'         => 'Old Store Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$setup->addAttribute('order_item', 'old_store_id', array(
    'type'          => 'int',
    'label'         => 'Old Store Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$setup->endSetup();

$installer->run("update sales_flat_quote set old_store_id = store_id");
$installer->run("update sales_flat_order set old_store_id = store_id");
$installer->run("update sales_flat_quote_item set old_store_id = store_id");
$installer->run("update sales_flat_order_item set old_store_id = store_id");

$installer->endSetup();

?>
