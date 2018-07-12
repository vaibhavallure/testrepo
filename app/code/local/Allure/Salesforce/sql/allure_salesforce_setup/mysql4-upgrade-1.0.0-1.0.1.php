<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

//create table - product_deleted_log
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('product_deleted_log')} (
    `id` int AUTO_INCREMENT NOT null,
    `product_id` int,
    `sku` varchar(255),
    `product_type` varchar(255),
    `name` varchar(255),
    `price` decimal(12,2),
    `salesforce_product_id` varchar(255),
    `salesforce_standard_pricebk` varchar(255),
    `salesforce_wholesale_pricebk` varchar(255),
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->run("INSERT INTO product_deleted_log(product_id,sku,name,price,product_type) SELECT product_id,sku,name,base_price,product_type FROM `sales_flat_order_item` a where a.sku not in(SELECT sku from catalog_product_entity) GROUP by a.sku");

$this->endSetup();

?>
