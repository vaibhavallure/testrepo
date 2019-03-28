<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('service_settings')}`
        CHANGE COLUMN `setting_value` `setting_value` MEDIUMTEXT NULL AFTER `channel_id`;
");

if($installer->getConnection()->delete($this->getTable('service_settings'), array('LENGTH(setting_value) = POW(2,16)-1')))
{
    $installer->run("
        UPDATE `{$this->getTable('service_chq')}` SET last_updated_time = NULL WHERE api_type='ecommerce-channel-export'
    ");
}
$installer->endSetup();