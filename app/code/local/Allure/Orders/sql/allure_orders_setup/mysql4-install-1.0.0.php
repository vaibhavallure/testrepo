<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('before_split_sales_flat_order')} LIKE `sales_flat_order`");
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('before_split_sales_flat_order_item')} LIKE `sales_flat_order_item`");
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('before_split_sales_flat_order_payment')} LIKE `sales_flat_order_payment`");
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('before_split_sales_flat_invoice')} LIKE `sales_flat_invoice`");
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('before_split_sales_flat_invoice_item')} LIKE `sales_flat_invoice_item`");
$installer->endSetup();

