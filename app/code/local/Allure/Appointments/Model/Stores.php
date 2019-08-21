<?php

/*

 * */

class Allure_Appointments_Model_Stores
{
    public function toOptionArray()
    {
        $virtualStoreHelper = Mage::helper("allure_virtualstore");
        $allStores = $virtualStoreHelper->getVirtualStores();
        $stores=array();

        foreach ($allStores as $st)
        {
            $stores[]=array('value' => $st->getStoreId(), 'label' =>$st->getName());
        }
        return $stores;
    }
}