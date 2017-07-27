<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$name='wrong_attributeproducts.csv';
$io = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'export' . DS;
$file = $path . DS . $name;
$io->setAllowCreateFolders(true);
$io->open(array('path' => $path));
$io->streamOpen($file, 'w+');
$io->streamLock(true);
$header = array("Id","sku","Name","Attr. Set Name");
$io->streamWriteCsv($header);
$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));


foreach ($collection as $product){
    
    $configProduct = Mage::getModel('catalog/product')->load($product->getId());
    $allAtributes = $configProduct->getTypeInstance(true)->getConfigurableAttributes($configProduct);
    foreach ($allAtributes as $attribute) {
        $productAttribute = $attribute->getProductAttribute();
        if(is_null($productAttribute)){
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($configProduct->getAttributeSetId());
            $attributeSetName  = $attributeSetModel->getAttributeSetName();
            $data = array("Id"=>$product->getId(),"sku"=>$product->getSku(),"Name"=>$configProduct->getName(),"Attr. Set Name"=>$attributeSetName);
            $io->streamWriteCsv($data);
            Mage::log($product->getId(),Zend_Log::DEBUG,'abc',true);
            Mage::log($product->getSku(),Zend_Log::DEBUG,'abc',true);
            Mage::log("******************************************",Zend_Log::DEBUG,'abc',true);
            break;
        }
       
    }
}

echo "Done";


