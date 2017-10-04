<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();


$from = $_GET['from'];
$to= $_GET['to'];
$store= $_GET['store'];

if(empty($from) || empty($to) || empty($store)){
    die('Please add from and to limit');
}


//Calculating purchased qty for each item since 31 March 2017 to till Date

$from_date = date("Y-m-d 04:00:00",strtotime($from));
$to_date = date("Y-m-d 03:59:59",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('store_id',$store)
        ->addAttributeToFilter('created_at', array('from'=>$from_date, 'to'=>$to_date))
         ->addAttributeToFilter('status', array('complete','processing','pending'));

echo $orders->getSelect();
echo "<br>";


foreach ($orders as $order){
	$items=$order->getAllVisibleItems();
	foreach ($items as $item){
				$itemData=Mage::getModel('allure_londoninventory/inventory')->load($item['sku'],'sku');
				$id=$itemData->getId();
				if(isset($id)){
					$qty=$itemData->getQty()+$item['qty_ordered'];
					$itemData->setQty($qty)->save();
				}else {
					$itemData=Mage::getModel('allure_londoninventory/inventory');
					$itemData->setQty($item['qty_ordered']);
					$itemData->setSku($item['sku']);
					$itemData->save();
				}
			
			}
}
echo "Done";
die;