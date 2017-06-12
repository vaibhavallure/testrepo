<?php

class Allure_AdminPermissions_Model_System_Store extends Varien_Object
{

    protected function _getStoreCollection()
    {
        return  Mage::app()->getStores();
    }

    /**
     * Retrieve region values for form
     *
     * @param bool $empty
     * @param bool $all
     * @return array
     */
    public function getStoreValuesForForm()
    {
        $options = array();
        $stores = $this->_getStoreCollection();
        $options[] = array(
        		'label' => 'All Store',
        		'value' => 0
        );
        foreach ($stores as $_eachStoreId => $val) {
        	$_store = Mage::app()->getStore($_eachStoreId);
        	$_storeName = $_store->getName();
        	$_storeId = $_store->getId();
            $options[] = array(
                'label' => $_storeName,
                'value' => $_storeId
            );
        }

        return $options;
    }
}
