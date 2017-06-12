<?php

$installer = $this;
$installer->startSetup();

$installer->run(
    "CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful/pphtoken')}` (
        `token_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `api_mode` ENUM('sandbox', 'live') NOT NULL,
        `timestamp` VARCHAR(255) NOT NULL,
        `access_token` VARCHAR(255) NOT NULL,
        `refresh_token` VARCHAR(255) NOT NULL,
        `account_id` VARCHAR(255) NOT NULL,
        PRIMARY KEY(`token_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8
"
);


// if there were tokens in the config, move them to the db
$modes = array('sandbox', 'live');
//$coreConfig = Mage::getModel('core/config');
$configPath = 'payment/bakerloo_paypalhere/';

foreach($modes as $mode) {

    $configData = Mage::getModel('core/config_data');
    $configData->load("{$configPath}access_token_{$mode}", 'path');

    if ($configData->getId()) {
        $accessToken = $configData->getValue();
        $refreshToken = $configData->load("{$configPath}refresh_token_{$mode}", 'path')->getValue();
        $backendAccountId = $configData->load("{$configPath}backend_account_id_{$mode}", 'path')->getValue();
        $timestamp = $configData->load("{$configPath}timestamp_{$mode}", 'path')->getValue();

        $token = Mage::getModel('bakerloo_restful/pphtoken')
            ->setApiMode($mode)
            ->setAccessToken($accessToken)
            ->setRefreshToken($refreshToken)
            ->setAccountId($backendAccountId)
            ->setTimestamp($timestamp)
            ->save();

        $configValues = Mage::getModel('core/config_data')
            ->getCollection()
            ->addFieldToFilter('path', array('like' => "{$configPath}%{$mode}"));

        foreach ($configValues as $value) {
            $value->delete();
        }
    }
}

$installer->endSetup();

