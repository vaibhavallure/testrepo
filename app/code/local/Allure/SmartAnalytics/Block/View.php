<?php
class Allure_SmartAnalytics_Block_View extends Mage_Core_Block_Template
{
    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function getProducts($_productIds)
    {
        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect(array('name','sku'))
            ->addAttributeToFilter('entity_id',array('in' => $_productIds))
            ->addUrlRewrite();
    }
}
