<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

class Magestore_Webpos_Block_Login extends Magestore_Webpos_Block_AbstractBlock
{
    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';

    /**
     * @return string
     */
    public function _toHtml()
    {
        $isLogin = Mage::helper('webpos/permission')->getCurrentUser();
        if (!$isLogin) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        $imageUrl = Mage::helper('webpos')->getWebposLogo();
        if ($imageUrl) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'webpos/logo/'.$imageUrl;
        } else {
            return $this->getStoreLogoUrl();
        }
    }

    /**
     * @return string
     */
    protected function getStoreLogoUrl()
    {
        $uploadFolderName = Mage_Adminhtml_Model_System_Config_Backend_Image_Favicon::UPLOAD_DIR;
        $webposLogoPath = Mage::getStoreConfig('design/head/logo_src', Mage::app()->getStore()->getId());
        $logoUrl = Mage::getBaseUrl('media') . $uploadFolderName . '/' . $webposLogoPath;
        $path = Mage::getBaseDir('media') . '/' . $uploadFolderName . '/' . $webposLogoPath;

        if ($webposLogoPath !== null && $this->_isFile($path)) {
            $url = $logoUrl;
        } elseif ($this->getLogoFile()) {
            $url = $this->getViewFileUrl($this->getLogoFile());
        } else {
            $url = $this->getViewFileUrl('images/logo.svg');
        }
        $url = $this->getSkinUrl(Mage::getStoreConfig('design/header/logo_src', Mage::app()->getStore()->getId()));

        return $url;
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function _isFile($filename)
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage() && !is_file($filename)) {
            Mage::helper('core/file_storage_database')->saveFileToFilesystem($filename);
        }

        return is_file($filename);
    }

    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::getModel('core/website')->load($website);
        }
        return $website->getGroupCollection();
    }

    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::getModel('core/store_group')->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    public function getStores(){
        $storeId = Mage::app()->getStore()->getId();
        $website = Mage::app()->getWebsite();
        $stores = array();
        foreach ($this->getGroupCollection($website) as $_group){
            foreach ($this->getStoreCollection($_group) as $_store){
                $stores[] = array(
                    'id' => $_store->getId(),
                    'selected' => ($storeId == $_store->getId())?true:false,
                    'name' => $this->escapeHtml($_group->getName().' - '.$_store->getName()),
                );
            }
        }
        return $stores;
    }
}
