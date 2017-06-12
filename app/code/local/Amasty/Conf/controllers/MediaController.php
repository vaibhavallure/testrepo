<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_MediaController extends Mage_Core_Controller_Front_Action
{
    protected function _initProduct()
    {
        Mage::dispatchEvent('catalog_controller_product_init_before', array('controller_action'=>$this));
        $productId  = (int) $this->getRequest()->getParam('id');

        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product'=>$product));
            Mage::dispatchEvent('catalog_controller_product_init_after', array('product'=>$product, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
    
    public function indexAction()
    {
        $this->_initProduct();
        $view = $this->getRequest()->getParam('q',null);       
        if($view == 'qv'){
              $template = 'ecp/quickview/product/view/media.phtml';
        }else {
              $template = 'catalog/product/view/media.phtml';
        }
        Mage::register('amconf_product_load', true);
        $parentBlock = $this->getLayout()->createBlock('catalog/product_view', 'product.info');
      
        if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Lbox/active'))
        {
            $template = 'amlbox/media.phtml';
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('catalog/product_view_media', 'product.info.media', array('template' => $template))->setParentBlock($parentBlock)->toHtml()
        );
    }
    
    public function galleryAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }
}