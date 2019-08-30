<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_appointment_customers')} ADD info_update INT(1) default 0");

$installer->endSetup();



