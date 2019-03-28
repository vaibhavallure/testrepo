<?php
/**
 * Created by allure.
 * User: adityagatare
 * Date: 13/11/18
 * Time: 11:17 PM
 */

class Allure_HarrodsInventory_Model_Observer {
    public function checkHarrodsPrice($observer) {
        $product = $observer->getEvent()->getProduct();

        $productId=$product->getId();
        $harrodsprice=(float)$product->getHarrodsPrice();

        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');


        $query="SELECT * FROM `allure_harrodsinventory_price` WHERE  `productid`={$productId}";
        $result = $readConnection->fetchAll($query);
        $savedPrice=current($result)['price'];

        if($result)
        {
            if((float)$savedPrice!=$harrodsprice) {

                $updateQuery = "UPDATE `allure_harrodsinventory_price` SET `price`={$harrodsprice},`updated_date`=(now()) WHERE `productid`={$productId}";

                try {
                    $writeAdapter->query($updateQuery);
                    $writeAdapter->commit();


                } catch (Exception $e) {
                    Mage::helper("harrodsinventory")->add_log("Exception -:" . $e->getMessage());
                }
            }
        }
        else
        {
            Mage::helper("harrodsinventory")->add_log("price not found in table");

            if($harrodsprice) {
                $insertQuery = "INSERT INTO `allure_harrodsinventory_price`(`productid`, `price`) VALUES ({$productId},{$harrodsprice})";
                Mage::helper("harrodsinventory")->add_log("price inserted into table");

                try {
                    $writeAdapter->query($insertQuery);
                    $writeAdapter->commit();


                } catch (Exception $e) {
                    Mage::helper("harrodsinventory")->add_log("Exception -:" . $e->getMessage());
                }
            }
        }


    }
}