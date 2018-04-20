<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
    MODIFY COLUMN `EComShippingMethod` VARCHAR(255) DEFAULT NULL
");
$installer->endSetup();