<?php
class Allure_HarrodsInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function harrodsConfig(){
        return Mage::helper("harrodsinventory/config");
    }

    public function add_log($message){
       if (!$this->harrodsConfig()->getDebugStatus()) {
            return;
           }
        Mage::log($message,Zend_log::DEBUG,"update_harrods_inventory.log",true);
    }

}
	 