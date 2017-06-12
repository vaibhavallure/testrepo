<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('allure_productshare')} ADD COLUMN `last_product` int default 0
");

$installer->endSetup();

?>
