<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Richmediachannel
{
    public function toOptionArray()
    {
        $result = array(
            array(
                'label' => 'all channels',
                'value' => Teamwork_Transfer_Helper_Config::RICHMEDIA_PUSH_FROM_ALL_CHANNELS
            )
        );

        $db          = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select      = $db->select()->from(Mage::getSingleton('core/resource')->getTableName('service_channel'), array('channel_id', 'channel_name'));
        $ecmChannels = $db->fetchPairs($select);

        foreach (Mage::app()->getWebsites() as $website)
        {
            foreach ($website->getGroups() as $group)
            {
                foreach ($group->getStores() as $store)
                {
                    $storeCode = $store->getCode();
                    foreach (array_keys($ecmChannels, $storeCode) as $channelId)
                    {
                        $prettyChannelId = substr($channelId, 0, 4) . '...';
                        $result[] = array(
                            'label' => $storeCode . " (channel {$prettyChannelId})",
                            'value' => $channelId
                        );
                    }
                }
            }
        }

        return $result;
    }
}