<?php

/**
 * Ecp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Productview
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Slideshow
 *
 * @category    Ecp
 * @package     Ecp_Slideshow
 */
class Ecp_Productview_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function loadAttributesAction()
    {
            $color = $this->getRequest()->getParam('color');
            $size = $this->getRequest()->getParam('size');
            $_product = $this->getRequest()->getParam('product');
            $colorAttributeId = $this->getRequest()->getParam('colorId');
            $sizeAttributeId = $this->getRequest()->getParam('sizeId');
            $productLoaded = Mage::getModel('catalog/product')->load($_product);
            //var_dump($productLoaded->getData());
            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(array($sizeAttributeId => $size, $colorAttributeId => $color), $productLoaded);
            $idProduct = $childProduct->getId();
            echo $idProduct;
    }   
    
    public function sendProductAction()
    {    
        $productId = $this->getRequest()->getParam('product');
        $productName = trim($this->getRequest()->getParam('productName'));
        $widthw = $this->getRequest()->getParam('widthwindow');
        $heightw = $this->getRequest()->getParam('heightwindow');
        $widthfullimage = $this->getRequest()->getParam('widthfullimage');
        $heightfullimage = $this->getRequest()->getParam('heightfullimage');
        $widththumbimage = $this->getRequest()->getParam('widththumbimage');
        $heightthumbimage = $this->getRequest()->getParam('heightthumbimage');
       
        $this->getLayout()->getUpdate()->addHandle('catalog_product_view');
        $this->loadLayout();

        $media = $this->getLayout()->getBlock('product.info.media');
        $media->setTemplate('catalog/product/view/mediaFullscreen.phtml');
        $media->setProduct(Mage::getModel('catalog/product')->load($productId));
        $media->setProductName($productName);
        $media->setWidthwindow(ceil($widthw));
        $media->setHeightwindow(ceil($heightw));       
        $media->setWidthfullimage(ceil($widthfullimage));
        $media->setHeightfullimage(ceil($heightfullimage));
        $media->setWidththumbimage(ceil($widththumbimage));
        $media->setHeightthumbimage(ceil($heightthumbimage));
        
        echo $media->toHtml();
    }    

}