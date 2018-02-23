<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

echo "<pre>";


$lower = $_GET['lower'];
$upper= $_GET['upper'];


if(empty($lower) || empty($upper)){
    die('Please add Upper and Lower limit');
}


$atributeCode = 'metal_color';
$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
$optionsMetal = $attribute->getSource()->getAllOptions();


$quotes=Mage::getModel('sales/quote')->getCollection()
->addFieldToFilter('customer_id', array('neq' =>0))
->addFieldToFilter('is_active', array('eq' => 1));

$quotes->addFieldToFilter('entity_id',
    array(
        'gteq' => $lower
    ));

$quotes->addFieldToFilter('entity_id', array(
    'lteq' => $upper
));



$count=1;
$fixedItems = array("ZSTH","XWB15BKD","XWB10D","XWB10BKD","XTHMQD","XTHD4","XTHD2","XTHBF6","XTHBF2D","XTHBF25D","XTHBF","XTHBAD","XSNPA10RB","XSNPA10DRB","XSNPA10D","XSNEG5RB","XSNEG5D","XSNEG5BKD","XSNEG10RB","XSNEG10D","XSNEG10BKD","XSN65BKD","XSN5RB","XSN5D","XSN5BKD","XSN10RB","XSN10D","XSN10BKD","XG5OP","XDS5DOP","XDS5D","XDGTR65RB","XDGTR65OP","XDGTR65D","XDGTR65BKD","XDG65D","XDG65BKD","XDG5D","ESN10RB","CWB10D_T","CWB10D_R","CWB10D_E","CWB10D_C","CWB10D_B","CWB10D","CWB10BKD_T","CWB10BKD_R","CWB10BKD_E","CWB10BKD_C","CWB10BKD_B","CWB10BKD","CTHBF_T","CTHBF_R","CTHBF_E","CTHBF_C","CTHBF_B","CTHBF","CSNPA10DRB_T","CSNPA10DRB_R","CSNPA10DRB_E","CSNPA10DRB_C","CSNPA10DRB_B","CSNPA10DRB","CSNPA10D_T","CSNPA10D_R","CSNPA10D_E","CSNPA10D_C","CSNPA10D_B","CSNPA10D","CSNEG5RB_T","CSNEG5RB_R","CSNEG5RB_E","CSNEG5RB_C","CSNEG5RB_B","CSNEG5RB","CSNEG5D_T","CSNEG5D_R","CSNEG5D_E","CSNEG5D_C","CSNEG5D_B","CSNEG5D","CSNEG10D_T","CSNEG10D_R","CSNEG10D_E","CSNEG10D_C","CSNEG10D_B","CSNEG10D","CSNEG10BKD_T","CSNEG10BKD_R","CSNEG10BKD_E","CSNEG10BKD_C","CSNEG10BKD_B","CSNEG10BKD","CSN5RB_T","CSN5RB_R","CSN5RB_E","CSN5RB_C","CSN5RB_B","CSN5RB","CSN5BKD_T","CSN5BKD_R","CSN5BKD_E","CSN5BKD_C","CSN5BKD_B","CSN5BKD","CSN10RB_T","CSN10RB_R","CSN10RB_E","CSN10RB_B","CSN10RB","CSN10D_T","CSN10D_R","CSN10D_E","CSN10D_C","CSN10D_B","CSN10D","CSN10BKD_T","CSN10BKD_R","CSN10BKD_E","CSN10BKD_C","CSN10BKD_B","CSN10BKD","CG5OP","CDS5DOP_R","CDS5DOP_C","CDS5DOP_B","CDS5DOP","CDGTR65RB_T","CDGTR65RB_R","CDGTR65RB_E","CDGTR65RB_C","CDGTR65RB_B","CDGTR65RB","CDGTR65OP_E","CDGTR65OP_C","CDGTR65OP_B","CDGTR65OP","CDGTR65D_T","CDGTR65D_R","CDGTR65D_E","CDGTR65D_C","CDGTR65D_B","CDGTR65D","CDGTR65BKD_R","CDGTR65BKD_C","CDGTR65BKD_B","CDGTR65BKD","CDG65D_T","CDG65D_R","CDG65D_C","CDG65D_B","CDG65D","CDG65BKD_E","CDG65BKD_C","CDG65BKD_B","CDG65BKD","CDG5D_B","CDG5D","CSN5D_T","CSN5D_R","CSN5D_E","CSN5D_C","CSN5D_B","CSN5D","XWB15D");


foreach ($quotes as $quote){
    
    foreach ($quote->getAllItems() as $item){
      
        $oldItem = $item->getSku();
        
        $oldItemSku = explode('|', $oldItem);
        
        $parentItem = $oldItemSku[0];
        $post_length = $oldItemSku[2];
        
        if (count($oldItemSku) ==  4 && in_array($parentItem, $fixedItems)) {
            
           
            
            $newItem = implode('|', array($parentItem, $oldItemSku[1], $oldItemSku[3]));
            
            if (empty($post_length)) {
                var_dump("Post Length: NONE");
                continue;
            }
          
            $oldItemId = Mage::getModel('catalog/product')->getIdBySku($oldItem);
            $newItemId = Mage::getModel('catalog/product')->getIdBySku($newItem);
            $parentItemId = Mage::getModel('catalog/product')->getIdBySku($parentItem);
           
          
            
            
            if($buyRequestObj=$item->getProduct()->getCustomOption('info_buyRequest')){
                $buyRequest= unserialize($buyRequestObj->getValue());
                //configurable product
               
                $colorLabel=$oldItemSku[1];
                
                $lengthLabel= $oldItemSku[2];
                
                $configProduct=Mage::getModel('catalog/product')->load($parentItemId);
                $configProductId=$configProduct->getId();
                
                //simple product without length
                
                $withoutPostlengthProduct=Mage::getModel('catalog/product')->load($newItemId);
                
                
                if($withoutPostlengthProduct->getId()){
                   
                        if(isset($configProductId)&& !empty($configProductId))
                            $buyRequest['product']=$configProduct->getId();  //set as configurable product
                        if(isset($buyRequest['cpid']))
                            unset($buyRequest['cpid']);
                                
                      
                        $superAttribute=$buyRequest['super_attribute'];
                        
                        foreach ($superAttribute as $key=>$value){
                            if($key==189){  // Post lengths
                                unset($superAttribute[$key]);
                            }
                            
                        }
                        
                        if (!in_array(209, $superAttribute)){ //check metel color is prsent in super_attribute
                            foreach ($optionsMetal as $options) {
                                //if (preg_match("/.'$colorLabel'./", $options['label'])) {
                                if(strtolower($colorLabel) == strtolower($options['label'])){
                                    $superAttribute[209]=$options['value'];
                                    break;
                                }
                            }
                        }
                        
                        $buyRequest['super_attribute']=$superAttribute;
                        
                        $temp = array();
                        $temp['attributes'] = serialize($buyRequest['super_attribute']);
                        
                        $optionsArray=$buyRequest['options'];
                        if(!isset($optionsArray) || empty($optionsArray)){
                            foreach ($configProduct->getOptions() as $optionsValue){
                                foreach($optionsValue->getValues() as $value) {
                                    $lengthLabel=strtolower($lengthLabel);
                                    $title=strtolower($value['title']);
                                    if(preg_match("/.$lengthLabel./", $title))
                                   // if(strtolower($lengthLabel)==strtolower($value['title']))
                                    {
                                        $optionsArray[$value['option_id']]=$value['option_type_id'];
                                        break;
                                    }
                                }
                                if(empty($optionsArray)){
                                    foreach($optionsValue->getValues() as $value) {
                                            $optionsArray[$value['option_id']]=$value['option_type_id'];
                                            break;
                                    }
                                }
                            }
                        }
                        
                        if(isset($optionsArray) && !empty($optionsArray))
                            $buyRequest['options']=$optionsArray;
                            
                        foreach ($optionsArray as $key=>$value){
                                $temp['option_ids'] = $key;
                                $tkey = 'option_'.$key;
                                $temp[$tkey] = $value;
                        }
                            
                        echo $count.'-item id::'. $item->getItemId()."-----".'Email::'.$quote->getCustomerEmail();
                        echo "<br>";
                            
                        $item->setSku($withoutPostlengthProduct->getSku());
                        $item->setProductId($configProduct->getId());
                        $item->setProductType('configurable');
                        $item->setName($configProduct->getName());
                            
                            try {
                                $buyRequestObj->setValue(serialize($buyRequest))->save();
                                
                                foreach ($temp as $key => $tData){
                                    
                                    $itemOptionColl = Mage::getModel('sales/quote_item_option')->getCollection();
                                    $itemOptionColl->addFieldToFilter('item_id', array('eq' =>$item->getItemId()));
                                    $itemOptionColl->addFieldToFilter('code', $key);
                                    if(count($itemOptionColl)<=0){
                                        $tOption = Mage::getModel("sales/quote_item_option");
                                        $tOption->setItem($item)
                                        ->setProductId($configProduct->getId())
                                        ->setCode($key)
                                        ->setValue($tData)
                                        ->save();
                                        $tOption=null;
                                    }
                                    $itemOptionColl=null;
                                    
                                }
                                $itemOptionColl1 = Mage::getModel('sales/quote_item_option')->getCollection();
                                $itemOptionColl1->addFieldToFilter('item_id', array('eq' =>$item->getItemId()));
                                $itemOptionColl1->addFieldToFilter('code', 'info_buyRequest');
                                if(!empty($itemOptionColl1->getFirstItem()))
                                    $itemOptionColl1->getFirstItem()->setProductId($configProduct->getId())->save();
                                
                                $item->save();
                                
                                Mage::log($count."-item id::". $item->getItemId()."-----".'Email::'.$quote->getCustomerEmail(),Zend_log::DEBUG,'parentchild_cartupdate_3d.log',true);
                                
                            } catch (Exception $e) {
                                var_dump("Exception Occured:".$e->getMessage());
                                Mage::log($quote->getCustomerEmail()."---Exception Occured:".$e->getMessage(),Zend_log::DEBUG,'parentchild_cartupdate_3d-error.log',true);
                            }
                            $count++;
                            
                           
                }  //End if of without postlenth
                else{
                    Mage::log("No new item find related to sku::".$newItem,'parentchild_cartupdate_3d-error.log',true);
                }
                
            }
       
        }  
       
    }
}

echo "Done";
die;
