<?php

class Allure_Category_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const XML_URL_REWRITE_CATEGORY_IDS                  = 'allure/category_url/category_ids';
    const XML_ALLOW_PARENT_CATEGORY_IN_URL          = 'allure/category_url/allow_parent_category';
    const XML_ALLOW_SUB_PARENT_CATEGORY_IN_URL      = 'allure/category_url/allow_subparent_category';
    
    public function isAllowCategoryForCustomUrlChanges($categoryId)
    {
        $categories = Mage::getStoreConfig(self::XML_URL_REWRITE_CATEGORY_IDS);
        $categoriesArr = explode(",", $categories);
        return in_array($categoryId, $categoriesArr);
    }
    
    public function isAllowParentCategoryInUrl(){
        return Mage::getStoreConfig(self::XML_ALLOW_PARENT_CATEGORY_IN_URL);
    }
    
    public function isAllowSubParentCategoryInUrl(){
        return Mage::getStoreConfig(self::XML_ALLOW_SUB_PARENT_CATEGORY_IN_URL);
    }
    
    public function getOptionNumber($metalName){
        
        $productModel = Mage::getModel('catalog/product');
        $str_attr_label='category_postlengths ';
     
        $attr = $productModel->getResource()->getAttribute($str_attr_label);
        $optionsValue = $attr->getSource()->getOptionId($metalName);
        return $optionsValue;
        
    }
    public function getOptionText($optiomId){
        
        $productModel = Mage::getModel('catalog/product');
        $str_attr_label='category_postlengths ';
        
        $attr = $productModel->getResource()->getAttribute($str_attr_label);
        $optionsText = $attr->getSource()->getOptionText($optiomId);
        
        return $optionsText;
        
    }
    public function getTitles($titles=array()){
        
        $productModel = Mage::getModel('catalog/product');
        $str_attr_label='category_postlengths ';
        $optionsText=array();
        $attr = $productModel->getResource()->getAttribute($str_attr_label);
        foreach ($titles as $titleId){
            $optionsText[] = $attr->getSource()->getOptionText($titleId);
        }
        return $optionsText;
        
    }
}