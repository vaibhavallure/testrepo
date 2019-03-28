<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder_discount_reason')}`
    DROP COLUMN `RecCreated`;

ALTER TABLE `{$this->getTable('service_weborder_fee')}`
    DROP COLUMN `RecCreated`;

ALTER TABLE `{$this->getTable('service_weborder_item')}`
    DROP COLUMN `RecCreated`;

ALTER TABLE `{$this->getTable('service_weborder_item_fee')}`
    DROP COLUMN `RecCreated`;

ALTER TABLE `{$this->getTable('service_weborder_item_line_discount')}`
    DROP COLUMN `RecCreated`;

ALTER TABLE `{$this->getTable('service_weborder_payment')}`
    DROP COLUMN `RecCreated`;

ALTER TABLE `{$this->getTable('service_weborder')}`
    ALTER `RecCreated` DROP DEFAULT;
ALTER TABLE `{$this->getTable('service_weborder')}`
    CHANGE COLUMN `RecCreated` `RecCreated` DATETIME NOT NULL AFTER `EComChannelId`;

ALTER TABLE `{$this->getTable('service')}`
    ALTER `rec_creation` DROP DEFAULT;
ALTER TABLE `{$this->getTable('service')}`
    CHANGE COLUMN `rec_creation` `rec_creation` DATETIME NOT NULL AFTER `request_id`;

ALTER TABLE `{$this->getTable('service_category')}`
    CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL AFTER `category_name`,
    ADD COLUMN `keywords` TEXT NULL DEFAULT NULL AFTER `description`;
");

$table = $this->getTable('service_settings');
$installer->run("
ALTER TABLE `{$table}`
    DROP PRIMARY KEY,
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `setting_name`,
    ADD PRIMARY KEY (`setting_name`, `channel_id`);
");

Mage::app()->getCacheInstance()->flush();

$records = $installer->getConnection()->fetchAll("SELECT * FROM `{$table}`");

$fixedData = array();
$channelIds = array();
foreach($records as $record)
{
    $settingValue = @unserialize($record['setting_value']);
    if (is_array($settingValue)) {
        $fixedData[$record['setting_name']] = array();
        foreach($settingValue as $channelId => $channelVals) {
            //$fixedData[$record['setting_name']][$channelId] = array($channelId => $channelVals);
            $fixedData[$record['setting_name']][$channelId] = $channelVals;
            $channelIds[$channelId] = true;
        }
    } else {
        $fixedData[$record['setting_name']] = $record['setting_value'];
    }
}
if (count($channelIds)) {
    $channelIds = array_keys($channelIds);
    foreach($fixedData as $settingName => $settingVal)
    {
        if (!is_array($settingVal))
        {
            $fixedData[$settingName] = array();
            foreach($channelIds as $channelId)
            {
                $fixedData[$settingName][$channelId] = $settingVal;
            }
        }
    }
    $namesToDeleteArr = array_keys($fixedData);
    $namesToDelete = "";
    $prefix = "";
    foreach($namesToDeleteArr as $k=>$v)
    {
        $namesToDelete .= $prefix . '"' . $v . '"';
        $prefix = ',';
    }
    $installer->run("DELETE FROM `{$table}` WHERE `setting_name` IN ({$namesToDelete})");
    foreach ($fixedData as $settingName => $settingValue)
    {
        foreach($settingValue as $channelId => $channelValue)
        {
            if (is_array($channelValue)) $channelValue = serialize($channelValue);
            $installer->run("INSERT INTO {$table} (`channel_id`, `setting_name`, `setting_value`) VALUES ('{$channelId}', '{$settingName}', '{$channelValue}')");
        }
    }
}

$installer->endSetup();