<?php
/**
 * @author allure
 */
class Allure_Virtualstore_Model_Observer
{	
	/**
	 * set store_id of order to custom column in
	 * order i.e old_store_id
	 */
    public function setDataToOrder($observer){
        try{
            $order = $observer->getOrder();
            $storeId = $order->getStoreId();
            $order->setOldStoreId($storeId)->save();
        }catch(Exception $e){
        }
    }
}
