<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_attribute_set')}`
    ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1;
");
$installer->endSetup();
