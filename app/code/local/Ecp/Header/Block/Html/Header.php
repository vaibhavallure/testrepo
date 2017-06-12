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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ecp_Header_Block_Html_Header extends Mage_Core_Block_Template {

    public function _construct() {
        $this->setTemplate('page/html/header.phtml');
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage() {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
    }

    public function setLogo($logo_src, $logo_alt) {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    public function getLogoSrc() {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_image');
        }
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'logo/'.$this->_data['logo_src'];
        return $this->getSkinUrl($this->_data['logo_src']);
        /*$model = Mage::getModel('ecp_headerlogo/headerlogo')->getCollection()
                ->addFieldToFilter('status',1)
                ->getFirstItem();        
        return str_replace('<p>', '', $model->getContentHeaderLogo());*/
    }

    public function getLogoAlt() {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = Mage::getStoreConfig('design/header/logo_alt');
        }
        return $this->_data['logo_alt'];
    }

    public function getWelcome() {
        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {

            	$customerName = $this->escapeHtml(Mage::getSingleton('customer/session')->getCustomer()->getName());
		$customer = explode(' ', $customerName);
                
                $msg = str_replace(array('Guest','guest'), $customer[0] , Mage::getStoreConfig('design/header/welcome'));
                $this->_data['welcome'] = $this->__('%s', $msg);
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }
        
        return $this->_data['welcome'];
    }

    public function getLoginLogutUrl(){
        return ($this->helper('customer')->isLoggedIn())
            ? $this->getUrl('customer/account/logout')
            : $this->getUrl('customer/account/login');
    }
    
    public function getLoginLogutText(){
		$addText = 'Sign out';
		if(!$this->helper('customer')->isLoggedIn()) 
			$addText = 'Sign in';
        return $addText;
    }
	public function getMyAccountUrl(){
        return ($this->helper('customer')->isLoggedIn())
            ? $this->getUrl('customer/account')
            : $this->getUrl('customer/account/login');
    }
}
