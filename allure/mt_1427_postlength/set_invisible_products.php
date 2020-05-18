<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

/*Get group Ids*/
Mage::log('------------- Start ------------',Zend_Log::DEBUG,'setVisible.log',true);
$attrCode = 'allowed_group';
$sourceModel = Mage::getModel('catalog/product')->getResource()
    ->getAttribute($attrCode)->getSource();
$valuesText = explode(',', 'Wholesale');
$valuesIds = array_map(array($sourceModel, 'getOptionId'), $valuesText);

$sku_list = array('XTHBF',
    'XTHBF6',
    'XTHD4',
    'XTHD2',
    'ZPBF6R0',
    'XTHBF25D',
    'XTHBF2D',
    'XTHMQD');

$totalCount = 0;
foreach ($sku_list as $sku) {

    $collection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('sku', $sku)
        ->addAttributeToFilter('type_id', 'configurable');
    $foundCount = $collection->getSize();

    echo PHP_EOL . "SKU: " . $sku;
    echo PHP_EOL . "Count: " . $foundCount;

    if ($foundCount > 0) {

        $childrenCount = 0;
        $product = $collection->getFirstItem();
        $parentId = $product->getId();

        Mage::log('Parent SKU: '.$sku,Zend_Log::DEBUG,'setVisible.log',true);
        Mage::log('Parent Id. :'.$parentId,Zend_Log::DEBUG,'setVisible.log',true);

        /*Set Allowed group to parent*/
        $parentProduct = Mage::getModel('catalog/product')
            ->load($parentId);
        try {
            $parentProduct->setAllowedGroup($valuesIds);
            /*$parentProduct->save();*/
            $totalCount++;
        }catch (Exception $ex){
            echo 'Exception'.$ex->getMessage();
            Mage::log('Exception While Saving Parent:'.$ex->getMessage(),Zend_Log::DEBUG,'setVisible.log',true);
        }

        $childrenProducts = Mage::getModel('catalog/product_type_configurable')
            ->getChildrenIds($parentId);
        $cnt = 0;

        foreach ($childrenProducts as $set) {

            foreach ($set as $child) {
                /*Set Allowed group to childs*/
                $childProduct = Mage::getModel('catalog/product')
                    ->load($child);
                if ($childProduct) {
                    try {
                        $childProduct->setAllowedGroup($valuesIds);
                        /*$childProduct->save();*/
                        $totalCount++;
                    }catch (Exception $ex){
                        echo 'Exception'.$ex->getMessage();
                        Mage::log('Exception While Saving Child:'.$ex->getMessage(),Zend_Log::DEBUG,'setVisible.log',true);
                    }

                }
                $childrenCount++;

            }
        }

        Mage::log('Total Childrens: '.$childrenCount,Zend_Log::DEBUG,'setVisible.log',true);
        echo PHP_EOL . ' Child Count' . $cnt;
    } else {
        Mage::log('NOT FOUND :'.$sku,Zend_Log::DEBUG,'setVisible.log',true);
        echo "Product Not Found...!";
    }
}
Mage::log('Total Found: '.$totalCount,Zend_Log::DEBUG,'setVisible.log',true);
echo "Done";


