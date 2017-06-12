<?php

class Ebizmarts_BakerlooShipping_Model_Adminhtml_System_Config_Source_Stores
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $collection = Mage::getModel('core/website')
            ->getCollection()
            ->joinGroupAndStore();

        $stores = array();

        foreach ($collection as $store) {
            $myStore = array();

            $optionLabel = "{$store->getGroupTitle()} / {$store->getStoreTitle()}";

            if (isset($stores[$store->getWebsiteId()])) {
                $stores[$store->getWebsiteId()]['value'][] = array('value' => $store->getStoreId(), 'label' => $optionLabel);
            } else {
                $myStore['label']= "{$store->getName()}";
                $myStore['value']= array(array('value' => $store->getStoreId(), 'label' => $optionLabel));

                $stores[$store->getWebsiteId()] = $myStore;
            }
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
