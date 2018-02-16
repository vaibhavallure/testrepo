<?php

class Teamwork_Common_Model_Staging_Channel extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'channel_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/channel');
    }
    
    public function getChannels()
    {
        $helper = Mage::helper('teamwork_common/staging_channel');
        $channels = array();
        $collection = $this->getCollection()
            ->setOrder('entity_id', Varien_Data_Collection::SORT_ORDER_ASC)
        ->load();
        foreach($collection as $element)
        {
            $storeId = $helper->getStoreByChannelName($element->getChannelName());
            if($storeId && $helper->getChannelAvailabilityByStore($storeId))
            {
                $channels[$element->getChannelId()] = $element->getChannelName();
            }
        }
        return $channels;
    }
}