<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('cataloginventory/stock')}` ADD COLUMN `website_id` int default 0");

//$installer->run("ALTER TABLE `{$this->getTable('cataloginventory/stock_item')}` ADD COLUMN `website_id` int default 0");

$installer->endSetup();

?>
