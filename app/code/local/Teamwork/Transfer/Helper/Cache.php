<?php
/**
 * Data helper
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Helper_Cache extends Mage_Core_Helper_Abstract
{
    public function cleanCache($ecm)
    {
        /* Mage::getModel('catalogrule/rule')->applyAll(); */
        $cacheMode = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_ECM_CACHE_MODE);
        if($cacheMode == Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_FLUSH_SYSTEM)
        {
            $this->flushSystemCache();
        }
        elseif($cacheMode == Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_FLUSH_ALL)
        {
            $this->flushAllCache();
        }
        elseif($cacheMode == Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_REFRESH_ALL)
        {
            $this->refreshAllCache();
        }
    }
    
    public function flushSystemCache()
    {
        Mage::app()->cleanCache();
        Mage::dispatchEvent('adminhtml_cache_flush_system');
    }
    
    public function flushAllCache()
    {
        Mage::dispatchEvent('adminhtml_cache_flush_all');
        Mage::app()->getCacheInstance()->flush();
    }
    
    public function refreshAllCache()
    {
        $types = array_keys((array)Mage::helper('core')->getCacheTypes());
        if (!empty($types))
        {
            foreach ($types as $type)
            {
                $tags = Mage::app()->getCacheInstance()->cleanType($type);
                Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
            }
        }
    }
}