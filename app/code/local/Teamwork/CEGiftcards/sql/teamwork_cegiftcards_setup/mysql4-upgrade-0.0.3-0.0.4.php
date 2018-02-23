<?php
$installer = $this;
$installer->startSetup();
$productTableName = $this->getTable('catalog/product');
$amountTableName = $this->getTable('teamwork_cegiftcards/amount');


$installer->run("

CREATE TABLE `{$amountTableName}` (
`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `amount` DECIMAL(12,4) NOT NULL DEFAULT 0,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `position` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`entity_id`),
  FOREIGN KEY (`product_id`) REFERENCES `{$productTableName}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();