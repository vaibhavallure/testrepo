<?php
$installer = $this;

$installer->startSetup();

$installer->run("
         CREATE TABLE IF NOT EXISTS `allure_appointment_flag` (
                `flag` int(1) default 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");

$installer->run("INSERT INTO `allure_appointment_flag` (`flag`) VALUES (0);");


$installer->endSetup();



