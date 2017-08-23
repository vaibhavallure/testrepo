<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales order shipment model
 *
 * @method Mage_Sales_Model_Resource_Order_Shipment _getResource()
 * @method Mage_Sales_Model_Resource_Order_Shipment getResource()
 * @method int getStoreId()
 * @method Mage_Sales_Model_Order_Shipment setStoreId(int $value)
 * @method float getTotalWeight()
 * @method Mage_Sales_Model_Order_Shipment setTotalWeight(float $value)
 * @method float getTotalQty()
 * @method Mage_Sales_Model_Order_Shipment setTotalQty(float $value)
 * @method int getEmailSent()
 * @method Mage_Sales_Model_Order_Shipment setEmailSent(int $value)
 * @method int getOrderId()
 * @method Mage_Sales_Model_Order_Shipment setOrderId(int $value)
 * @method int getCustomerId()
 * @method Mage_Sales_Model_Order_Shipment setCustomerId(int $value)
 * @method int getShippingAddressId()
 * @method Mage_Sales_Model_Order_Shipment setShippingAddressId(int $value)
 * @method int getBillingAddressId()
 * @method Mage_Sales_Model_Order_Shipment setBillingAddressId(int $value)
 * @method int getShipmentStatus()
 * @method Mage_Sales_Model_Order_Shipment setShipmentStatus(int $value)
 * @method string getIncrementId()
 * @method Mage_Sales_Model_Order_Shipment setIncrementId(string $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Order_Shipment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Order_Shipment setUpdatedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Allure_MultiCheckout_Model_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{

    /**
     * After object save manipulations
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _afterSave()
    {
        if (null !== $this->_items) {
            foreach ($this->_items as $item) {
                $item->save();
            }
        }

        if (null !== $this->_tracks) {
            foreach($this->_tracks as $track) {
                $track->save();
            }
        }

        if (null !== $this->_comments) {
            foreach($this->_comments as $comment) {
                $comment->save();
            }
        }
        
        // MultiCheckout - Phase II - Charge After Shipment is created
        $order = $this->getOrder();
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT && $order->hasInvoices()) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->canCapture()) {
                    $invoice->capture();
                    $invoice->getOrder()->setIsInProcess(true);
                    $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
                }
            }
        }

        return parent::_afterSave();
    }
}
