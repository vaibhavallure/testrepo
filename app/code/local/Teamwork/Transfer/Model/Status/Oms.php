<?php
/**
 * Magento order status manipulation model (for order status XMLs came from OMS)
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Status_Oms extends Teamwork_Transfer_Model_Status_Abstract
{
    protected $_db_table_status             = Teamwork_Service_Model_Status_Oms::DB_TABLE_STATUS;
    protected $_db_field_status_entity_id   = Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_STATUS_ID;
    protected $_db_field_status_weborder_id = Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ORDER_ID;
    protected $_db_field_status_status      = Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_STATUS;

    protected $_entityIdLabel = 'status_id';

    /**
     * Prepare working objects
     *
     * @param string $orderNumber
     * @param string $statusId
     * @param string $channelId
     */
    protected function init($orderNumber, $statusId, $channelId)
    {
        $this->_totalPackageQty = 0;
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderNumber);

        // For each item we will have shipping method, carrier (taken from 'MagentoShippingMethod' field) and tracking number
        $select = $this->_db->select()->distinct()
            ->from(array('stit' => Mage::getSingleton('core/resource')->getTableName(Teamwork_Service_Model_Status_Oms::DB_TABLE_STATUS_ITEMS)), array(Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_STATUS, 'ShippingMethod' => Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_SHIPPING_METHOD, Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_CARRIER, Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_MAGENTO_SHIPPING_METHOD, 'TrackingNumber' => Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_TRACKING_NUMBER))
            ->joinLeft(array('webit' => Mage::getSingleton('core/resource')->getTableName('service_weborder_item')), "stit." . Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_ORDER_ITEM_ID . "=webit.WebOrderItemId", array('InternalId', 'Qty' => 'OrderQty'))
            ->join(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), "stit." . Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_ITEM_ID . "=it.item_id", array('internal_id'))
        ->where('stit.' . Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_STATUS_ID . ' = ?', $statusId);

        $this->prepareItem($this->_db->fetchAll($select));
    }

    // in 'init' method we mapped 'MagentoShippingMethod' field to 'Carrier' field in $this->_items.
    // 'MagentoShippingMethod' is what we keep in 'service_setting_shipping' table. It has form like '<carrier>_<shipping_method>'. We take carrier from this field
    // but, if MagentoShippingMethod is empty, we take carrier from CHQ carrier field
    protected function _getCarrierFromItem($orderItem)
    {
        if (!isset($this->_serviceSettingsModel))
        {
            $this->_serviceSettingsModel = Mage::getModel('teamwork_service/settings');
        }

        $magentoShippingName = $orderItem[Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_MAGENTO_SHIPPING_METHOD];
        return ($magentoShippingName) ? $this->_serviceSettingsModel->getCarrierCodeFromShippingName($magentoShippingName) : $orderItem[Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_CARRIER];
    }

    protected function _getShippingAmount($package, $packageId, $order)
    {
        return $order->getBaseShippingInvoiced();/**/
    }

    /**
     * On 'Modified' order status we create shipment for shipped items,
     * and then create credit memo for WHOLE order
     *
     * @param string $statusId
     * @param array $statusInfo
     */
    protected function setModified($statusId, $statusInfo)
    {
        $allItems = $this->_items;

        // select 'Shipped' items and create shipment with them
        $this->createInvoice();
        $this->_items = $this->filterItemsByStatus(Teamwork_Service_Model_Status_Oms::ORDER_STATUS_SHIPPED, $this->_items);
        $this->setShipped($statusId, $statusInfo, true);

        // create credit memo for all order items
        $this->_items = $allItems;
        $this->setCanceled($statusId, $statusInfo, true);
    }

    protected function filterItemsByStatus($targetStatus, $itemsToFilter)
    {
        $result = array();
        foreach ($itemsToFilter as $sku => $items)
        {
            foreach ($items as $item)
            {
                if ($item[Teamwork_Service_Model_Status_Oms::DB_FIELD_STATUS_ITEMS_STATUS] == $targetStatus)
                {
                    $result[$sku][] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * Method stub for possible request
     *
     * @param string $statusId
     * @param array $statusInfo
     */
    protected function setProcessing($statusId, $statusInfo)
    {
    }
}
