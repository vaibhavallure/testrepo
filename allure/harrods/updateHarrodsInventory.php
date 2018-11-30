<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

//
//$lower = $_GET['lower'];
//$upper = $_GET['upper'];
//
//if(empty($lower) || empty($upper)){
//    die('Please add Upper and Lower limit');
//}



try {
    $collection = Mage::getModel("catalog/product")->getCollection();
    $collection->addAttributeToFilter('status', array('eq' => 1));
    $collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));


//    $collection->addAttributeToFilter('entity_id',
//        array('gteq' => $lower))
//        ->addAttributeToFilter('entity_id', array('lteq' => $upper));



    foreach ($collection as $_product) {

        $_product = Mage::getSingleton("catalog/product")->load($_product->getId());

        $parentHarrodsInventory = 0;

        if ($_product->getTypeId() == 'configurable') {
            $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
            $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addAttributeToFilter('harrods_inventory', array('gt' => 0))->addFilterByRequiredOptions();

            foreach ($simple_collection as $k => $simpleProd) {
                $simple_product = Mage::getModel("catalog/product")->load($simpleProd->getId());


                $parentHarrodsInventory += $simple_product->getHarrodsInventory();


            }
        }

        if ($parentHarrodsInventory != 0 && $_product->getHarrodsInventory() != $parentHarrodsInventory) {
            $_product->setHarrodsInventory($parentHarrodsInventory);
            $_product->save();
        }
    }

}catch(Exception $e)
{
    echo $e->getMessage();
}
die("Finished");

