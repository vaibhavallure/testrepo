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
$valuesText = explode(',', 'NOT LOGGED IN,General,Retailer,Press');
$valuesIds = array_map(array($sourceModel, 'getOptionId'), $valuesText);

$skustring="XMQD735P32D,XCAPIVDR,XCHPEF75D,KLPKM,XGALOTIV42DR,XET3SPLSL8D,XET3SPLSL65D,XET1SPLSL8D,XBARTS18R,XBARAPIV18DR,XBARAPIV11DR,ECLBARCCRIVD18D,XBARTS11R,XIVTG4D,XIVTG3D,XIVTG25D,JSPL1C20,JSP1C40,JPE53SC31C32D,XIVTG5D,XETHM65D,XETHIVM65D,KPKM,JSPL1C40,JPE531C40D,JPE531C20D,XMQD84P32DR,XMQ3D84P32DR,JSP1C20,XET1SPSL8D,XSLEG8DR,XET1SPLSL65D,XSL8DR,VLOT6CLD9D,EIVTG4D,XCHMQF735D,XCHMQF63D,XBARCRIVD11DR,XBARCRIVD18DR,XDRPCHDD,XCHPEF64D,XCHPEF43D,XDRPR2D,J2J3C76,J2J2SC1C76D,J2J2CH76,J2J1CH76,ECLBARCTS18,EBARTS11,EBARCRIVD11D,EBARAPIV18D,EBARAPIV11D,XSLIV55D,ESLIV55D,XET1SPSL65D,EHLOTD,K2HC,XETTSF65D,XCHFR3FP75D,X1EB1CH22,XCHP53D,XCHP75D,XET65D,XETIV5D,XETIV65D,X2EB1SC2D";

$sku_list = explode(",",$skustring);

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


