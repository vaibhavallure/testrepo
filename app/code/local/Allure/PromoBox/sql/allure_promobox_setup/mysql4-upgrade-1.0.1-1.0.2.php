<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  `allure_promobox_banner` ADD `iframe_style` text default null");

$installer->endSetup();



