<?php

class Ecp_Quickview_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction(){
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        // Prepare helper and params
        $viewHelper = Mage::helper('ecp_quickview/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    echo 'ERROR!!! '.$e->getMessage();
                    //$this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }
    public function indextryonAction(){
    	// Get initial data from request
    	$categoryId = (int) $this->getRequest()->getParam('category', false);
    	$productId  = (int) $this->getRequest()->getParam('id');
    	$specifyOptions = $this->getRequest()->getParam('options');
    	
    	// Prepare helper and params
    	$viewHelper = Mage::helper('ecp_quickview/product_view');
    	
    	$params = new Varien_Object();
    	$params->setCategoryId($categoryId);
    	$params->setSpecifyOptions($specifyOptions);
    	
    	// Render page
    	try {
    		$viewHelper->prepareAndRender($productId, $this, $params);
    	} catch (Exception $e) {
    		if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
    			if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
    				$this->_redirect('');
    			} elseif (!$this->getResponse()->isRedirect()) {
    				echo 'ERROR!!! '.$e->getMessage();
    				//$this->_forward('noRoute');
    			}
    		} else {
    			Mage::logException($e);
    			$this->_forward('noRoute');
    		}
    	}
    }
}
