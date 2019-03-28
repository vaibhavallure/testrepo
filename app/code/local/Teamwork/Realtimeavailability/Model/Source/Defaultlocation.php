<?php
class Teamwork_Realtimeavailability_Model_Source_Defaultlocation
{
    public function toOptionArray()
    {
        if( $store = Mage::app()->getRequest()->getParam('store') )
        {
            $locations = Mage::getSingleton('teamwork_realtimeavailability/resource')->getEnabledLocationsByStore( $store );
        }
        else
        {
            $locations = Mage::getSingleton('teamwork_realtimeavailability/resource')->getEnabledLocationsForEachStore();
        }
        
        if( !empty($locations) )
        {
            $result = array(null);
            foreach($locations as $code => $location_id)
            {
                $result[$location_id] = $code;
            }
            return $result;
        }
    }
}