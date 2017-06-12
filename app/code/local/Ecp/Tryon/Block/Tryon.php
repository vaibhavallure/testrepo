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
class Ecp_Tryon_Block_Tryon extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getTryon()     
     { 
        if (!$this->hasData('tryon')) {
            $this->setData('tryon', Mage::registry('tryon'));
        }
        return $this->getData('tryon');
        
    }
    //->addFilter('status',1)->setPageSize(3)
    public function getRegions(){
        //$collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('tryonregion', array('in' => array('internal','lobule')));
        //$collection->addAttributeToFilter('admin_id', Mage::getSingleton('admin/session')->getUser()->getUserId());
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'tryonregion');

/*        $materialsArray = array();
        foreach($collection as $col){
            $string = explode(',',$col->getMaterial());
            foreach($string as $str){
                array_push($materialsArray, $str);
            }                
        }
        $result = array_unique($materialsArray);*/
       
        return $attribute->getSource()->getAllOptions(true, true);
    }
    
    public function getMaterials(){
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'material');

/*        $materialsArray = array();
        foreach($collection as $col){
            $string = explode(',',$col->getMaterial());
            foreach($string as $str){
                array_push($materialsArray, $str);
            }                
        }
        $result = array_unique($materialsArray);*/
       
        return $attribute->getSource()->getAllOptions(true, true);
    }
    
    /*
    public function _toHtmlilter() {
        die('kjhgfd'); 
        return parent::_toHtml();
    }*/
}