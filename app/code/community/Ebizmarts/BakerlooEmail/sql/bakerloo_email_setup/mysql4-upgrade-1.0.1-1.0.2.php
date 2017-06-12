<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$installer->getTable('bakerloo_email/queue')}` ENGINE='InnoDB';
    ALTER TABLE `{$installer->getTable('bakerloo_email/unsent')}` ENGINE='InnoDB';
  ");
$installer->endSetup();
