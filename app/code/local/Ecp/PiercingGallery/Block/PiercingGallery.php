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
 * @package     Ecp_PiercingGallery
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of PiercingGallery
 *
 * @category    Ecp
 * @package     Ecp_PiercingGallery
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_PiercingGallery_Block_PiercingGallery extends Mage_Core_Block_Template
{

    private $_piercingCategories = array();
    private $_currentLevel = 0;
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getPiercingGallery()     
    { 
        if (!$this->hasData('piercinggallery')) {
            $this->setData('piercinggallery', Mage::registry('piercinggallery'));
        }
        return $this->getData('piercinggallery');
        
    }
    
    public function _init(){
        $this->_currentLevel = 0;
        
        $piercingCat = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('name','PIERCING')->getData();

        $piercingSubCat = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')->addFieldToFilter('parent_id',$piercingCat[0]['entity_id'])
                ->addFieldToFilter('is_active', array('eq'=>'0'))
                ->addFieldToFilter('name',array('nlike'=>'%piercing%'))
                ->addAttributeToSort('position', 'ASC')
                ->getData();

        $contador = $contador2 = 0;
        foreach($piercingSubCat as $subcat){
            $subcategory = Mage::getModel('catalog/category')->load($subcat['entity_id'])->getData();
            $this->_piercingCategories[$subcategory['name']] = array();
            $this->_piercingCategories[$subcategory['name']]['name'] = $subcategory['name'];

            $locations = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('*')->addFieldToFilter('parent_id',$subcategory['entity_id'])->addAttributeToSort('position', 'ASC');
            
            foreach($locations as $location){

                $current = Mage::registry('current_piercing_location');
                if($contador == 0 && empty($current)){
                    Mage::register('current_piercing_location',$location->getId());
                    $this->_currentLevel = $contador2;
                }
                
                $contador++;
                
                if(Mage::registry('current_piercing_location')==$location->getId()){
                    $this->_currentLevel = $contador2;
                }
                    
                $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getId()] = array();
                $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getId()]['name'] = $location->getName();
                $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getId()]['id'] = $location->getEntityId();
                                                
            }
            
            $contador2++;
        }
    }
    
    public function getLooksByLocation($locationId){
//        $looks = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('parent_id',$location->getId());
       $looks = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('parent_id',$locationId)
           ->addFieldToFilter('is_active',1)
           ->addFieldToFilter('include_in_menu',1)
           ->addAttributeToSort('position','asc');
       $array = array();
       
       foreach($looks as $look){
            $productsLooks = array();
            
            $productsLooks['name'] = $look->getName();
            $productsLooks['img'] = $look->getImageUrl();
            $productsLooks['id'] = $look->getId();
//            $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getName()]['looks'][$look->getName()]['name'] = $look->getName();
//            $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getName()]['looks'][$look->getName()]['img'] = $look->getImageUrl();

            $products = $look->getProductCollection()
                    ->addAttributeToFilter('visibility' , array('neq'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
            $arrayProducts = array();
            
            foreach ($products as $product) {                 
                 $arrayProducts[] = $product;
            }
            
            $productsLooks['products'] = $arrayProducts;
            $array[] = $productsLooks;
//            $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getName()]['looks'][$look->getName()]['products'] = $productsLooks;
        }
        
        return $array;
    }
    
    public function getCategoriesArray(){
        if(empty($this->_piercingCategories))
            $this->_init();
        
        return $this->_piercingCategories;
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
    
    public function getCurrentLevel(){
        return $this->_currentLevel;
    }
    
    public function getBreadcrumbs(){
        $tmp = $this->getLayout()->getBlock('breadcrumbs');
        $piercing = Mage::getModel('catalog/category')->load(Mage::getStoreConfig('ecp_piercinggallery/piercinggallery/piercing_category_id'));
        $locationtype = $this->getRequest()->getParam('locationtype',null);
		$piercing_galary = Mage::getModel('catalog/category')->load(Mage::getStoreConfig('ecp_piercinggallery/piercinggallery/piercing_gallery_category_id'));
        $tmp->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
        $tmp->addCrumb('piercing', array('label'=>$piercing->getName(), 'title'=>$piercing->getName(), 'link'=>$piercing->getUrl()));
        if( $locationtype) {
            $locationtype_category = $piercing = Mage::getModel('catalog/category')->load( $locationtype);
            $tmp->addCrumb('piercinggal', array('label'=>$piercing_galary->getName(), 'title'=>$piercing_galary->getName(), 'link'=>$piercing_galary->getUrl()));
            $tmp->addCrumb('locationtype', array('label'=>$locationtype_category->getName(), 'title'=>$locationtype_category->getName()));
        } else {
            $tmp->addCrumb('piercinggal', array('label'=>$piercing_galary->getName(), 'title'=>$piercing_galary->getName()));
        }
        return $this->getLayout()->getBlock('breadcrumbs')->toHtml();
    }
    
}