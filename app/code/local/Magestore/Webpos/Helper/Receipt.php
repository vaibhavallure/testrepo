<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Helper_Receipt extends Mage_Core_Helper_Abstract {

	public function getStoreId() {
		return Mage::app()->getStore()->getId();
    }

    public function getStoreInformation() {
        $settings = array();
        $storeId = $this->getStoreId();
        $storePhone = Mage::getStoreConfig('general/store_information/phone', $storeId);
        $storeName = Mage::getStoreConfig('general/store_information/name', $storeId);
        $storeAddress = Mage::getStoreConfig('general/store_information/address', $storeId);
        $userData = $this->getUserData();
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            if(isset($userData['user_id'])){
                $pos_user_id = $userData['user_id'];
                $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                        ->addFieldToFilter('user_id', $pos_user_id);
                $warehouseId = $userCollection->getFirstItem()->getWarehouseId();
                $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
                $storeName = $warehouse->getData('warehouse_name');
                $street = $warehouse->getData('street');
                $city = $warehouse->getData('city');
                $country_id = $warehouse->getData('country_id');
                $postcode = $warehouse->getData('postcode');
                $storeAddress = $street . "," . $city . "," . $country_id . "," . $postcode;
            }
        }
        $settings['storeName'] = $storeName;
        $settings['storePhone'] = $storePhone;
        $settings['storeAddress'] = $storeAddress;
        return $settings;
    }

    public function getUserData() {
		$pos_user_id = Mage::getModel('webpos/session')->getUser()->getId();
		$pos_user = Mage::getModel('webpos/user')->load($pos_user_id);
		return $pos_user->getData();
    }

    public function getReceiptSettings() {
        $storeId = $this->getStoreId();
        $settings = array();
        $settings['date_format'] = Mage::getStoreConfig('webpos/receipt/date_format',$storeId);
        $settings['show_store_information'] = Mage::getStoreConfig('webpos/receipt/show_store_information',$storeId);
        $settings['show_cashier_name'] = Mage::getStoreConfig('webpos/receipt/show_cashier_name',$storeId);
        $settings['show_comment'] = Mage::getStoreConfig('webpos/receipt/show_comment',$storeId);
        $settings['show_barcode'] = Mage::getStoreConfig('webpos/receipt/show_barcode',$storeId);
        $settings['show_receipt_logo'] = Mage::getStoreConfig('webpos/receipt/show_receipt_logo',$storeId);
        $settings['show_shipping_method'] = Mage::getStoreConfig('webpos/receipt/show_shipping_method',$storeId);
        $settings['show_payment_method'] = Mage::getStoreConfig('webpos/receipt/show_payment_method',$storeId);
        $settings['header_text'] = htmlentities(Mage::getStoreConfig('webpos/receipt/header_text',$storeId));
        $settings['footer_text'] = htmlentities(Mage::getStoreConfig('webpos/receipt/footer_text',$storeId));
        $settings['font_type'] = Mage::getStoreConfig('webpos/receipt/font_type',$storeId);
        $settings['webpos_logo'] = Mage::getStoreConfig('webpos/general/webpos_logo',$storeId);
        return $settings;
    }

}
