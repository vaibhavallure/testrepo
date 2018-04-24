<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_status_shipping')}`
    ADD CONSTRAINT `FK_service_status_shipping_service_status` FOREIGN KEY (`PackageId`) REFERENCES `{$this->getTable('service_status')}` (`PackageId`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `{$this->getTable('service_status_items')}`
    ADD CONSTRAINT `FK_service_status_items_service_status` FOREIGN KEY (`PackageId`) REFERENCES `{$this->getTable('service_status')}` (`PackageId`) ON UPDATE CASCADE ON DELETE CASCADE;

DROP TABLE IF EXISTS `{$this->getTable('service_statusoms')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('service_statusoms')}` (
    `StatusId` CHAR(36) NOT NULL,
    `request_id` CHAR(36) NOT NULL,
    `OrderId` CHAR(36) NOT NULL,
    `Status` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`StatusId`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('service_statusoms_items')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('service_statusoms_items')}` (
    `StatusId` CHAR(36) NOT NULL,
    `OrderItemId` CHAR(36) NOT NULL,
    `ItemId` CHAR(36) NOT NULL,
    `Status` VARCHAR(50) NOT NULL,
    `ShippingMethod` VARCHAR(50) NOT NULL,
    `MagentoShippingMethod` VARCHAR(50) NOT NULL,
    `Carrier` VARCHAR(50) NOT NULL,
    `TrackingNumber` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`StatusId`, `OrderItemId`),
    CONSTRAINT `fk_service_statusoms_service_statusoms_items` FOREIGN KEY (`StatusId`) REFERENCES `{$this->getTable('service_statusoms')}` (`StatusId`) ON UPDATE CASCADE ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();