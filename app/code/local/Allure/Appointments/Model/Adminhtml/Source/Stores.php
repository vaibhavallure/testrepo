<?php
class Allure_Appointments_Model_Adminhtml_Source_Stores
{
    public function toOptionArray() {
        $stores = array();
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $stores = $this->customToptionArray();
        }else{
            $stores = $this->defaultToOptionArray();
        }
        return $stores;
    }
    
    /**
     * Default Store option array data
     */
    public function defaultToOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')
        ->getStoreValuesForForm(false, false);
    }
    
    /**
     * By using virtual store
     * retrive store data
     */
    public function customToptionArray(){
        return Mage::getSingleton('allure_virtualstore/adminhtml_store')
        ->getStoreValuesForForm();
    }
}