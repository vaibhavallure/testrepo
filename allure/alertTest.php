<?php
require_once '../app/Mage.php';
umask(0);
Mage::app('admin');
/*ini_set('memory_limit', '-1');*/

/*$function = $_GET['function'];
$password = $_GET['pass'];*/

/*Mage::getModel('alertservices/alerts')->alertAvgPageLoad();
Mage::getModel('alertservices/alerts')->alertAvgPageLoadEmail();
Mage::getModel('alertservices/alerts')->alertPageNotFound();
Mage::getModel('alertservices/alerts')->alertNullUsers();
Mage::getModel('alertservices/alerts')->alertProductPrice();
Mage::getModel('alertservices/alerts')->alertCheckoutIssue();*/
Mage::getModel('alertservices/alerts')->instaTokenAlert(true);

//Mage::getModel('alertservices/alerts')->alertSalesOfFour(true);
//Mage::getModel('alertservices/alerts')->alertSalesOfSix(true);
//Mage::getModel('alertservices/alerts')->alertSalesOfTwo(true);


/*9196a2*/
//66.65.83.126 old ip
//203.109.124.232 sess




/*SELECT ord.increment_id,ord.created_at,ord.updated_at,ord.status,item.sku, SUBSTRING_INDEX(SUBSTRING(item.sku , position("|" in item.sku)+1 ), "|", 1)as color,prod.value as teamwork_plu , item.name,item.qty_ordered as quantity,item.price as price, ord.base_discount_amount,ord.base_tax_amount,ord.base_total_paid FROM sales_flat_order as ord  JOIN sales_flat_invoice as inv ON(ord.entity_id = inv.order_id) JOIN sales_flat_order_item AS item ON(ord.entity_id = item.order_id) LEFT JOIN catalog_product_entity_text prod on(prod.entity_id = item.product_id and prod.attribute_id=298) where item.product_type NOT in('configurable') AND (ord.created_at >= '2018-01-01 00:01:01' AND ord.created_at <= '2018-05-31 23:59:59')*/

echo "done";
