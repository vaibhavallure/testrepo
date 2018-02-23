<?php

$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
$installer->run("
			ALTER TABLE {$this->getTable('allure_instacatalog/feed')} ADD COLUMN product_count INT DEFAULT 0
			");

$installer->endSetup();

