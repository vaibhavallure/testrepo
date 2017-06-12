<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Ddmenu_Block_Navigation_Lastproduct extends Mage_Catalog_Block_Navigation
{
    /**
     * HTML Last category product
     * 
     * @return string (or false)
     */
    protected function _toHtml()
    {
        if (!is_array($this->categoryIds)) {
            $this->categoryIds = explode(',', $this->categoryIds);
        }

        if (count($this->categoryIds)) {
            return parent::_toHtml();
        }

        return FALSE;
    }

    /**
     * Detection last product
     * 
     * @return Mage_Catalog_Model_Product
     */
    protected function _getLastProduct()
    {
        $collection	= Mage::getModel('catalog/product')->getCollection()
            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', NULL, 'left')
            ->addAttributeToFilter('category_id', array('in' => $this->categoryIds))
            ->addAttributeToSelect(array('name', 'url_key', 'small_image'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        $collection->getSelect()->order('entity_id desc')->limit(1);

        return $collection->getFirstItem();
    }

    /**
     * Get product category with max level
     *
     * @param Mage_Catalog_Model_Product
     * @return Mage_Catalog_Model_Category
     */
    protected function _getProductCategory($product)
    {
        return Mage::getModel('catalog/category')->load($product->getData('category_id'));
    }

    /**
     * Retrieve Product URL
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function _getProductUrl($product)
    {
        $routePath   = '';
        $routeParams = array();
        $rewrite     = Mage::getModel('core/url_rewrite');
        $storeId     = $product->getStoreId();
        $idPath      = sprintf('product/%d', $product->getEntityId());
        $rewrite->setStoreId($storeId)->loadByIdPath($idPath);
        if ($rewrite->getId()) {
            $requestPath = $rewrite->getRequestPath();
            $product->setRequestPath($requestPath);
        } else {
            $product->setRequestPath(FALSE);
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = TRUE;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return Mage::getModel('core/url')->setStore($storeId)->getUrl($routePath, $routeParams);
    }

}