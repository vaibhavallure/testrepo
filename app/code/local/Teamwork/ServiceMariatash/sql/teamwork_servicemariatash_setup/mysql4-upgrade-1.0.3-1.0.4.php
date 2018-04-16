<?php
$installer = $this;
$installer->startSetup();

$weborderTable = $this->getTable('service_weborder');
if( !$installer->getConnection()->tableColumnExists($weborderTable, 'IsReady') )
{
    $installer->run("
        ALTER TABLE `{$weborderTable}`
            ADD COLUMN `IsReady` TINYINT NOT NULL DEFAULT '0' AFTER `OrderNo`,
            CHANGE COLUMN `EComShippingMethod` `EComShippingMethod` VARCHAR(255) NULL DEFAULT NULL AFTER `IsReady`,
        CHANGE COLUMN `DefaultLocationId` `DefaultLocationId` CHAR(36) NULL DEFAULT NULL AFTER `EComShippingMethod`;
            
        UPDATE `{$weborderTable}` SET IsReady='1';
    ");
}
$installer->endSetup();