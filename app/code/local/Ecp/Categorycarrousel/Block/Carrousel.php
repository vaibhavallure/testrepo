<?php
//Carrousel
class Ecp_Categorycarrousel_Block_Carrousel
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface{

    protected function  _prepareLayout() {
        $head = $this->getLayout()->getBlock('head');
        //$head->addJs('jquery/jquery.carouFredSel-6.1.0-packed.js');
        //$head->addJs('jquery/plugins/jquery.jcarousel.min.js');
        $head->addJs('jquery/jquery.jcarousel.min.js');
        return parent::_prepareLayout();
    }

    protected function _toHtml(){
	$this->setTemplate('categorycarrousel/carrousel.phtml');
	return parent::_toHtml();
    }

    protected function getCurrentCategoryId(){
        $layer = Mage::getSingleton('catalog/layer');
        $_category = $layer->getCurrentCategory();
        return $_category->getId();
    }

    protected function getCurrentCategoryName(){
        $_category = Mage::getModel('catalog/category')->load($this->getCurrentCategoryId());
        return $_category->getName();
    }

    protected function adjustcategoryLevel(){
        $value = $this->getData('category_level') + 1;
        return $value;
    }

    protected function getHrefUrl(){
        if(Mage::getStoreConfig('web/seo/use_rewrites') == 0){
            return 'index.php/';
        }else{
            return '';
        }
    }

    protected function getCategories(){
        $categories = array();
        $config_category_level = $this->adjustcategoryLevel();
        $category = Mage::getModel('catalog/category')->load($this->getCurrentCategoryId());
        $current_category_level = $category->getData('level');

        if($config_category_level == $current_category_level){
            $category = Mage::getModel('catalog/category')->load($category->getParentId());
        }
        return Mage::getModel('catalog/category')->getCollection()
                    ->addFieldToFilter('entity_id',explode(',',$category->getAllChildren()))
                    ->addFieldToFilter('level',$this->adjustcategoryLevel())
                    ->addFieldToFilter('is_active',1)
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('thumbnail')
                    ->addAttributeToSelect('url_path')
                    ->addAttributeToSelect('position')
                    ->setOrder('position');

    }

}
