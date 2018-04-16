<?php
class Teamwork_Common_Helper_Staging_Channel extends Mage_Core_Helper_Abstract
{
    const CHANNEL_AVAILABILITY = 'teamwork_common/chq/channel_usage';
    public function getStoreByChannelName($name)
    {
        foreach (Mage::app()->getWebsites() as $website)
        {
            foreach($website->getGroups() as $group)
            {
                foreach($group->getStores() as $store)
                {
                    if(strtolower(trim($store->getCode())) == strtolower(trim($name)))
                    {
                        return $store->getId();
                    }
                }
            }
        }
    }
    
    public function getChannelAvailabilityByStore($storeId)
    {
        return Mage::getStoreConfig(self::CHANNEL_AVAILABILITY, $storeId);
    }
}