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
 * @package     Ecp_Citysearch
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Citysearch
 *
 * @category    Ecp
 * @package     Ecp_Citysearch
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Category_ProductsController extends Mage_Core_Controller_Front_Action {
    
    protected function _initCatagory() {
        Mage::dispatchEvent('catalog_controller_category_init_before', array('controller_action' => $this));
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($categoryId);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
        Mage::register('current_category', $category);
        try {
            Mage::dispatchEvent(
                    'catalog_controller_category_init_after', array(
                'category' => $category,
                'controller_action' => $this
                    )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $category;
    }

    //public function viewAction()
    public function getAction() {       

        $params = $this->getRequest()->getParams();     
        
        if(!isset($params['limit']) && empty($params['p'])){
            Mage::getSingleton('catalog/session')->setData("limit_page", 10);
        }else{
//            Mage::getSingleton('catalog/session')->setData("limit_page", $params['limit']);
        }

        if ($familycolor = $this->getRequest()->getParam('color', false)) {
            $colors = Mage::getModel('ecp_familycolors/familycolors')->getCollection()->AddFieldToFilter('colorfamily_id', array(explode(',', $familycolor)));
            if ($colors->getSize() > 0) {
                $colors = $colors->getColumnValues($this->getRequest()->getParam('attribute'));
                
                $tmp = array();
                foreach ($colors as $key => $color) {
                    foreach (unserialize($color) as $idx => $col) {
                        if (empty($col))
                            continue;
                        $tmp[] = $col;
                    }
                }
                $this->getRequest()->setParam($this->getRequest()->getParam('attribute'), implode(',', $tmp));
            }
        }
        
        if ($category = $this->_initCatagory()) {
            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($category);
           
            // apply custom design
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }

            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();
            //$update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('catalog_category_layered_ajax');
            $update->addHandle('CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            if ($layoutUpdates = $settings->getLayoutUpdates()) {
                if (is_array($layoutUpdates)) {
                    foreach ($layoutUpdates as $layoutUpdate) {
                        $update->addUpdate($layoutUpdate);
                    }
                }
            }

            $this->generateLayoutXml()->generateLayoutBlocks();
            // apply custom layout (page) template once the blocks are generated

            /* if ($settings->getPageLayout()) {
              $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
              } */

            $this->getLayout()->getUpdate()->addHandle('ecp_category_products_get');

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                        ->addBodyClass('category-' . $category->getUrlKey());
            }




            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }

    }

    public function changeAction() {
        $referer = $this->_getRefererUrl();
        $_arrReferer = explode('/price/', $referer);
        $_fromto = explode('/', $_arrReferer[1]);
        if (count($_fromto) == 1)
            $_fromto = explode('.', $_arrReferer[1]);
        $_fromto = explode(',', $_fromto[0]);
        $_from = $_fromto[0];
        $_to = $_fromto[1];
        if ($curency = (string) $this->getRequest()->getParam('currency')) {
            $oldCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            Mage::app()->getStore()->setCurrentCurrencyCode($curency);
            $currency = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getCurrentCurrencyCode());
            //list($to, $from) = explode(',', $this->getRequest()->getParam('price'));
            $to = $_to; //$this->getRequest()->getParam('to');
            $from = $_from; //$this->getRequest()->getParam('from');
            $currencyUSD = Mage::getModel('directory/currency')->load('USD');
            $rate = $currencyUSD->getRate($oldCurrency);
            $to = $currencyUSD->convert($to / $rate, Mage::app()->getStore()->getCurrentCurrencyCode());
            $from = $currencyUSD->convert($from / $rate, Mage::app()->getStore()->getCurrentCurrencyCode());
            $info = array(
                'to' => ceil($to),
                'from' => floor($from)
            );
            Mage::getSingleton('customer/session')->setCurrencyReloadInfo($info);
        }
        $_arrReferer = explode('/price/' . $_fromto[0] . ',' . $_fromto[1], $referer);
        $newReferer = ($info['from'] == 0 && $info['to'] == 0) ? $referer : $_arrReferer[0] . '/price/' . $info['from'] . ',' . $info['to'] . $_arrReferer[1];        
        $this->getResponse()->setRedirect($newReferer);
    }
}