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
 * @package     Ecp_DiscoverNavigation
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of DiscoverNavigation
 *
 * @category    Ecp
 * @package     Ecp_DiscoverNavigation
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_DiscoverNavigation_Block_DiscoverNavigation extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getMenu()
    {
        $discover = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToFilter('discover_mt_navigation',Ecp_DiscoverNavigation_Model_Entity_Attribute_Source_Options::HOME)
                ->getFirstItem();
        
        $discover = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($discover->getId());
        
//        $discoverItems = Mage::getModel('catalog/category')->getCollection()
//                ->addAttributeToFilter('name',array('neq'=>''))
//                ->addFieldToFilter('entity_id',explode(',',$discover->getCategoriesList()));
        
        $discoverItems = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToFilter('name',array('neq'=>''))
                ->addFieldToFilter('entity_id',explode(',',$discover->getCategoriesList()));
        
        $discoverItems->getSelect()
                ->join(array('discover'=>'ecp_discover_mariatash_navigation_menu'),'e.entity_id = discover.category_id')
                ->order('sort_order ASC');

        return $discoverItems;
        //return Mage::getModel('ecp_discovernavigation/discovernavigation')->getCollection()->addFieldToFilter('type',2)->addOrder('sort_order','asc');
    }
    
    public function getCompleteUrl($url_path){
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$url_path;
    }
    
    public function getCurrent(){
        return Mage::registry('current_category');           
    }
}