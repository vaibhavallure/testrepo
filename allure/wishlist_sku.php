<?php
/**
 * "C" starts sku replace with the "X" sku
 */

require_once('../app/Mage.php');
umask(0);
Mage::app();

$customerId = 3349;//52577;

$wishlistLogFile = "wishlist_sku_update_c_to_x.log";
$counterDeleted=0;
$counterProcessed=0;
try{
    $START_CHAR = "C";  //sku starts at "C" letter.
    $NEW_START_CHAR = "X";
    
    //get wishlist model by using customer
    //$wishlist   = Mage::getModel("wishlist/wishlist")->load($customerId,"customer_id");
    $wishlistCollection   = Mage::getModel("wishlist/wishlist")->getCollection();
    foreach ($wishlistCollection as $wishlist){
        
        $wishlistId = $wishlist->getId();
        
        //get wishlist item collection
        $collection = Mage::getModel("wishlist/item")->getCollection()
        ->addFieldToFilter("wishlist_id",$wishlistId);
        
        foreach ($collection as $wishlistItem){
            try{
                $wishlistItemId = $wishlistItem->getId();
                $product = $wishlistItem->getProduct();
                //echo "<pre>"; print_r($product->getOptions());
                $existingOptions = array();
                foreach ($product->getOptions() as $options){
                    foreach ($options->getValues() as $option){
                        $existingOptions[$option['option_type_id']] = $option['title']; //array($option['option_id']=>$option['option_type_id']);
                    }
                }
                
                $sku = $product->getSku();
                $firstLetter = substr($sku,0,1);
                
                $oldProductId = $product->getId();
                if($firstLetter == $START_CHAR){
                    Mage::log("sku = ".$sku,Zend_Log::DEBUG,$wishlistLogFile,true);
                    $newSku = $NEW_START_CHAR . substr($sku, 1);
                    $parentProductId = Mage::getModel("catalog/product")->getIdBySku($newSku);
                    if($parentProductId){
                        $wishlistItem->setProductId($parentProductId)->save();
                        $parentProduct = Mage::getModel("catalog/product")->load($parentProductId);
                        $itemOptionCollection = Mage::getModel("wishlist/item_option")->getCollection()
                        ->addFieldToFilter("wishlist_item_id",$wishlistItemId);
                        //echo "<pre>"; print_r($parentProduct->getOptions());
                        
                        $newOptionsArr = array();
                        foreach ($parentProduct->getOptions() as $options){
                            foreach ($options->getValues() as $option){
                                $newOptionsArr[$option['title']] = array($option['option_id']=>$option['option_type_id']);
                            }
                        }
                        
                        $selectedOption = array();
                        $existingKey = "";
                        foreach ($itemOptionCollection as $itemOption){
                            $code = $itemOption->getData("code");
                            $value = $itemOption->getData("value");
                            if($code == "info_buyRequest"){
                                $dvalue = unserialize($value);
                                
                                if($dvalue['cpid']){
                                    $dvalue['cpid'] = $parentProduct->getId();
                                    if($dvalue['product']){
                                        $childPro = Mage::getModel('catalog/product')->load($dvalue['product']);
                                        $newSkuChild1 = $NEW_START_CHAR . substr($childPro->getSku(), 1);
                                        $childProductId1 = Mage::getModel("catalog/product")->getIdBySku($newSkuChild1);
                                        $dvalue['product'] = $childProductId1;
                                    }
                                }else{
                                    $dvalue['product'] = $parentProduct->getId();
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
                                $itemOption->setProductId($parentProduct->getId());
                                
                            }elseif ($code == "option_ids"){
                                foreach ($selectedOption as $key => $val){
                                    $itemOption->setValue($key);
                                    
                                    $wishListOptionCollection1 = Mage::getModel('wishlist/item_option')->getCollection();
                                    $wishListOptionCollection1->addFieldToFilter('wishlist_item_id', array(
                                        'eq' => $wishlistItemId
                                    ));
                                    $wishListOptionCollection1->addFieldToFilter('code', array(
                                        'eq' => 'option_'.$existingKey
                                    ));
                                    if($wishListOptionCollection1->getSize()){
                                        $model = $wishListOptionCollection1->getFirstItem();
                                        $model->setProductId($parentProduct->getId())
                                        ->setValue($val)
                                        ->setCode('option_'.$key)->save();
                                    }
                                }
                                $itemOption->setProductId($parentProduct->getId());
                            }elseif ($code == "simple_product"){
                                $childProduct = Mage::getModel("catalog/product")->load($value);
                                $childSku = $childProduct->getSku();
                                $newSkuChild = $NEW_START_CHAR . substr($childSku, 1);
                                $childProductId = Mage::getModel("catalog/product")->getIdBySku($newSkuChild);
                                $itemOption->setValue($childProductId);
                                $itemOption->setProductId($childProductId);
                                
                                $wishListOptionCollection1 = Mage::getModel('wishlist/item_option')->getCollection();
                                $wishListOptionCollection1->addFieldToFilter('wishlist_item_id', array(
                                    'eq' => $wishlistItemId
                                ));
                                $wishListOptionCollection1->addFieldToFilter('code', array(
                                    'eq' => 'product_qty_'.$value
                                ));
                                if($wishListOptionCollection1->getSize()){
                                    $model = $wishListOptionCollection1->getFirstItem();
                                    $model->setProductId($childProductId)
                                    ->setCode('product_qty_'.$childProductId)->save();
                                }
                            }
                            $counterProcessed++;
                            $itemOption->save();
                            Mage::log($counterProcessed."-Data is updated".$sku,Zend_Log::DEBUG,$wishlistLogFile,true);
                        }
                    }else {
                        $counterDeleted++;
                        Mage::log($counterDeleted."-XSKU NOT FOUD FOR::".$sku,Zend_Log::DEBUG,'WL_missing_sku.log',true);
                        Mage::log("Wishlist ID::".$wishlistId."Wishlist ITEMID::".$wishlistItemId,Zend_Log::DEBUG,'WL_missing_sku.log',true);
                        $wishlistItem->delete();
                    }
                }
                
            } catch (Exception $e){
                Mage::log("Exc: ".$e->getMessage(),Zend_Log::DEBUG,$wishlistLogFile,true);
            }
        }
    }
}catch (Exception $e){

    Mage::log("Exception: ".$e->getMessage(),Zend_Log::DEBUG,$wishlistLogFile,true);
}

echo "Deleted::".$counterDeleted ." Processed::".$counterProcessed;



