<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id','configurable');
$counter=0;
foreach ($collection as $product){
    $product=Mage::getModel('catalog/product')->load($product->getId());
    $values = array();
    $p=array();
    foreach ($product->getOptions() as $o) {
        if($o->getTitle()=='Post Length'){
            $p = $o->getValues();
        }
    }
    if(!empty($p)){
        foreach($p as $v)
        {
            $values[$v->getId()]['option_type_id']= $v->getId();
            $values[$v->getId()]['title']= str_replace("MM","mm",$v->getTitle());
            $values[$v->getId()]['price']= 0;
            $values[$v->getId()]['price_type']= 'fixed';
            $values[$v->getId()]['sku']= '';
            $values[$v->getId()]['sort_order']= $v->getSortOrder();
            
        }
        $v->setValues($values);
        $v->saveValues();
        $product->save();
        $counter++;
        Mage::log($counter."-POST LENGTH UPDATED::".$product->getId().' SKU:: '.$product->getSku(),Zend_log::DEBUG,'update_postlengths.log',true);
    }
    
}