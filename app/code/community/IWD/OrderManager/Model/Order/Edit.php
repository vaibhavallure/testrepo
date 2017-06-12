<?php
class IWD_OrderManager_Model_Order_Edit extends Mage_Sales_Model_Order_Item
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

    public function getOrderStatusesForUpdateIds()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_SALES_STATUS_ORDER));
    }

    public function getAllowReturnToStock()
    {
        return Mage::getStoreConfig(self::XML_PATH_RETURN_TO_STOCK);
    }

    public function isRecalculateShipping()
    {
        return Mage::getStoreConfig(self::XML_PATH_RECALCULATE_SHIPPING);
    }

    public function editItems($order_id, $items)
    {
        /* event */
        $order = Mage::getModel('sales/order')->load($order_id);
        $old_order = clone $order;
        Mage::dispatchEvent('iwd_ordermanager_sales_order_edit_before', array('order' => $order, 'order_items' => $order->getItemsCollection()));

        /* check status */
        if (!$this->checkOrderStatusForUpdate($order)) {
            Mage::getSingleton('adminhtml/session')->addError("Sorry... You can't edit order with current status. Check configuration: IWD >> Order Manager >> Edit Order");
            return 0;
        }

        $this->updateOrderItems($items, $order_id);

        $this->collectOrderTotals($order_id);

        $this->createCreditMemo($order_id);

        $order = Mage::getModel("sales/order")->load($order_id);
        if ($this->isRecalculateShipping() && $order->canShip()) {
            Mage::getModel('iwd_ordermanager/shipping')->recollectShippingAmount($order_id);
        }

        $this->collectOrderTotals($order_id);

        $this->updateOrderPayment($order_id, $old_order);

        /* event */
        $order = Mage::getModel('sales/order')->load($order_id);
        Mage::dispatchEvent('iwd_ordermanager_sales_order_edit_after', array('order' => $order, 'order_items' => $order->getItemsCollection()));
        return 1;
    }

    public function execEditOrderItems($order_id, $params)
    {
        try {
            $notify = isset($params['notify']) ? $params['notify'] : null;
            $result = $this->editItems($order_id, $params['items']);

            if ($result && $notify) {
                $message = isset($params['comment_text']) ? $params['comment_text'] : null;
                $email = isset($params['comment_email']) ? $params['comment_email'] : null;
                $result['notify'] = Mage::getModel('iwd_ordermanager/notify_notification')->sendNotifyEmail($order_id, $email, $message);
            }
            return $result;
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'iwd_order_manager.log');
            return false;
        }
    }

    public function checkOrderStatusForUpdate($order)
    {
        $order_status = $order->getStatus();
        $allow_order_statuses = $this->getOrderStatusesForUpdateIds();
        $wait_order_status = Mage::getStoreConfig(IWD_OrderManager_Model_Logger::CONFIG_XML_PATH_CONFIRM_STATUS_WAIT, Mage::app()->getStore());

        return in_array($order_status, $allow_order_statuses) || ($wait_order_status == $order_status);
    }

    public function updateOrderPayment($order_id, $old_order)
    {
        try {
            $old_total_paid = $old_order->getBaseTotalPaid() - $old_order->getBaseTotalRefunded();

            $old_total_paid = !empty($old_total_paid) ? $old_total_paid : 0.0;

            $order = Mage::getModel('sales/order')->load($order_id);

            if (isset($this->fact_grand_totals["grand_total"])) {
                $new_total_paid = $this->fact_grand_totals["grand_total"] - $order->getBaseTotalRefunded();
            } else {
                $new_total_paid = $order->getBaseGrandTotal() - $order->getBaseTotalRefunded();
            }

            if($new_total_paid != $old_total_paid) {
                $this->reauthorizePayment($order_id, $old_order);

                if ($new_total_paid > $old_total_paid) {
                    $this->updateInvoice($order);
                }

                if($new_total_paid < $old_total_paid) {
                    $this->refundAdjustment($order_id, $old_total_paid);
                }
            }
        } catch (Exception $e) {
            return -1;
        }

        return 0;
    }

    protected function refundAdjustment($order_id, $old_total_paid)
    {
        $refund = 0;
        if (isset($this->fact_grand_totals["base_grand_total"])) {
            //$refund = $order->getBaseTotalPaid() - $order->getBaseTotalRefunded() - $this->fact_grand_totals["base_grand_total"];
            $refund = $old_total_paid - $this->fact_grand_totals["base_grand_total"];
        }

        if ($refund <= 0) {
            return;
        }

        Mage::getModel('iwd_ordermanager/creditmemo')->createCreditmemoAdjustmentRefund($order_id, $refund);
    }

    protected function reauthorizePayment($order_id, $old_order)
    {
        $payment = Mage::getModel('iwd_ordermanager/payment_payment');

        if ($payment->reauthorizePayment($order_id, $old_order) === -1) {
            $order = Mage::getModel('sales/order')->load($order_id);
            Mage::dispatchEvent('iwd_ordermanager_sales_order_reauthorize_payment_fail', array('order' => $order, 'payment' => $payment));
            throw new Exception('IWD Order Manager re-authorization failed');
        }
    }

    protected function checkItemData($item)
    {
        $keys = array('price', 'price_incl_tax', 'qty',
            'subtotal', 'subtotal_incl_tax',
            'tax_amount', 'tax_percent',
            'discount_amount', 'discount_percent', 'row_total'
        );

        foreach ($keys as $key) {
            if (isset($item[$key]) && !is_numeric($item[$key])) {
                return false;
            }
        }

        return true;
    }

    protected function updateQty($order_item, $new_qty)
    {
        /*
         *  $new_qty is a NOT fact qty for customer NOW !!!
         *  it is the order item ORDERED QTY !!!
         */

        // '-' qty
        if ($order_item->getQtyOrdered() > $new_qty) {
            $this->reduceProduct($order_item, $new_qty);
        } // '+' qty
        else {
            $this->increaseProduct($order_item, $new_qty);
        }

        if ($order_item->getQtyInvoiced() > 0) {
            $qty_ordered = max($new_qty, $order_item->getQtyOrdered());
        } else {
            $qty_ordered = max($new_qty, 0);
        }

        $order_item->setQtyOrdered($qty_ordered);
        $order_item->setRowWeight($order_item->getWeight() * $qty_ordered - $order_item->getQtyRefunded());
        $order_item->save();

        return $qty_ordered;
    }

    protected function reduceProduct($order_item, $new_qty)
    {
        $refund = $order_item->getQtyOrdered() - $new_qty - $order_item->getQtyRefunded();

        if ($refund > 0) {
            $this->refund_qty[$order_item->getId()] = $refund;

            if ($this->getAllowReturnToStock()) {
                Mage::getSingleton('cataloginventory/stock')->backItemQty($order_item->getProductId(), $refund);
            }
        }
    }

    protected function increaseProduct($order_item, $new_qty)
    {
        $product_id = $order_item->getProductId();

        if ($product_id) {
            $stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);

            if (Mage::helper('cataloginventory')->isQty($stock_item->getTypeId())) {
                if ($order_item->getStoreId()) {
                    $stock_item->setStoreId($order_item->getStoreId());
                }

                $qty = $new_qty - ($order_item->getQtyOrdered()); // - $order_item->getQtyRefunded());
                $qty = $qty < 0 ? 0 : $qty;

                if ($stock_item->checkQty($qty)) {
                    $stock_item->subtractQty($qty)->save();
                }
            }
        } else {
            Mage::throwException(Mage::helper('iwd_ordermanager')->__('Cannot specify product identifier for the order item.'));
        }

        $this->added_items = true;
    }


    protected function currencyConvert($price)
    {
        if ($this->base_currency_code === $this->order_currency_code){
            return $price;
        }
        return Mage::helper('directory')->currencyConvert($price, $this->base_currency_code, $this->order_currency_code);
    }

    protected function updateOrderItems($items, $orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);

        $this->base_currency_code = $order->getBaseCurrencyCode();
        $this->order_currency_code = $order->getOrderCurrencyCode();
        $this->refund_qty = array();
        $this->edit_items = array();
        $this->added_items = false;
        $this->fact_grand_totals = array();

        foreach ($items as $id => $item) {
            $order_item = $order->getItemById($id);

            // remove item
            if (isset($item['remove']) && $item['remove'] == 1) {
                $this->removeOrderItem($order_item);
                continue;
            }

            // add new item
            if (isset($item['quote_item'])) {
                $order_item = $this->addNewOrderItem($item['quote_item'], $order);
            }

            // edit item
            $this->editOrderItem($order_item, $item);
        }
    }

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

        $this->updateQty($order_item, $new_item_qty);
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

    protected function addNewOrderItem($quote_item_id, $order)
    {
        $quote_item = Mage::getModel('sales/quote_item')->load($quote_item_id);
        if (!$quote_item->getId()) {
            return null;
        }

        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quote_item->getQuoteId());
        $quote_item->setQuote($quote);

        $order_item = $this->addItemToOrder($order, $quote_item);
        $order_item->save();

        $this->addChildrenItems($quote_item_id, $quote, $order_item, $order);

        Mage::getSingleton('iwd_ordermanager/logger')->addOrderItemAdd($order_item);

        return $order_item;
    }

    public function addItemToOrder($order, $quote_item)
    {
        try {
            $optionCollection = Mage::getModel('sales/quote_item_option')->getCollection()
                ->addItemFilter(array($quote_item->getId()));
            $quote_item->setOptions($optionCollection->getOptionsByItem($quote_item));

            if ($simpleOption = $quote_item->getProduct()->getCustomOption('simple_product')) {
                $simple_product = Mage::getModel('catalog/product')->load($simpleOption->getProductId());
                $simpleOption->setProduct($simple_product);
            }

            $order_item = Mage::getModel('sales/convert_quote')->itemToOrderItem($quote_item);
            $order_item->setOrderId($order->getId());

            if ($quote_item->getParentItemId()) {
                $order_item->setParentItem($order->getItemByQuoteItemId($quote_item->getParentItemId()));
            }

            if (Mage::getModel("tax/config")->priceIncludesTax()) {
                $order_item->setOriginalPrice($order_item->getPriceInclTax());
                $order_item->setBaseOriginalPrice($order_item->getBasePriceInclTax());
            } else {
                $order_item->setOriginalPrice($order_item->getPrice());
                $order_item->setBaseOriginalPrice($order_item->getBasePrice());
            }

            $order_item->save($order->getId());

            //from inventory
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($order_item->getProductId());
            if ($stockItem->checkQty($order_item->getQtyOrdered()) || Mage::app()->getStore()->isAdmin()) {
                $stockItem->subtractQty($order_item->getQtyOrdered());
                $stockItem->save();
                $this->added_items = true;
            }

            $this->addOrderTaxItemTable($order_item);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'iwd_order_manager.log');
            return null;
        }
        return $order_item;
    }

    protected function addChildrenItems($quote_item_id, $quote, $order_item, $order)
    {
        $id = $order_item->getId();
        $qty = $order_item->getQtyOrdered();

        // children
        $quote_children_items = Mage::getModel("sales/quote_item")
            ->getCollection()->setQuote($quote)
            ->addFieldToFilter("parent_item_id", $quote_item_id);

        foreach ($quote_children_items as $quote_children_item) {
            $quote_children_item->setQuote($quote);
            $order_item = $this->addItemToOrder($order, $quote_children_item);
            $order_item_qty = $order_item->getQtyOrdered() * $qty;
            $order_item->setQtyOrdered($order_item_qty)
                ->setParentItemId($id)
                ->save();
        }
    }

    protected function removeOrderItem($order_item)
    {
        $product_type = $order_item->getProductType();

        if ($product_type == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE || $product_type == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $children_items = $order_item->getChildrenItems();
            foreach ($children_items as $children_item) {
                $this->deleteOrRefundOrderItem($children_item);
            }
        }

        $this->deleteOrRefundOrderItem($order_item);
        $this->addToLogAboutDeleteOrderItem($order_item);
    }

    protected function deleteOrRefundOrderItem($order_item)
    {
        if ($order_item->getQtyInvoiced() == 0) {
            $order_item->delete();
        } else {
            // we can not hard delete item from order when item is invoiced!
            $this->updateQty($order_item, 0);
        }
    }

    protected function addToLogAboutDeleteOrderItem($orderItem)
    {
        $is_refunded = ($orderItem->getQtyInvoiced() != 0);
        Mage::getSingleton('iwd_ordermanager/logger')->addOrderItemRemove($orderItem, $is_refunded);
    }

    public function addOrderTaxItemTable($order_item)
    {
        $result = Mage::getModel('tax/sales_order_tax')->getCollection()
            ->addFieldToFilter('order_id', $order_item->getOrderId());

        if (count($result) > 0) {
            $data = array(
                'item_id' => $order_item->getId(),
                'tax_id' => $result->getFirstItem()->getTaxId(),
                'tax_percent' => $order_item->getTaxPercent()
            );
            Mage::getModel('tax/sales_order_tax_item')->setData($data)->save();
        }
    }

    public function createCreditMemo($order_id)
    {
        $order = Mage::getModel("sales/order")->load($order_id);

        if (empty($this->refund_qty) || $order->getTotalPaid() == 0) {
            return null;
        }

        try {
            $creditmemo = Mage::getModel('iwd_ordermanager/creditmemo');
            if ($creditmemo->isAllowCreateCreditmemo($order_id)) {
                return $creditmemo->createCreditmemo($order_id, $this->refund_qty);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'iwd_order_manager.log');
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        return null;
    }

    public function updateInvoice($order)
    {
        try {
            if ($order->getTotalPaid() > 0) {
                Mage::getModel('iwd_ordermanager/invoice')->updateInvoice($order);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'iwd_order_manager.log');
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }

        return true;
    }


    protected function updateOrderTaxItemTable($order_item)
    {
        $new_tax_percent = $order_item->getTaxPercent();

        $tax_collection = Mage::getModel('tax/sales_order_tax_item')->getCollection()
            ->addFieldToFilter('item_id', $order_item->getId());

        //add new
        if ($tax_collection->getSize() == 0) {
            $result = Mage::getModel('tax/sales_order_tax')->getCollection()
                ->addFieldToFilter('order_id', $order_item->getOrderId());
            if (count($result) > 0) {
                $data = array(
                    'item_id' => $order_item->getId(),
                    'tax_id' => $result->getFirstItem()->getTaxId(),
                    'tax_percent' => $new_tax_percent
                );
                Mage::getModel('tax/sales_order_tax_item')->setData($data)->save();
            }
        }

        //update
        foreach ($tax_collection as $tax) {
            $tax->setTaxPercent($new_tax_percent)->save();
        }
    }

    public function updateOrderTaxTable($order_id)
    {
        $order_items = Mage::getModel('sales/order')->load($order_id)->getItemsCollection();
        $taxes = array();

        foreach ($order_items as $item) {
            if (!isset($taxes[$item->getTaxPercent()])) {
                $taxes[$item->getTaxPercent()] = array('amount' => 0, 'base_amount' => 0);
            }

            $taxes[$item->getTaxPercent()]['amount'] += $item->getTaxAmount();
            $taxes[$item->getTaxPercent()]['base_amount'] += $item->getBaseTaxAmount();
        }

        $source = Mage::getModel('sales/order')->load($order_id);
        $rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($source);

        foreach ($rates as $rate) {
            if (isset($taxes[$rate->getPercent()])) {
                $rate->setAmount($taxes[$rate->getPercent()]['amount'])
                    ->setBaseAmount($taxes[$rate->getPercent()]['base_amount'])
                    ->save();
            } else {
                //TODO
            }
        }
    }

    public function calculateOrderRefundedAmount($order)
    {
        $amount_refunded = 0;
        $base_amount_refunded = 0;

        $item_collection = $order->getItemsCollection();
        foreach ($item_collection as $order_item) {
            $amount_refunded += $order_item->getAmountRefunded();
            $base_amount_refunded += $order_item->getBaseAmountRefunded();
        }

        return array(
            "amount_refunded" => $amount_refunded,
            "base_amount_refunded" => $base_amount_refunded,
        );
    }

    public function collectOrderTotals($order_id)
    {
        $this->fact_grand_totals = array();

        $order = Mage::getModel('sales/order')->load($order_id);

        $total_qty_ordered = 0;
        $weight = 0;
        $total_item_count = 0;
        $base_tax_amount = 0;
        $base_hidden_tax_amount = 0;
        $base_discount_amount = 0;
        $base_total_weee_discount = 0;
        $base_subtotal = 0;
        $base_subtotal_incl_tax = 0;

        foreach ($order->getItemsCollection() as $orderItem) {
            $base_discount_amount += $orderItem->getBaseDiscountAmount();

            //bundle part
            if ($orderItem->getParentItem()) {
                continue;
            }

            $base_tax_amount += $orderItem->getBaseTaxAmount();
            $base_hidden_tax_amount += $orderItem->getBaseHiddenTaxAmount();

            $total_qty_ordered += $orderItem->getQtyOrdered();
            $total_item_count++;
            $weight += $orderItem->getRowWeight();
            $base_subtotal += $orderItem->getBaseRowTotal(); /* RowTotal for item is a subtotal */
            $base_subtotal_incl_tax += $orderItem->getBaseRowTotalInclTax();
            $base_total_weee_discount += $orderItem->getBaseDiscountAppliedForWeeeTax();
        }

        //$base_subtotal_incl_tax = $base_subtotal + $base_hidden_tax_amount + $base_total_weee_discount + $base_tax_amount;

        /** convert currency **/
        $base_currency_code = $order->getBaseCurrencyCode();
        $order_currency_code = $order->getOrderCurrencyCode();
        $directory = Mage::helper('directory');
        if ($base_currency_code === $order_currency_code) {
            $discount_amount = $base_discount_amount;
            $tax_amount = $base_tax_amount;
            $hidden_tax_amount = $base_hidden_tax_amount;
            $subtotal = $base_subtotal;
            $subtotal_incl_tax = $base_subtotal_incl_tax;
        } else {
            $discount_amount = $directory->currencyConvert($base_discount_amount, $base_currency_code, $order_currency_code);
            $tax_amount = $directory->currencyConvert($base_tax_amount, $base_currency_code, $order_currency_code);
            $hidden_tax_amount = $directory->currencyConvert($base_hidden_tax_amount, $base_currency_code, $order_currency_code);
            $subtotal = $directory->currencyConvert($base_subtotal, $base_currency_code, $order_currency_code);
            $subtotal_incl_tax = $directory->currencyConvert($base_subtotal_incl_tax, $base_currency_code, $order_currency_code);
        }

        $order->setTotalQtyOrdered($total_qty_ordered)
            ->setWeight($weight);

        if (!$order->hasInvoices() || $order->getSubtotal() < $subtotal) {
            $order->setSubtotal($subtotal)->setBaseSubtotal($base_subtotal)
                ->setSubtotalInclTax($subtotal_incl_tax)->setBaseSubtotalInclTax($base_subtotal_incl_tax)
                ->setTaxAmount($tax_amount)->setBaseTaxAmount($base_tax_amount)
                ->setHiddenTaxAmount($hidden_tax_amount)->setBaseHiddenTaxAmount($base_hidden_tax_amount)
                ->setDiscountAmount('-' . $discount_amount)->setBaseDiscountAmount('-' . $base_discount_amount)
                ->setTotalItemCount($total_item_count)
                ->save();

            $this->fact_grand_totals = array();
        } else {
            $tax = $tax_amount + $order->getShippingTaxAmount();
            $base_tax = $base_tax_amount + $order->getBaseShippingTaxAmount();
            $grand_total = $subtotal + $tax + $order->getShippingInclTax() - abs($discount_amount);
            $base_grand_total = $base_subtotal + $base_tax + $order->getBaseShippingInclTax() - abs($base_discount_amount);
            $this->fact_grand_totals = array(
                "grand_total" => $grand_total,
                "base_grand_total" => $base_grand_total,
            );
        }

        $order->save();

        $this->calculateGrandTotal($order);

        $this->updateOrderTaxTable($order_id);
    }

    public function calculateGrandTotal($order)
    {
        // shipping tax
        $tax = $order->getTaxAmount() + $order->getShippingTaxAmount();
        $base_tax = $order->getBaseTaxAmount() + $order->getBaseShippingTaxAmount();

        $order->setTaxAmount($tax)->setBaseTaxAmount($base_tax)->save();

        // Order GrandTotal include tax !!!
        $grand_total = $order->getSubtotal() + $order->getTaxAmount() + $order->getShippingAmount() - abs($order->getDiscountAmount());
        $base_grand_total = $order->getBaseSubtotal() + $order->getBaseTaxAmount() + $order->getBaseShippingAmount() - abs($order->getBaseDiscountAmount());

        $order->setGrandTotal($grand_total)
            ->setBaseGrandTotal($base_grand_total)
            ->save();

        $this->addCustomPriceToOrderGrandTotal($order);
    }

    protected function getOrderItemRowTotal($item)
    {
        return $item->getRowTotal() + $item->getTaxAmount() + $item->getHiddenTaxAmount() + $item->getWeeeTaxAppliedRowAmount() - $item->getDiscountAmount();
    }


    protected function addCustomPriceToOrderGrandTotal($order)
    {
        //TODO: add custom logic if you need add custom price to order
        return;

        /*
        $additional_total = 0.0;        // add custom amount
        $additional_base_total = 0.0;   // add custom base amount

        $grand_total = $order->getGrandTotal();
        $base_grand_total = $order->getBaseGrandTotal();
        $order->setGrandTotal($grand_total + $additional_total)
            ->setBaseGrandTotal($base_grand_total + $additional_base_total)
            ->save();

        if(isset($this->fact_grand_totals["grand_total"])){
            $this->fact_grand_totals["grand_total"] += $additional_total;
        }
        if(isset($this->fact_grand_totals["base_grand_total"])){
            $this->fact_grand_totals["base_grand_total"] += $additional_base_total;
        }
        */
    }
}