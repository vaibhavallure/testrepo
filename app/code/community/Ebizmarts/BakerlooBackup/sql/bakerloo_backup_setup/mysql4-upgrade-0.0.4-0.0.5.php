<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_backup/files')}` ENGINE='InnoDB';");
$installer->endSetup();
