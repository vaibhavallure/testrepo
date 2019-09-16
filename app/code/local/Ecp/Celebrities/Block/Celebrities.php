<?php

/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Block_Celebrities extends Mage_Core_Block_Template {

    protected $_celebrity = null;
    
    public function __construct(){
        if(!Mage::registry('celebrities')) {
            $collection = Mage::getModel('ecp_celebrities/celebrities')->getCollection();
            $collection->getSelect()->order('ordering ASC');
            $collection->addFieldToFilter('status',1);

            Mage::register('celebrities',$collection->addFieldToFilter('status',1)->getFirstItem());
			$celebrity = $collection->addFieldToFilter('status',1)->getFirstItem();
			Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $celebrity->getUrl());
        }
    }

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getCelebrities() {
        if (!$this->_celebrity)
            $this->_celebrity = Mage::registry('celebrities');
        return $this->_celebrity;
    }

    public function getImageUrl($image) {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "celebrities/" . $image;
    }

    public function getCelebrityOutfits() {
        $celebrityOutfit = Mage::getModel('ecp_celebrities/outfits')->getCollection()->addFieldToFilter('celebrity_id', $this->_celebrity->getId())->addFieldToFilter('status',1);        
        Mage::register('celebritiesoutfit',$celebrityOutfit);
        
        return $celebrityOutfit;
        //return Mage::getModel('ecp_celebrities/outfits')->getCollection()->addFieldToFilter('celebrity_id', $this->_celebrity->getId());
    }

    public function loadProductsOutfit($productsString) {
        return Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('id', $productsString);
    }

    public function getAllOutfitProducts($outfit) {
        return Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('size_sample')
                ->addFieldToFilter('entity_id', explode(',', $outfit->getRelatedProducts()))
                ->addAttributeToFilter('visibility' , array('neq'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
    }

    public function getProductPriceBlock($product) {
        Mage::unregister('product');
        Mage::unregister('current_product');
        Mage::register('product',$product);
        Mage::register('current_product',$product);
        return $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_Price', 
                'price', 
                array('template' => 'catalog/product/price.phtml')
        );
    }
    
    public function getJsonConfig($product){
        $productView = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View', 
                'product_view', 
                array('template' => '')
        )->setProduct($product);
        
        return $productView->getJsonConfig();
    }
    
    public function getAttributesBlocks($product) {
        $options = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View_Type_Configurable', 
                'options_configurable', 
                array('template' => 'catalog/product/view/type/options/configurable.phtml')
        );
        
        $options->setProduct($product);
        return $options;
    }
    
    public function getAddToCartBlock($product){
        $addToCart = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View', 
                'options_configurable', 
                array('template' => 'catalog/product/view/addtocart.phtml')
        );
        return $addToCart;
    }
    
    public function getAddToBlock($product){
        $addToCart = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View', 
                'options_configurable', 
                array('template' => 'catalog/product/view/addto.phtml')
        );
        return $addToCart;
    }
    protected function getCelebritiesCol() {
        $collection = Mage::getModel('ecp_celebrities/celebrities')->getCollection();
        $collection->getSelect()->order('ordering ASC');
        $collection->addFieldToFilter('status',1);

        return $collection;
    }
}