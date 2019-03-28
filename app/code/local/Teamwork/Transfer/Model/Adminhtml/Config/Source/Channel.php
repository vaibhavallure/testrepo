<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Channel
{
    public function toOptionArray()
    {
        $result = array(
            array(
                'label' => null,
                'value' => null
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
                        $result[] = array(
                            'label' => $storeCode . ": {$channelId}",
                            'value' => $channelId
                        );
                    }
                }
            }
        }

        return $result;
    }
}