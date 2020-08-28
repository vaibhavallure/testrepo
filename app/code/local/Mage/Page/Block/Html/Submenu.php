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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Page
 * @copyright  Copyright (c) 2006-2018 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html_Submenu extends Mage_Core_Block_Template
{
    public function getParentCategories()
    {
        $helper = Mage::helper('catalog/category');
        return $helper->getStoreCategories();
    }

    public function getChildCategories($id)
    {
        $_category = Mage::getModel('catalog/category')->load($id);

        if($_category->hasChildren())
            return $_category->getChildrenCategories();
        else
            return array();
    }
    public function wholesaleCheck($id)
    {
        $_category = Mage::getModel('catalog/category')->load($id);

        if($_category->getIsWholesale()) {
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if($customer->getGroupId()==2)
                    return true;
            }
            return false;
        }
            return true;
    }
    public function isPrivateCategory($id)
    {
        $helper=Mage::helper('privatesale');
        if($helper->getCategory()==$id && $helper->hidePrivateCategory() && $helper->isEnabled())
            return true;

        return false;
    }

    public function jwRightSectionCat()
    {
       return array('new arrivals','curated under $300');
    }
    public function isJwRightSectionCat($catName)
    {
        return in_array(strtolower($catName),$this->jwRightSectionCat());
    }
    public function thumbnailCat()
    {
        return array('appointments');
    }
    public function isthumbnailCat($catName)
    {
        return in_array(strtolower($catName),$this->thumbnailCat());
    }
    public function getCatThumbnail($cat_id)
    {
        $thumb = Mage::getModel('catalog/category')->load($cat_id)->getThumbnail();
        return Mage::getBaseUrl('media').'catalog/category/'.$thumb;
    }
}
