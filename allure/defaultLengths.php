<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');

//18-Earlobe,20-Helix,23-Earhead,21-Trugs,25-Conch,22-Tash-rook
$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('attribute_set_id',array(18,20,23,21,25,22));
$collection->addAttributeToFilter('type_id','configurable');
//var_dump(count($collection));

foreach ($collection as $product){
    $product=Mage::getModel('catalog/product')->load($product->getId());
    foreach ($product->getOptions() as $value) {
            if($value->getTitle()=='Post Length'){
            $tempCounter=1;
            $valuesArray = array();
            $values = $value->getValues();
            
          /*   if(count($values) <= 1)
                continue; */
            foreach ($values as $valueopt) {
                //Trugs
                if (((strpos($valueopt->getTitle(), '6.5') !== false)) && ($product->getAttributeSetId()==21))           
                {
                    $valuesArray[$valueopt->getId()]['sort_order']=0;
                    
                }elseif (((strpos($valueopt->getTitle(), '6.5') !== false)) && ($product->getAttributeSetId()==18)){
                    $valuesArray[$valueopt->getId()]['sort_order']=0;
                }elseif (((strpos($valueopt->getTitle(), '6.5') !== false)) && ($product->getAttributeSetId()==20)){
                    $valuesArray[$valueopt->getId()]['sort_order']=0;
                }elseif (((strpos($valueopt->getTitle(), '5') !== false)) && ($product->getAttributeSetId()==23)){
                    $valuesArray[$valueopt->getId()]['sort_order']=0;
                }elseif (((strpos($valueopt->getTitle(), '8') !== false)) && ($product->getAttributeSetId()==25)){
                    $valuesArray[$valueopt->getId()]['sort_order']=0;
                }elseif (((strpos($valueopt->getTitle(), '6.5') !== false)) && ($product->getAttributeSetId()==22)){
                    $valuesArray[$valueopt->getId()]['sort_order']=0;
                }else {
                    $valuesArray[$valueopt->getId()]['sort_order']=$tempCounter;
                    $tempCounter++;
                }
                $valuesArray[$valueopt->getId()]['option_type_id']=$valueopt->getId();
                $valuesArray[$valueopt->getId()]['title']=$valueopt->getTitle();
                $valuesArray[$valueopt->getId()]['price_type']= 'fixed';
                $valueopt->setValues($valuesArray);
                $valueopt->saveValues();
                
            }
            Mage::log($product->getId().'-'.$product->getSku(),Zend_log::DEBUG,'defaultlenths.log',true);
            $product->save();
        }
    }
}
echo "FInished";
//echo $collection->getSelect();