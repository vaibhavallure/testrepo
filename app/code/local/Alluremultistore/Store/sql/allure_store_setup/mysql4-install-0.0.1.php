<?php
$installer = $this;
$installer->startSetup();

/*$installer->getConnection()
	->addColumn($installer->getTable('core/store'),'is_copy_old_product', array(
		'type'      => 'int',
		'default'   => 0,
		'label' => 'Is copy old product',
    	));  */
    	
    	
$installer->run("ALTER TABLE `{$this->getTable('core/store')}` ADD COLUMN `is_copy_old_product` int default 0");
$installer->run("ALTER TABLE `{$this->getTable('core/website')}` ADD COLUMN `stock_id` int default 0");
$installer->run("ALTER TABLE `{$this->getTable('core/website')}` ADD COLUMN `website_price_rule` decimal(12,4) default 1");

$installer->endSetup();

?>
