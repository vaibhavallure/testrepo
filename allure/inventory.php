<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

//Calculating purchased qty for each item since 31 March 2017 to till Date

$from_date = date("Y-m-d 00:00:00",strtotime('03/31/2017'));
$to_date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('store_id',2)
        ->addAttributeToFilter('created_at', array('from'=>$from_date, 'to'=>$to_date));

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