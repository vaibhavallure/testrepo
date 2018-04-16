<?php

class Allure_Catalog_Model_Config extends Varien_Object
{

    const XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_QTY = 'warehouse/catalog/display_backend_grid_qty';

    const XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_BATCH_PRICES = 'warehouse/catalog/display_backend_grid_batch_prices';

    /**
     * Check if catalog backend grid qty visible
     *
     * @return boolean
     */
    public function isCatalogBackendGridQtyVisible ()
    {
        return true; // Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_QTY);
    }

    /**
     * Check if catalog backend grid batch prices visible
     *
     * @return boolean
     */
    public function isCatalogBackendGridBatchPricesVisible ()
    {
        return true; // Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_BATCH_PRICES);
    }
}
?>
