<?php

class Ecp_Productattribute_IndexController extends Mage_Core_Controller_Front_Action
{
    public function setimagesAction()
    {
        $quickview = $this->getRequest()->getParam('quickview');

        $color_id = (int)$this->getRequest()->getParam('color_id');
        $products = (string)$this->getRequest()->getParam('products');
        $products = unserialize($products);
        $product_id = $products[$color_id];

        $this->getLayout()->getUpdate()->addHandle('catalog_product_view');
        $this->loadLayout();

        $media = $this->getLayout()->getBlock('product.info.media');
        if (!$quickview) {
            $media->setTemplate('catalog/product/view/media.phtml');
            $media->setReloadYT(true);
        } else {
            $media->setTemplate('ecp/quickview/product/view/media.phtml');
        }

        $media->setProduct(Mage::getModel('catalog/product')->load($product_id));
        echo $media->toHtml();
    }
}
