<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_location')}`
	DROP COLUMN `status_changed`;
");
$installer->endSetup();