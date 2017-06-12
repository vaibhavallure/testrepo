<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$installer->getTable('bakerloo_restful/customprice')}` ENGINE='InnoDB';
    ALTER TABLE `{$installer->getTable('bakerloo_restful/shift')}` ENGINE='InnoDB';
    ALTER TABLE `{$installer->getTable('bakerloo_restful/shift_activity')}` ENGINE='InnoDB';
    ALTER TABLE `{$installer->getTable('bakerloo_restful/shift_movement')}` ENGINE='InnoDB';
  ");
$installer->endSetup();

