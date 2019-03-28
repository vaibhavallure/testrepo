<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service')}`
	CHANGE COLUMN `chunk` `chunk` INT(10) NOT NULL DEFAULT '0' AFTER `status`;
    
ALTER TABLE `{$this->getTable('service_setting_payment')}`
	ADD COLUMN `refund_in_teamwork` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `allow_authorize_only`;
");
$installer->endSetup();