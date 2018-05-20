<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$product = Mage::getModel('catalog/product')->load(12);

$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('attribute_set_id',array(18,20,23,21,25,22));
$collection->addAttributeToFilter('type_id','configurable');
foreach ($collection as $product){
$product=Mage::getModel('catalog/product')->load($product->getId());
foreach ($product->getOptions() as $value) {
    if($value->getTitle()=='Post Length'){
        $value->delete();
        $optionValues=array();
        
        //18-Earlobe,20-Helix,23-Earhead,21-Trugs,25-Conch,22-Tash-rook
        
        if ($product->getAttributeSetId()==21)
        {
            // 21-Trugs
            $optionValues = array(
                array(
                    'title' => '5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 1
                ),
                array(
                    'title' => '6.5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0
                ),
                array(
                    'title' => '8MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 2
                )
            );
            
        }elseif ($product->getAttributeSetId()==18){
            
            //18-Earlobe
            $optionValues = array(
                array(
                    'title' => '5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 1
                ),
                array(
                    'title' => '6.5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0
                ),
                array(
                    'title' => '8MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 2
                )
            );
            
        }elseif($product->getAttributeSetId()==20){
            
            //20-Helix
            $optionValues = array(
                array(
                    'title' => '5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 1
                ),
                array(
                    'title' => '6.5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0
                ),
                array(
                    'title' => '8MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 2
                )
            );
            
        }elseif ($product->getAttributeSetId()==23){
            
            //23-Earhead
            $optionValues = array(
                array(
                    'title' => '5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0
                ),
                array(
                    'title' => '6.5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 1
                )
            );
            
        }elseif ($product->getAttributeSetId()==25){
            //25-Conch
          
            $optionValues = array(
                
                array(
                    'title' => '6.5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 1
                ),
                array(
                    'title' => '8MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0
                )
            );
            
            
        }elseif ($product->getAttributeSetId()==22){
            //22-Tash-rook
          
            $optionValues = array(
                array(
                    'title' => '5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 1
                ),
                array(
                    'title' => '6.5MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0
                ),
                array(
                    'title' => '8MM',
                    'price' => 0,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 2
                )
            );
            
        }
        
     
        $options = array(
            'title' => 'Post Length',
            'type' => 'drop_down',
            'is_required' => 1,
            'sort_order' => 0,
            'values' => $optionValues
        );
        
        $optionInstance = $product->getOptionInstance()->unsetOptions();
        $product->setHasOptions(1);
        $optionInstance->setProduct($product);
        
        $product->setProductOptions(array($options));
        $product->setCanSaveCustomOptions(true);
        
        $product->save();
        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
        $attributeSetModel->load($product->getAttributeSetId());
        $attributeSetName  = $attributeSetModel->getAttributeSetName();
        Mage::log($product->getId().'-'.$product->getSku().'------'.$attributeSetName,Zend_log::DEBUG,'defaultlenths.log',true);
        
        }
    }
}
die("Finished");
