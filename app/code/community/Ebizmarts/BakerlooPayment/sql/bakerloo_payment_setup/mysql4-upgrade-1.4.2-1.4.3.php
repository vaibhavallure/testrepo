<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_payment/installment')}` ENGINE='InnoDB';");
$installer->endSetup();