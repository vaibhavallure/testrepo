<?php

/**
 * Entrepids
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
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Tryon
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tryon
 *
 * @category    Ecp
 * @package     Ecp_Tryon
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tryon_Adminhtml_TryonController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ecp_tryon/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()->renderLayout();
    }
    
    public function productsperregionAction() {
        $this->_initAction()->renderLayout();
    }
    
    public function ajaxAction() {
        $this->_initAction()->renderLayout();
    }
    
    public function updateproductsAction() {
        $prods = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('tryonids',array('like'=>'%'.$this->getRequest()->getParam('code').'%'));
        
        $prods = $prods->getColumnValues('entity_id');
        
        $toDelete = array_diff_key(array_flip($prods),array_flip($this->getRequest()->getParam('product')));
        
        foreach($toDelete as $id => $index){
            $product = Mage::getModel('catalog/product')->load($id);
            $tryonData = array_flip(explode(',',$product->getTryonids()));
            unset($tryonData[$this->getRequest()->getParam('code')]);
            $product->setTryonids(implode(',',array_flip($tryonData)))->save();
        }
        
        foreach($this->getRequest()->getParam('product') as $key => $product){
            $product = Mage::getModel('catalog/product')->load($product);
            $tryonData = array_flip(explode(',',$product->getTryonids()));
            if(!array_key_exists($this->getRequest()->getParam('code'), $tryonData)){
                $tryonData[$this->getRequest()->getParam('code')] = count($tryonData);
                $product->setTryonids(implode(',',array_flip($tryonData)))
                        ->save();
            }
        }
        
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_tattoo')->__('Region updated'));
        
        //$this->_forward('productsperregion');
        $this->_redirect('*/*/productsperregion',array('code'=>$this->getRequest()->getParam('code')));
    }

    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_tryon');
    }
}