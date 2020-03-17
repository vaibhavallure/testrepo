<?php
class Allure_PromoBox_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCategoryName($category_id)
    {
        $_category = Mage::getModel('catalog/category')->load($category_id);
        return $_category->getName();
    }
}