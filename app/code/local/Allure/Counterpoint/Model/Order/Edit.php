<?php
class Allure_Counterpoint_Model_Order_Edit extends IWD_OrderManager_Model_Order_Edit
{
    const XML_PATH_SALES_STATUS_ORDER = 'iwd_ordermanager/edit/order_status';
    const XML_PATH_RETURN_TO_STOCK = 'iwd_ordermanager/edit/return_to_stock';
    const XML_PATH_RECALCULATE_SHIPPING = 'iwd_ordermanager/edit/recalculate_shipping';

    private $added_items = false;
    private $refund_qty = array();
    private $edit_items = array();
    protected $base_currency_code = "USD";
    protected $order_currency_code = "USD";
    protected $delta = 0.06;
    protected $fact_grand_totals = array();

    protected function editOrderItem($order_item, $item)
    {
        $logger = Mage::getSingleton('iwd_ordermanager/logger');
        $old_row_total = $this->getOrderItemRowTotal($order_item);

        // description
        if (isset($item['description'])) {
            $logger->addOrderItemEdit($order_item, 'Description', $order_item->getDescription(), $item['description']);
            $order_item->setDescription($item['description']);
        }

        if (!$this->checkItemData($item)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('iwd_ordermanager')->__("Enter the correct data for product with sku [{$order_item->getSku()}]"));
            return;
        }

        $qty = $order_item->getQtyOrdered();

        // qty ordered
        $old_qty_ordered = $order_item->getQtyOrdered() - $order_item->getQtyRefunded();
        $fact_qty = isset($item['fact_qty']) ? $item['fact_qty'] : $item['qty'];
        $new_item_qty = $order_item->getQtyOrdered() < $item['qty'] ? $item['qty'] : $fact_qty;

        //$this->updateQty($order_item, $new_item_qty);
        $logger->addOrderItemEdit($order_item, 'Qty', number_format($old_qty_ordered, 2), number_format($new_item_qty, 2));

        $order = $order_item->getOrder();
        if (!$order->hasInvoices() || $qty <= $item['qty']) {
            // tax amount
            if (isset($item['tax_amount'])) {
                $logger->addOrderItemEdit($order_item, 'Tax amount', number_format($order_item->getBaseTaxAmount(), 2), number_format($item['tax_amount'], 2));
                $tax_amount = $this->currencyConvert($item['tax_amount']);
                $order_item->setBaseTaxAmount($item['tax_amount'])->setTaxAmount($tax_amount);
            }

            // hidden tax amount
            if (isset($item['hidden_tax_amount'])) {
                $hidden_tax_amount = $this->currencyConvert($item['hidden_tax_amount']);
                $order_item->setBaseHiddenTaxAmount($item['hidden_tax_amount'])->setHiddenTaxAmount($hidden_tax_amount);
            }

            // tax percent
            if (isset($item['tax_percent'])) {
                $logger->addOrderItemEdit($order_item, 'Tax percent', number_format($order_item->getTaxPercent(), 2), number_format($item['tax_percent'], 2));
                $order_item->setTaxPercent($item['tax_percent']);
            }

            // price
            if (isset($item['price'])) {
                $logger->addOrderItemEdit($order_item, 'Price (excl. tax)', number_format($order_item->getBasePrice(), 2), number_format($item['price'], 2));
                $price = $this->currencyConvert($item['price']);
                $order_item->setBasePrice($item['price'])->setPrice($price);
            }

            // price include tax
            if (isset($item['price_incl_tax'])) {
                $price_incl_tax = $this->currencyConvert($item['price_incl_tax']);
                $order_item->setBasePriceInclTax($item['price_incl_tax'])->setPriceInclTax($price_incl_tax);
            }

            // discount amount
            if (isset($item['discount_amount'])) {
                $logger->addOrderItemEdit($order_item, 'Discount amount', number_format($order_item->getBaseDiscountAmount(), 2), number_format($item['discount_amount'], 2));
                $discount_amount = $this->currencyConvert($item['discount_amount']);
                $order_item->setBaseDiscountAmount($item['discount_amount'])->setDiscountAmount($discount_amount);
            }

            // discount percent
            if (isset($item['discount_percent'])) {
                $logger->addOrderItemEdit($order_item, 'Discount percent', number_format($order_item->getDiscountPercent(), 2), number_format($item['discount_percent'], 2));
                $order_item->setDiscountPercent($item['discount_percent']);
            }

            // subtotal (row total)
            if (isset($item['subtotal'])) {
                $subtotal = $this->currencyConvert($item['subtotal']);
                $order_item->setBaseRowTotal($item['subtotal'])->setRowTotal($subtotal);
            }

            // subtotal include tax
            if (isset($item['subtotal_incl_tax'])) {
                $subtotal_incl_tax = $this->currencyConvert($item['subtotal_incl_tax']);
                $order_item->setBaseRowTotalInclTax($item['subtotal_incl_tax'])->setRowTotalInclTax($subtotal_incl_tax);
            }
        }

        // support_date
        if (isset($item['support_date'])) {
            if (Mage::getConfig()->getModuleConfig('IWD_Downloadable')->is('active', 'true')) {
                Mage::helper('iwd_ordermanager/downloadable')->updateSupportPeriod($order_item->getId(), $item['support_date']);
            }
        }

        $order_item->save();
        $this->updateOrderTaxItemTable($order_item);

        $new_row_total = $this->getOrderItemRowTotal($order_item);
        if (abs($old_row_total - $new_row_total) >= $this->delta) {
            $this->edit_items[] = $order_item->getId();
        }
    }

}