<?php
/**
 * "C" starts sku replace with the "X" sku
 */

require_once('../app/Mage.php');
umask(0);
Mage::app();

$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);

$customerId = 52577;

$START_CHAR     = "C";  //sku starts at "C" letter.
$NEW_START_CHAR = "X";

$cart_log_file = "cart_replace_c_to_x.log";

$quotes=Mage::getModel('sales/quote')->getCollection()
->addFieldToFilter('customer_id', array('gt' => 0))
->addFieldToFilter('is_active', array('eq' => 1));

$quotes->getSelect()->join(array("sales_quote_item" => "sales_flat_quote_item"),
    "(sales_quote_item.quote_id = main_table.entity_id and sales_quote_item.sku like 'c%')",
    array());

$quotes->getSelect()->group('entity_id');

$counterProcessed=0;

foreach ($quotes as $quote){
   try {
       $customerId = $quote->getCustomerId();
       $email = $quote->getEmail();
       $quoteId = $quote->getId();
       $count = 0;
       foreach ($quote->getAllItems() as $item){
           $itemId = $item->getId();
           $productType = $item->getProductType();
           $sku = $item->getSku();
           
           $firstLetter1 = substr($sku,0,1);
           if($firstLetter1 != $START_CHAR){
               continue;
           }
           
           if($count == 0){
               Mage::log("--- Record Found ---",Zend_Log::DEBUG,$cart_log_file,true);
               Mage::log("Quote Id : ".$quoteId,Zend_Log::DEBUG,$cart_log_file,true);
               Mage::log("Customer Id : ".$customerId,Zend_Log::DEBUG,$cart_log_file,true);
               Mage::log("Customer Email : ".$email,Zend_Log::DEBUG,$cart_log_file,true);
           }
           $count++;
           
           Mage::log("sku : ".$sku,Zend_Log::DEBUG,$cart_log_file,true);
           
           $newSku = $NEW_START_CHAR . substr($sku, 1);
           $parentProductId = Mage::getModel("catalog/product")->getIdBySku($newSku);
           
           if(!$parentProductId){
               $productTempId = Mage::getModel("catalog/product")->getIdBySku($sku);
               if($productTempId){
                   $productTemp = Mage::getModel("catalog/product")->load($productTempId);
                   $parentItemNumber = $productTemp->getParentItemNumber();
                   if($parentItemNumber){
                       $newSku = $parentItemNumber;
                       $parentProductId = Mage::getModel("catalog/product")->getIdBySku($parentItemNumber);
                   }else{
                       continue;
                   }
               }else {
                   continue;
               }
           }
           
           $oldSkuArr = explode('|', $sku);
           $oldProductId = Mage::getModel("catalog/product")->getIdBySku($oldSkuArr[0]);
           $oldProduct = Mage::getModel("catalog/product")->load($oldProductId);
           
           $existingOptions = array();
           foreach ($oldProduct->getOptions() as $options){
               foreach ($options->getValues() as $option){
                   $existingOptions[$option['option_type_id']] = $option['title'];
               }
           }
           
           $newSkuArr = explode('|', $newSku);
           $newProductId = Mage::getModel("catalog/product")->getIdBySku($newSkuArr[0]);
           $newProduct = Mage::getModel("catalog/product")->load($newProductId);
           
           $newOptionsArr = array();
           foreach ($newProduct->getOptions() as $options){
               foreach ($options->getValues() as $option){
                   $newOptionsArr[$option['title']] = array($option['option_id']=>$option['option_type_id']);
               }
           }
           
           
           if($parentProductId){
               $iproductId = $item->getProductId();
               $iProductPP = Mage::getModel("catalog/product")->load($iproductId);
               $iskuPP = $iProductPP->getSku();
               
               $newSkuPP = $NEW_START_CHAR . substr($iskuPP, 1);
               $parentProductIdPP = Mage::getModel("catalog/product")->getIdBySku($newSkuPP);
               if(!$parentProductIdPP)
                   continue;
               
               $item->setProductId($parentProductIdPP);
               $item->setSku($newSku)->save();
               
               $itemOptionCollection = Mage::getModel("sales/quote_item_option")->getCollection()
               ->addFieldToFilter("item_id",$itemId);
               
               $selectedOption = array();
               $existingKey = "";
               foreach ($itemOptionCollection as $itemOption){
                   $code = $itemOption->getData("code");
                   $value = $itemOption->getData("value");
                   if($productType == "configurable"){
                       if($code == "info_buyRequest"){
                           $dvalue = unserialize($value);
                           
                           if($dvalue['cpid']){
                               $dvalue['cpid'] = $newProduct->getId();
                               if($dvalue['product']){
                                   $childPro = Mage::getModel('catalog/product')->load($dvalue['product']);
                                   $newSkuChild1 = $NEW_START_CHAR . substr($childPro->getSku(), 1);
                                   $childProductId1 = Mage::getModel("catalog/product")->getIdBySku($newSkuChild1);
                                   $dvalue['product'] = $childProductId1;
                               }
                           }else{
                               $dvalue['product'] = $newProduct->getId();
                           }
                           
                           foreach ($dvalue['options'] as $key1 => $opt1){
                               $existingKey = $key1;
                               $lable = $existingOptions[$opt1];
                               unset($dvalue['options'][$key1]);
                               $dvalue['options'] = $newOptionsArr[$lable];
                               $selectedOption = $newOptionsArr[$lable];
                           }
                           $sValue = serialize($dvalue);
                           $itemOption->setValue($sValue);
                           $itemOption->setProductId($newProduct->getId())
                           ->save();
                           
                       }elseif ($code == "option_ids"){
                           foreach ($selectedOption as $key2 => $val2){
                               $itemOption->setValue($key2);
                               
                               $itemOptionCollection1 = Mage::getModel('sales/quote_item_option')->getCollection();
                               $itemOptionCollection1->addFieldToFilter('item_id', array(
                                   'eq' => $itemId
                               ));
                               $itemOptionCollection1->addFieldToFilter('code', array(
                                   'eq' => 'option_'.$existingKey
                               ));
                               if($itemOptionCollection1->getSize()){
                                   $model1 = $itemOptionCollection1->getFirstItem();
                                   $model2 = Mage::getModel('sales/quote_item_option')->load($model1->getId());
                                   $model2->setProductId($newProduct->getId());
                                   $model2->setValue($val2);
                                   $model2->setCode('option_'.$key2);
                                   $model2->save();
                               }
                           }
                           $itemOption->setProductId($newProduct->getId())->save();
                       }elseif ($code == "simple_product"){
                           $childProduct = Mage::getModel("catalog/product")->load($value);
                           $childSku = $childProduct->getSku();
                           $newSkuChild = $NEW_START_CHAR . substr($childSku, 1);
                           $childProductId = Mage::getModel("catalog/product")->getIdBySku($newSkuChild);
                           $itemOption->setValue($childProductId);
                           $itemOption->setProductId($childProductId)->save();
                           
                           $itemOptionCollection1 = Mage::getModel('sales/quote_item_option')->getCollection();
                           $itemOptionCollection1->addFieldToFilter('item_id', array(
                               'eq' => $itemId
                           ));
                           $itemOptionCollection1->addFieldToFilter('code', array(
                               'eq' => 'product_qty_'.$value
                           ));
                           if($itemOptionCollection1->getSize()){
                               $model = $itemOptionCollection1->getFirstItem();
                               $model->setProductId($childProductId)
                               ->setCode('product_qty_'.$childProductId)
                               ->save();
                           }
                       }else{
                           if($code == "attributes"){
                               $itemOption->setProductId($newProduct->getId())->save();
                           }
                           
                       }
                   }elseif ($productType == "simple"){
                       $prodId = $itemOption->getData("product_id");
                       $productChildT = Mage::getModel("catalog/product")->load($prodId);
                       
                       $newSkuChildt = $NEW_START_CHAR . substr($productChildT->getSku(), 1);
                       
                       $newProductId1 = Mage::getModel("catalog/product")->getIdBySku($newSkuChildt);
                       $productChild = Mage::getModel("catalog/product")->load($newProductId1);
                       
                       if($code == "info_buyRequest"){
                           $dvalue = unserialize($value);
                           
                           if($dvalue['cpid']){
                               $dvalue['cpid'] = $newProduct->getId();
                               if($dvalue['product']){
                                   $childPro = Mage::getModel('catalog/product')->load($dvalue['product']);
                                   $newSkuChild2 = $NEW_START_CHAR . substr($childPro->getSku(), 1);
                                   $childProductId2 = Mage::getModel("catalog/product")->getIdBySku($newSkuChild2);
                                   $dvalue['product'] = $childProductId2;
                               }
                           }else{
                               $dvalue['product'] = $newProduct->getId();
                           }
                           
                           foreach ($dvalue['options'] as $key => $opt){
                               $existingKey = $key;
                               $lable = $existingOptions[$opt];
                               unset($dvalue['options'][$key]);
                               $dvalue['options'] = $newOptionsArr[$lable];
                               $selectedOption = $newOptionsArr[$lable];
                           }
                           $sValue = serialize($dvalue);
                           $itemOption->setValue($sValue);
                           $itemOption->setProductId($productChild->getId())
                           ->save();
                           
                           foreach ($selectedOption as $key => $val){
                               $itemOptionCollection3 = Mage::getModel('sales/quote_item_option')->getCollection();
                               $itemOptionCollection3->addFieldToFilter('item_id', array(
                                   'eq' => $itemId
                               ));
                               $itemOptionCollection3->addFieldToFilter('code', array(
                                   'eq' => 'option_'.$existingKey
                               ));
                               if($itemOptionCollection3->getSize()){
                                   $model = $itemOptionCollection3->getFirstItem();
                                   $model->setProductId($productChild->getId())
                                   ->setValue($val)
                                   ->setCode('option_'.$key)
                                   ->save();
                               }
                           }
                           
                           $itemOptionCollection4 = Mage::getModel('sales/quote_item_option')->getCollection();
                           $itemOptionCollection4->addFieldToFilter('item_id', array(
                               'eq' => $itemId
                           ));
                           $itemOptionCollection4->addFieldToFilter('code', array(
                               'eq' => 'parent_product_id'
                           ));
                           
                           if($itemOptionCollection4->getSize()){
                               $model = $itemOptionCollection4->getFirstItem();
                               $model->setProductId($productChild->getId())
                               ->setValue($newProduct->getId())
                               ->save();
                           }
                           
                       }
                   }
               }
               Mage::log($counterProcessed."::Record updated",Zend_Log::DEBUG,$cart_log_file,true);
           }
       }
   } catch (Exception $e) {
   }
}

echo "Processed:".$counterProcessed;