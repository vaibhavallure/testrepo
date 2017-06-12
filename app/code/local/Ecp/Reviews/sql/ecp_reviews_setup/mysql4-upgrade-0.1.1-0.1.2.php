<?php
$installer = $this;
$installer->startSetup();

$table = $this->getTable('ecp_reviews');
$query = "ALTER TABLE `ecp_reviews` CHANGE `review` `review` VARCHAR( 2000 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$connection->query($query);

$installer->endSetup();
?>