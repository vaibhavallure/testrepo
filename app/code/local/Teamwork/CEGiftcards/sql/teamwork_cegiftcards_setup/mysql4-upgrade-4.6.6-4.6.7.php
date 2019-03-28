<?php
$installer = $this;
$installer->startSetup();

$gcLinkTableName = $this->getTable('teamwork_cegiftcards/giftcard_link');

$installer->run("
ALTER TABLE `{$gcLinkTableName}`
    ADD COLUMN `gc_pin` VARCHAR(255) DEFAULT NULL
");

$installer->endSetup();
