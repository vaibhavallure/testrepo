<?php
/**
 * Magento order status manipulation model (for order status XMLs came from CHQ)
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Status_Chq extends Teamwork_Transfer_Model_Status_Abstract
{
    protected $_db_table_status             = 'service_status';
    protected $_db_field_status_entity_id   = 'PackageId';
    protected $_db_field_status_weborder_id = 'WebOrderId';
    protected $_db_field_status_status      = 'Status';

    protected $_entityIdLabel = 'package_id';

    /**
     * Prepare working objects
     *
     * @param string $orderNumber
     * @param string $packageId
     * @param string $channelId
     */
    protected function init($orderNumber, $packageId, $channelId)
    {
        $this->_totalPackageQty = 0;
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderNumber);

        // For each item we will have shipping method, carrier and tracking number taken from 'service_status_shipping' table. 
        // It means that now we include data 'service_status_shipping' table to $this->_items property.
        $select = $this->_db->select()
            ->from(array('stit' => Mage::getSingleton('core/resource')->getTableName('service_status_items')), array('Qty'))
            ->joinLeft(array('webit' => Mage::getSingleton('core/resource')->getTableName('service_weborder_item')), "stit.WebOrderItemId=webit.WebOrderItemId", array('InternalId'))
            ->join(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), "stit.ItemId=it.item_id and it.channel_id = '{$channelId}'", array('internal_id'))
            ->joinLeft(array('ship' => Mage::getSingleton('core/resource')->getTableName('service_status_shipping')), "ship.PackageId=stit.PackageId", array('ShippingMethod' => 'ShippingMethod', 'TrackingNumber' => 'TrackingNo', 'Carrier' => 'Carrier'))
        ->where('stit.PackageId = ?', $packageId);

        $this->prepareItem($this->_db->fetchAll($select));
    }

    protected function _getCarrierFromItem($orderItem)
    {
        return $orderItem['Carrier'];
    }

    protected function _getShippingAmount($package, $packageId, $order)
    {
        return (float)$package['ShippingAmount'];
    }

    /**
     * Method stub for possible request
     *
     * @param string $packageId
     * @param array $package
     */
    protected function setFilling($packageId, $package)
    {
    }

}
