<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *  
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

class Magestore_Webpos_Model_Observer_Inventorysuccess_GetStockId
    extends Magestore_Webpos_Model_Observer_Abstract
{
    /**
     * switch to stock_id (warehouse_id) which's linked to current location
     * 
     * @param $observer
     * @return $this
     */
    public function execute($observer)
    {
        $stock = $observer->getEvent()->getStock();
        /* get current location */
        $locationId = Mage::helper('webpos/permission')->getCurrentLocation();
        
        if(!$locationId || strpos(Mage::app()->getRequest()->getPathInfo(), 'webpos') === false) {
            return $this;
        }
        /* get warehouse which is linked to current location */
        $locationMapping = Magestore_CoreSuccess_Model_Service::locationService();
        $warehouseId = $locationMapping->getWarehouseIdByLocationId($locationId);

        if($warehouseId) {
            $stock->setStockId($warehouseId);
        }
    }    

}