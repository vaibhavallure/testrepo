<?php
class Allure_SmartAnalytics_Block_List extends Mage_Core_Block_Template
{
    public function getProductCollection()
    {
       return $this->getLayout()->getBlockSingleton('catalog/product_list')->getLoadedProductCollection();
    }
}
