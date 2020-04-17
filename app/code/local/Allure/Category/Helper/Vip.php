<?php

class Allure_Category_Helper_Vip extends Mage_Core_Helper_Abstract
{
    protected $_categories;

    public function getVipCategories(){
        $category_list = array();
        $categories = Mage::getModel('catalog/category')->getCollection();
        if($this->vipAttributePresent()) {
            $categories->addAttributeToFilter('vip_category', 1);
            if($categories->getSize() > 0){
                foreach ($categories as $category){
                    $category_list[] = $category->getId();
                }
            }

        }
        $this->_categories = $category_list;
    }

    protected function vipAttributePresent(){
        $catalogSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
        return $catalogSetup->getAttribute('catalog_category', 'vip_category', 'attribute_id');
    }

    public function getLabel($_product,$default_label = 'Add to Bag'){
        $this->getVipCategories();
        $label = $default_label;
        if($_product->getTypeId() == 'simple'){
            $parent = $this->getParentIds($_product);
            if(!empty($parent))
                $_product =  $parent;
        }
        $categoryIds = $_product->getCategoryIds();

        if(!empty($this->_categories) && !empty($categoryIds)) {
            try {
                foreach ($this->_categories as $category){
                    if(in_array($category,$categoryIds))
                        return 'Pre-Order';
                }
            } catch (Exception $ex) {
                $this->writeLog('Exception' . $ex->getMessage() . ' Product :' . $_product->getId());
            }
        }
        return $label;

    }

    protected function writeLog($message){
        Mage::log($message,Zend_Log::DEBUG,'vip_category.log',true);
    }

    protected  function getParentIds($_product){
        $parentIdArray = Mage::getModel('catalog/product_type_configurable')
            ->getParentIdsByChild($_product->getId());
        $_parent = '';
        if(isset($parentIdArray[0])){
            $_parent = Mage::getModel('catalog/product')->load($parentIdArray[0]);
        }
        return $_parent;
    }

}