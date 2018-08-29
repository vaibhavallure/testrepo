<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

//$product = Mage::getModel('catalog/product')->load(12);

$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id','configurable');
$count=0;

foreach ($collection as $product){
$product=Mage::getModel('catalog/product')->load($product->getId());
if ($product->getPostLengthRequired()) {
       $count++;
        foreach ($product->getOptions() as $value) {
            
            if ($value->getTitle() == 'Post Length') {
                $value->delete();
            }
        }
                $optionValues = array();
                
                
                // 18-Earlobe
                $optionValues = array(
                    array(
                        'title' => '5mm',
                        'price' => 0,
                        'price_type' => 'fixed',
                        'sku' => '',
                        'sort_order' => 1
                    ),
                    array(
                        'title' => '6.5mm',
                        'price' => 0,
                        'price_type' => 'fixed',
                        'sku' => '',
                        'sort_order' => 2
                    ),
                    array(
                        'title' => '8mm',
                        'price' => 0,
                        'price_type' => 'fixed',
                        'sku' => '',
                        'sort_order' => 3
                    )
                );
                
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
                
                $product->setProductOptions(array(
                    $options
                ));
                $product->setCanSaveCustomOptions(true);
                
                $product->save();
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($product->getAttributeSetId());
                $attributeSetName = $attributeSetModel->getAttributeSetName();
                Mage::log($count.'----'.$product->getId() . '-' . $product->getSku() . '------' . $attributeSetName, Zend_log::DEBUG, 'defaultlenths.log', true);
}else {
    foreach ($product->getOptions() as $value) {
        if ($value->getTitle() == 'Post Length') {
            $value->delete();
        }
    }
}
}
die("Finished");
