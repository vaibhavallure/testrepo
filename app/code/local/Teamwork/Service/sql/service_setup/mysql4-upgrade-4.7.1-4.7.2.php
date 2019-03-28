<?php

$installer = $this;
$installer->startSetup();
$value = Teamwork_Service_Model_Confattrmapprop::VALUESMAPPING_VALUE;
$alias = Teamwork_Service_Model_Confattrmapprop::VALUESMAPPING_ALIAS;
$alias2 = Teamwork_Service_Model_Confattrmapprop::VALUESMAPPING_ALIAS2;
$aliasAlias2 = Teamwork_Service_Model_Confattrmapprop::VALUESMAPPING_ALIAS_ALIAS2;
$alias2Alias = Teamwork_Service_Model_Confattrmapprop::VALUESMAPPING_ALIAS2_ALIAS;
$installer->run("
ALTER TABLE `{$this->getTable('service_attribute_set')}`
    ADD COLUMN `values_mapping` ENUM('{$value}','{$alias}','{$alias2}','{$aliasAlias2}','{$alias2Alias}') DEFAULT '{$value}';
");
$installer->endSetup();
