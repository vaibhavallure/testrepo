<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'),'mail_delivery_option','tinyint unsigned NOT NULL DEFAULT 1');

$this->endSetup();
