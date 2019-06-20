<?php 
/**
 * @extension   Remmote_Facebookproductcatalog
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Helper
 */
class Remmote_Facebookproductcatalog_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if module is enabled
     * @param  [type]     $store            Store ID. If website_level is set, the value correspond to the Website ID
     * @param  boolean    $website_level    Define if value is retrieve by website level
     * @return boolean
     * @author remmote
     * @date   2016-11-29
     */
	public function isEnabled($store = null, $website_level = false){
        if($website_level){
            return Mage::app()->getWebsite($store)->getConfig(Remmote_Facebookproductcatalog_Model_Config::MODULE_ENABLED);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::MODULE_ENABLED, $store);
        }
    }

    /**
     * Return array of extra attributes
     * @param  [type]     $website
     * @param  boolean    $array_format
     * @return [type]
     * @author edudeleon
     * @date   2017-03-29
     */
    public function getExtraAttributes($website = null, $array_format=true){
        if($website){
            $extra_attributes = Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::EXTRA_ATTRIBUTES);
        } else {
            $extra_attributes = Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::EXTRA_ATTRIBUTES, $website);
        }

        if($array_format){
            if($extra_attributes){
                $extra_attributes = explode(',', $extra_attributes);
                if(is_array($extra_attributes)){
                    foreach ($extra_attributes as $key => $value) {
                        $extra_attributes[$key] = trim($value);
                    }
                    return $extra_attributes;
                } else {
                    return array();
                }
            }
        }

        return $extra_attributes;
    }

    /**
     * Check if option to export all products is selected
     * @param  [type]     $website  Website ID
     * @return [type]
     * @author edudeleon
     * @date   2016-12-03
     */
    public function exportAll($website = null){
        if($website){
            return Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::EXPORT_ALL);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::EXPORT_ALL, $website);
        }
    }

    /**
     * Check if export Product ID instead Product SKU
     * @param  [type] $website
     * @return [type]
     * @author Remmote
     * @date   2018-06-05
     */
    public function useProductId($website = null)
    {
        if ($website) {
            return Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::USE_PRODUCT_ID);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::USE_PRODUCT_ID, $website);
        }
    }

    /**
     * Check if use product description instead of short description
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2018-05-10
     */
    public function useProductDescription($website = null)
    {
        if ($website) {
            return Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::USE_PRODUCT_DESCRIPTION);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::USE_PRODUCT_DESCRIPTION, $website);
        }
    }


    /**
     * Export products not visible individually
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2017-05-31
     */
    public function exportProductsNotVisibleIndividually($website = null){
        if($website){
            return Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::EXPORT_NOT_VISIBLE);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::EXPORT_NOT_VISIBLE, $website);
        }
    }

    /**
     * Getting category assignation type
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2018-06-04
     */
    public function getCategoryAssignation($website = null)
    {
        if ($website) {
            return Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::CATEGORY_ASSIGNATION);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::CATEGORY_ASSIGNATION, $website);
        }
    }

    /**
     * Check if include tax in product price
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2017-05-31
     */
    public function includeTax($website = null){
        if($website){
            return Mage::app()->getWebsite($website)->getConfig(Remmote_Facebookproductcatalog_Model_Config::INCLUDE_TAX);
        } else {
            return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::INCLUDE_TAX, $website);
        }
    }

    /**
     * Gets the cron frequency cofiguration value
     * @param  [type]     $store
     * @return [type]
     * @author remmote
     * @date   2016-11-29
     */
    public function getCronFrequency($store = null){
        return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::CRON_FREQUENCY, $store);
    } 

    /**
     * Gets the cron time configuration value
     * @param  [type]     $store
     * @return [type]
     * @author remmote
     * @date   2016-11-29
     */
    public function getCronTime($store = null){
        return Mage::getStoreConfig(Remmote_Facebookproductcatalog_Model_Config::CRON_TIME, $store);
    }

    /**
     * Get store default website
     * @return [type]
     * @author edudeleon
     * @date   2017-06-01
     */
    public function getDefaultWebsite() {
        $websites       = Mage::getModel('core/website')->getCollection()->addFieldToFilter('is_default', 1);
        $website        = $websites->getFirstItem();

        return $website;
    }
}