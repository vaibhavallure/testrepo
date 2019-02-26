<?php
class Allure_BrownThomas_Helper_Data extends Mage_Core_Helper_Abstract
{


    private function config() {
        return Mage::helper("brownthomas/config");
    }
    private function cron() {
        return Mage::helper("brownthomas/cron");
    }

    public function add_log($message) {
		if (!$this->config()->getDebugStatus()) {
            return;
    	}
        Mage::log($message,Zend_log::DEBUG,"brownthomas_files.log",true);
    }

    public function getAttributeId($attribute_code)
    {
        $attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
        $attribute = $attribute_details->getData();
        return $attrbute_id = $attribute['attribute_id'];
    }

    public function  charEncode($str)
    {
        if(!empty($str))
        return mb_convert_encoding($str,"Windows-1252","UTF-8");
    }


}
