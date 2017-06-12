<?php

//@GC
class Ecp_Category_Block_Category extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    public function __construct() {
        parent::__construct();
    }

    public function _prepareLayout() {
        //$this->getLayout()->getBlock('left')->unsetChildren(); //remove left nav
        //$this->getLayout()->getBlock('right')->unsetChildren(); //remove right nav
        
        $content = $this->getLayout()->getBlock('content');
        $content->unsetChildren();
        
        $categoryLayeredNavigationBlock = $this->getLayout()->createBlock(
                'Ecp_Category_Block_Category_Product_Layered_Navigation', 
                'ecp_category.category_product_layered_navigation', 
                array('template' => 'ecp/category/product/layered/navigation.phtml')
        );
        
        $categoryProductViewBlock = $this->getLayout()->createBlock(
                'Ecp_Category_Block_Category_Product_View', 
                'ecp_category.category_product_view', 
                array('template' => 'ecp/category/product/view.phtml')
        );
        
        $content->append($categoryLayeredNavigationBlock);
        $content->append($categoryProductViewBlock);

        return parent::_prepareLayout();
    }

    protected function _toHtml() {
        return parent::_toHtml();
    }

}