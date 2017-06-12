<?php

class Allure_Managestock_Model_CatalogInventory_Stock extends Mage_CatalogInventory_Model_Stock
{
    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getId()
    {
    	return Mage::helper('managestock')->getStockIdOfCurrentWebsite();
    }

}
