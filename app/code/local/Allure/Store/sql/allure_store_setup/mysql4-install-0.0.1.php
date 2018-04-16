<?php
$installer = $this;
$installer->startSetup();
    	
$installer->run("ALTER TABLE `{$this->getTable('core/store')}` ADD COLUMN `is_copy_old_product` int default 0");
$installer->run("ALTER TABLE `{$this->getTable('core/website')}` ADD COLUMN `stock_id` int default 0");
$installer->run("ALTER TABLE `{$this->getTable('core/website')}` ADD COLUMN `website_price_rule` decimal(12,4) default 1");

$installer->endSetup();

?>
