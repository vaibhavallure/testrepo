<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


$row = 1;
if (($handle = fopen("barcodemapping.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        $row++;
        if($row==2)
            continue;



        Mage::log("checking for :".$data[0]." - ".$data[2],7,"brownthomas_update.log",true);

        echo "checking for :".$data[0]." - ".$data[2]."\n";

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('barcode')
            ->addAttributeToFilter('barcode',array('eq'=>$data[2]));

        if($products->getSize()) {
            foreach ($products as $product) {
            try {
                $product=Mage::getModel("catalog/product")->load($product->getId());
                $product->setBrownthomasBarcode($data[1]);
                $product->save();
                Mage::log("BrownthomasBarcode set successfully", 7, "brownthomas_update.log", true);
                echo "BrownthomasBarcode set successfully\n";

            } catch (Exception $e) {
                Mage::log($e->getMessage(), 7, "brownthomas_update.log", true);
                echo $e->getMessage()."\n";
            }
        }
        }
        else{
            Mage::log("product not found", 7, "brownthomas_update.log", true);
            echo "product not found\n";
        }

//
//
//        var_dump($products->getSelect()->__toString());
//
//        var_dump($products->getSize());
        if($row==20)
        {
            break;
        }
    }
    fclose($handle);
}





