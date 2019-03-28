<?php
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('service')}`
    ADD INDEX `status` (`status`);
");
$installer->endSetup();