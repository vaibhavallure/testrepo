<?php

class Allure_Privatesale_Model_Observer
{

    public function checkCategory()
    {
        if($this->helper()->isEnabled()) {
            $controllerName = Mage::app()->getRequest()->getControllerName();
            $actionName = Mage::app()->getRequest()->getActionName();
            if ($controllerName == 'category' && $actionName == 'view') {
                $id = Mage::app()->getRequest()->getParam('id');
                if ($this->helper()->getCategory() == $id) {
                    if (!Mage::getSingleton('core/session')->getPrivateSaleValidUser())
                        Mage::app()->getResponse()->setRedirect(Mage::getUrl('privatesale/login'));
                }
            }
            if ($controllerName == 'product' && $actionName == 'view') {
                $id = Mage::app()->getRequest()->getParam('id');
                $_product = Mage::getModel('catalog/product')->load($id);
                $categoryIds = array_values($_product->getCategoryIds());
                if (in_array($this->helper()->getCategory(),$categoryIds)) {
                    if (!Mage::getSingleton('core/session')->getPrivateSaleValidUser())
                        Mage::app()->getResponse()->setRedirect(Mage::getUrl('privatesale/login'));
                    }
            }

        }
    }
    private function helper()
    {
        return Mage::helper('privatesale');
    }

}