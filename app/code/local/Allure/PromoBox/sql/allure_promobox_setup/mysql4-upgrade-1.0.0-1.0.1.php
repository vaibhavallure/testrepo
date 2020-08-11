<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  `allure_promobox_banner` ADD `iframe_src` varchar (255) default null");

$installer->endSetup();



