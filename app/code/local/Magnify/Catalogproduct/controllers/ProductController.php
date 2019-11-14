<?php
/**
 * Magento
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
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Magnify_Catalogproduct_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Current applied design settings
     *
     * @deprecated after 1.4.2.0-beta1
     * @var array
     */
    protected $_designProductSettingsApplied = array();

    /**
     * Initialize requested product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);

        return Mage::helper('catalog/product')->initProduct($productId, $this, $params);
    }

    /**
     * Initialize product view layout
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Catalog_ProductController
     */
    protected function _initProductLayout($product)
    {
        Mage::helper('catalog/product_view')->initProductLayout($product, $this);
        return $this;
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Recursively apply custom design settings to product if it's container
     * category custom_use_for_products option is setted to 1.
     * If not or product shows not in category - applyes product's internal settings
     *
     * @deprecated after 1.4.2.0-beta1, functionality moved to Mage_Catalog_Model_Design
     * @param Mage_Catalog_Model_Category|Mage_Catalog_Model_Product $object
     * @param Mage_Core_Model_Layout_Update $update
     */
    protected function _applyCustomDesignSettings($object, $update)
    {
        if ($object instanceof Mage_Catalog_Model_Category) {
            // lookup the proper category recursively
            if ($object->getCustomUseParentSettings()) {
                $parentCategory = $object->getParentCategory();
                if ($parentCategory && $parentCategory->getId() && $parentCategory->getLevel() > 1) {
                    $this->_applyCustomDesignSettings($parentCategory, $update);
                }
                return;
            }

            // don't apply to the product
            if (!$object->getCustomApplyToProducts()) {
                return;
            }
        }

        if ($this->_designProductSettingsApplied) {
            return;
        }

        $date = $object->getCustomDesignDate();
        if (array_key_exists('from', $date) && array_key_exists('to', $date)
            && Mage::app()->getLocale()->isStoreDateInInterval(null, $date['from'], $date['to'])
        ) {
            if ($object->getPageLayout()) {
                $this->_designProductSettingsApplied['layout'] = $object->getPageLayout();
            }
            $this->_designProductSettingsApplied['update'] = $object->getCustomLayoutUpdate();
        }
    }

    /**
     * Product view action
     */
    public function viewAction()
    {

        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        $selectedColor = $this->getRequest()->getParam("colorOptionId", false);
        $optionHelper = Mage::helper('allure_catalog');

        /*For Default select*/
        $selectValue1=$selectValue2="";
        $selAtr1=$selAtr2="";
        if(!empty($this->getRequest()->getParam("selectValue1", false))) {
            $selectValue1 = $this->getRequest()->getParam("selectValue1", false);
        }
        if(!empty($this->getRequest()->getParam("selectValue2", false))) {
            $selectValue2 = $this->getRequest()->getParam("selectValue2", false);

        }





        if (!$selectedColor) {

            $optionId = $this->getRequest()->getParam("optionId", false);

            if ($optionId) {
                $selectedColor = $optionId;
            } else {
                $metal = $this->getRequest()->getParam("metal", false);

                if ($metal) {
                    $selectedColor = $optionHelper->getOptionNumber($metal);
                }
            }
        }





        if (!$selectedColor) {



            $product = Mage::getModel('catalog/product')->load($productId);
            
            //don't allow for wholesale customer
            if ($product->getTypeId() == 'giftcards'){
                if(Mage::getSingleton('customer/session')->getCustomerGroupId() == 2){
                    $this->_redirectUrl(Mage::getBaseUrl());
                }
            }
            
            if ($product->isConfigurable()) {
                $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                $simpleProducts = $product->getTypeInstance()->getUsedProductCollection()->addAttributeToSelect('sku');
                $flag=FALSE;
                foreach ($productAttributeOptions as $productAttribute) {

                    if($productAttribute['attribute_code'] == 'metal'/* 'metal_color' */){

                        foreach ($productAttribute['values'] as $single) {

                            $selectedColorLabel=$single['label'];
                            $selectedColor = $single['value_index'];
                            foreach ($simpleProducts as $simple){
                                $sku=explode('|', $simple->getSku());

                                if(strtolower($selectedColorLabel)==strtolower($sku[1])){
                                    $stockItem = Mage::getModel('cataloginventory/stock_item')
                                        ->loadByProduct($simple->getId());
                                    if($stockItem->getId()){
                                        if($stockItem->getQty() > 0){
                                            $selectedColor=$single['value_index'];
                                            $flag=true;
                                            //break 2;
                                            break 2;
                                        }
                                    }
                                }
                            }

                            if($flag){
                                break;
                            }
                        }

                        if (!$flag) {
                            $selectedColor=$productAttribute['values'][0]['value_index'];
                        }
                    }
                }




                if ($selectedColor) {

                    $selectedColorText = $optionHelper->getOptionText($selectedColor);

                    $resource = Mage::getSingleton('core/resource');
                    $readConnection = $resource->getConnection('core_read');
                    $query="SELECT rel.parent_id,rel.child_id,atr.product_super_attribute_id,atr.attribute_id,itm.qty,cpen.value FROM `catalog_product_relation` rel JOIN catalog_product_super_attribute atr on atr.product_id = rel.parent_id join cataloginventory_stock_item itm on itm.product_id = rel.child_id JOIN catalog_product_entity_int cpen ON (cpen.attribute_id=atr.attribute_id AND cpen.entity_id=rel.child_id) where rel.parent_id = ".$productId." and itm.qty > 0 GROUP BY atr.attribute_id";
                    $results = $readConnection->fetchAll($query);

                    if(count($results)>=2)
                    {


                        $cnt=0;
                        foreach ($results as $res) {

                        $childid= $res['child_id'];


                            $query = "SELECT attribute_code FROM `eav_attribute` WHERE `attribute_id` = " . $res['attribute_id'];


                            $results = $readConnection->fetchAll($query);

                            if ($results[0]['attribute_code'] != 'metal') {
                                if ($res['value'] != $selectedColor) {
                                    $cnt++;

                                    if ($cnt == 1) {
                                        $selectValue1 = $res['value'];
                                        $selAtr1 = $res['attribute_id'];
                                    }
                                    if (($cnt >= 2) && ($selectValue1 != $res['value'])) {
                                        $selectValue2 = $res['value'];
                                        $selAtr2 = $res['attribute_id'];

                                        break;
                                    }
                                }
                            }
                        }
                    }



                    if($selectValue1 && !$selectValue2)
                    {

                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText.'&attribute'.$selAtr1.'='.$selectValue1.'&child='.$childid);

                    }
                    else if($selectValue1 && $selectValue2)
                    {

                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText.'&attribute'.$selAtr1.'='.$selectValue1.'&attribute'.$selAtr2.'='.$selectValue2.'&child='.$childid);

                    }
                    else
                    {
                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText);

                    }
                    return;
                }
            }
        }

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }
    /**
     * Product recent action
     */
    public function recentAction()
    {
        try {
            $_id  = (int) $this->getRequest()->getParam('id');
            Mage::helper('catalog/product_view')->prepareAndRender($_id, $this);
            $content = $this->getLayout()
                ->createBlock('reports/product_viewed')
                ->setTemplate('reports/product_viewed.phtml')
                ->toHtml();
            $this->getResponse()->setBody($content);
            return;
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update recently viewed.'));
            Mage::logException($e);
        }
    }

    /**
     * View product gallery action
     */
    public function galleryAction()
    {
        if (!$this->_initProduct()) {
            if (isset($_GET['store']) && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Display product image action
     *
     * @deprecated
     */
    public function imageAction()
    {
        /*
         * All logic has been cut to avoid possible malicious usage of the method
         */
        $this->_forward('noRoute');
    }
}

