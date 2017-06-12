<?php

class Ebizmarts_BakerlooShipping_Model_Adminhtml_System_Config_Source_Locations
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $collection = Mage::getModel('bakerloo_location/store')
            ->getCollection()
            ->addFieldToFilter('active', array('eq' => true));

        $stores = array();

        foreach ($collection as $_store) {
            $stores[] = array('value' => $_store->getStoreId(), 'label' => $_store->getTitle());
        }

        return $stores;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array();
    }
}
