<?php

class Allure_Category_Helper_Data extends Mage_Core_Helper_Abstract
{
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