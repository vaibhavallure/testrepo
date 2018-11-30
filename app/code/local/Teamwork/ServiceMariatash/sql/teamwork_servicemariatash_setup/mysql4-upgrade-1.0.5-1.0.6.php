<?php
$installer = $this;

$installer->startSetup();
$installer->run("

    DROP TABLE IF EXISTS `{$this->getTable('service_fee_mapping')}`;
    CREATE TABLE `{$this->getTable('service_fee_mapping')}` (
		`entity_id` INT(11) NOT NULL AUTO_INCREMENT,
		`shipping_id` INT(11) NOT NULL,
		`fee_id` CHAR(36) NOT NULL,
		UNIQUE INDEX (`entity_id`),
		INDEX `shipping_id` (`shipping_id`),
		INDEX `fee_id` (`fee_id`)
    ) 
    ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
");
$installer->endSetup();