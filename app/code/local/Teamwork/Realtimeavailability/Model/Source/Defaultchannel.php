<?php
class Teamwork_Realtimeavailability_Model_Source_Defaultchannel
{
    public function toOptionArray()
    {
        $channels = Mage::getSingleton('teamwork_realtimeavailability/resource')->getChannels();
        if( !empty($channels) )
        {
            $result = array(null);
            foreach($channels as $channel_id => $channel_name)
            {
                $result[$channel_id] = $channel_name;
            }
            return $result;
        }
    }
}