<?php

class Ebizmarts_BakerlooRestful_Model_Api_Orders extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const PRICE_OVERRIDE_EMAIL_TEMPLATE = 'bakerloorestful_pos_customprice_template';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_order';

    public $defaultDir = "DESC";

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order';

    protected $_model = "sales/order";
    protected $_filterUseOR = true;
    protected $_pickUpSearch = false;

    public function checkDeletePermissions()
    {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/delete'));
    }

    public function checkPostPermissions()
    {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/create'));
    }

    /**
     * Return order options.
     * @param $item
     * @return array
     */
    public function orderOptions($item)
    {
        $result = array();
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }

        $selections = array();
        foreach ($result as $option) {

            if (!isset($option['label'])) {
                continue;
            }

            $_sel = array('label' => $option['label'], 'value' => '', 'type' => isset($option['option_type']) ? $option['option_type'] : '');
            if (!is_array($option['value'])) {
                if (isset($option['option_type']) && ($option['option_type'] === 'multiple' || $option['option_type'] === 'checkbox')) {
                    $_sel['value'] = explode(',', $option['option_value']);
                } else {
                    $_sel['value'] = array($option['value']);
                }
            }
            /*else
                //TODO*/

            array_push($selections, $_sel);
        }

        return $selections;
    }

    public function _createDataObject($id = null, $data = null)
    {
        $result = array();

        /* @var $order Mage_Sales_Model_Order */
        $order = $this->getModel('sales/order')->load($id);

        if ($order->getId()) {
            $orderItems = array();

            $childrenAux = array();

            foreach ($order->getItemsCollection() as $item) {
                if ($item->getParentItem()) {
                    $parentId = $item->getParentItemId();
                    if (array_key_exists($parentId, $childrenAux)) {
                        $childrenAux[$parentId]['discount'] += $item->getDiscountAmount();
                    } else {
                        $childrenAux[$parentId] = array('discount' => $item->getDiscountAmount());
                    }

                    continue;
                }

                $product = $this->getModel('catalog/product')->load($item->getProductId());

                $orderItems[$item->getId()] = array(
                    'name'           => $item->getName(),
                    'sku'            => $item->getSku(),
                    'product_id'     => (int)$item->getProductId(),
                    'item_id'        => (int)$item->getItemId(),
                    'product_type'   => $item->getProductType(),
                    'qty'            => ($item->getQtyOrdered() * 1),
                    'qty_invoiced'   => ($item->getQtyInvoiced() * 1),
                    'qty_shipped'    => ($item->getQtyShipped() * 1),
                    'qty_refunded'   => ($item->getQtyRefunded() * 1),
                    'qty_canceled'   => ($item->getQtyCanceled() * 1),
                    'price'          => (float)$item->getPrice(),
                    'tax_amount'     => (float)$item->getTaxAmount(),
                    'tax_compensation' => (float)$item->getTaxCompensation(),
                    'price_incl_tax' => (float)$item->getPriceInclTax(),
                    'tax_percent'    => (float)$item->getTaxPercent(),
                    'discount'       => (float)$item->getDiscountAmount(),
                    'total_invoiced' => (float)$item->getRowInvoiced(),
                    'options'        => $this->orderOptions($item),
                    'image_url'      => $product->getImageUrl(),
                    'bundle_items'   => array()
                );

                if ($item->getProductType() === 'bundle') {
                    $itemChildrens = $item->getChildrenItems();
                    foreach ($itemChildrens as $child) {
                        $orderItems[$item->getId()]['bundle_items'][] = array(
                            'name'           => $child->getName(),
                            'sku'            => $child->getSku(),
                            'product_id'     => (int)$child->getProductId(),
                            'item_id'        => (int)$child->getItemId(),
                            'product_type'   => $child->getProductType(),
                            'qty'            => ($child->getQtyOrdered() * 1),
                            'qty_invoiced'   => ($child->getQtyInvoiced() * 1),
                            'qty_shipped'    => ($child->getQtyShipped() * 1),
                            'qty_refunded'   => ($child->getQtyRefunded() * 1),
                            'qty_canceled'   => ($child->getQtyCanceled() * 1),
                            'price'          => (float)$child->getPrice(),
                            'tax_amount'     => (float)$child->getTaxAmount(),
                            'price_incl_tax' => (float)$child->getPriceInclTax(),
                            'tax_percent'    => (float)$child->getTaxPercent(),
                            'discount'       => (float)$child->getDiscountAmount(),
                            'total_invoiced' => (float)$child->getRowInvoiced(),
                            'options'        => $this->orderOptions($child),
                            'image_url'      => ""
                        );
                    }

                }

                $giftTypes = $this->getHelper('bakerloo_gifting')->getSupportedTypes();
                if (array_key_exists($item->getProductType(), $giftTypes)) {
                    $orderItems[$item->getId()]['gift_card_options'] = $this->getModel('bakerloo_restful/api_giftcards')->getOrderItemData($item);
                }
                
                if ($item->getProductType() === 'customercredit') {
                    $itemOptions = $item->getProductOptions();
                    $orderItems[$item->getId()]['store_credit_options'] = $itemOptions['info_buyRequest'];
                }

            }

            if (!empty($childrenAux)) {
                foreach ($childrenAux as $itemId => $iData) {
                    if (array_key_exists($itemId, $orderItems)) {
                        foreach ($iData as $key => $value) {
                            if ($value) {
                                $orderItems[$itemId][$key] = $value;
                            }
                        }
                    }
                }
            }


            $shippingAddress = is_object($order->getShippingAddress()) ? $order->getShippingAddress() : new Varien_Object;
            $billingAddress  = is_object($order->getBillingAddress()) ? $order->getBillingAddress() : new Varien_Object;

            $posOrder = $this->getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');

            $result += array(
                "pos_entity_id"        => (int)$posOrder->getId(),
                "entity_id"            => (int)$order->getId(),
                "status"               => $order->getStatusLabel(),
                "state"                => $order->getState(),
                "created_at"           => $this->formatDateISO($order->getCreatedAt()),
                "updated_at"           => $this->formatDateISO($order->getUpdatedAt()),
                "store_id"             => (int)$order->getStoreId(),
                "store_name"           => $order->getStoreName(),
                "store_view_name"      => Mage::app()->getStore()->getName(),
                "customer_id"          => (int)$order->getCustomerId(),
                "base_subtotal"        => (float)$order->getBaseSubtotal(),
                "subtotal"             => (float)$order->getSubtotal(),
                "base_grand_total"     => (float)$order->getBaseGrandTotal(),
                "base_total_paid"      => (float)$order->getBaseTotalPaid(),
                "grand_total"          => (float)$order->getGrandTotal(),
                "total_paid"           => (float)$order->getTotalPaid(),
                "tax_amount"           => (float)$order->getTaxAmount(),
                "discount_amount"      => (float)$order->getDiscountAmount(),
                "coupon_code"          => (string)$order->getCouponCode(),
                "shipping_description" => (string)$order->getShippingDescription(),
                "shipping_amount"      => (float)$order->getShippingInclTax(),
                "shipping_amount_refunded" => (float)$order->getShippingRefunded() + (float)$order->getShippingTaxRefunded(),
                "increment_id"         => $order->getIncrementId(),
                "currency_rate"        => (float)$order->getBaseToOrderRate(),
                "base_currency_code"   => $order->getBaseCurrencyCode(),
                "order_currency_code"  => $order->getOrderCurrencyCode(),
                "customer_email"       => (string)$order->getCustomerEmail(),
                "customer_firstname"   => (string)$order->getCustomerFirstname(),
                "customer_lastname"    => (string)$order->getCustomerLastname(),
                "customer_group"       => (int)$order->getCustomerGroupId(),
                "shipping_name"        => (string)$shippingAddress->getName(),
                "billing_name"         => (string)$billingAddress->getName(),
                "products"             => array_values($orderItems),
                "invoices"             => $this->_getAssociatedData($order->getId(), 'invoices'),
                "creditnotes"          => $this->_getAssociatedData($order->getId(), 'creditnotes'),
                "shipping_address"     => $this->_getOrderAddress($order, 'shipping'),
                "billing_address"      => $this->_getOrderAddress($order, 'billing'),
                "payment"              => $this->_getOrderPayments($order, $posOrder),
                "pos_order"            => $this->_getJsonPayload($posOrder)
            );
        }

        return $this->returnDataObject($result);
    }

    /**
     * Create order in Magento.
     *
     */
    public function post()
    {
        parent::post();
        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }
        Mage::app()->setCurrentStore($this->getStoreId());
        //Mage::log("Store: " . $this->getStoreId(), null, 'store.log', true);

        $data = $this->getJsonPayload(true);

        $order = new Varien_Object;
        //Save order data to local storage
        $posOrder = $this->saveOrder($order, $data, null, $this->getRequest()->getRawBody());
        $returnData = array(
            'order_id'     => null,
            'order_number' => null,
            'order_state'  => "",
            'order_status' => ""
        );
        try {
            $quote = $this->getHelper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data);
            $service = $this->getModel('sales/service_quote', false, $quote);
            $service->submitAll();
            $order = $service->getOrder();

            if ($order->getId()) {
                $order = $this->getModel('sales/order')->load($order->getId());
            }

            if ($order) {
                Mage::dispatchEvent(
                    'checkout_type_onepage_save_order_after',
                    array('order' => $order, 'quote' => $quote)
                );
            }
            Mage::dispatchEvent(
                'checkout_submit_all_after',
                array('order' => $order, 'quote' => $quote)
            );

            if (isset($data['returns']) && !empty($data['returns'])) {
                Mage::dispatchEvent('pos_order_has_returns', array('order_id' => $order->getIncrementId(), 'returned_items' => $this->getReturnDetails($data['returns'])));
            }

            /* Check payment method is Layaway */
            if ($data['payment']['method'] == Ebizmarts_BakerlooPayment_Model_Layaway::CODE) {
                Mage::dispatchEvent('pos_order_has_layaway_payment', array('order' => $order, 'payload' => $data, 'posorder' => $posOrder));
            }

            //Cancel order if its posted as canceled from device
            if (isset($data['order_state']) && ((int)$data['order_state'] === 4)) {
                $order->cancel()
                    ->save();
            }
            //Report price override if custom price has been entered
            if (isset($data['discount']) and $data['discount'] > 0) {
                $this->reportPriceOverride($order, $data);
            }

            //Invoice and ship
            if ($order->getId()) {
                list($invoiceConfig, $shipmentConfig) = $this->getInvoiceAndShipmentConfig($order);

                $transactionSave = $this->getModel('core/resource_transaction');

                if ($order->canInvoice() and $invoiceConfig) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                    $invoice->setTransactionId(time());
                    $invoice->register();

                    //Do no send invoice email
                    $invoice->setEmailSent(false);
                    $invoice->getOrder()->setCustomerNoteNotify(false);

                    //$invoice->setCreatedAt($data['order_date']);

                    $transactionSave->addObject($invoice)
                        ->addObject($invoice->getOrder());
                } elseif ($order->hasInvoices()) {
                    $invoice = $order->getInvoiceCollection()->getFirstItem();
                }

                $itemsShipmentQty = $this->getBundleItemQty($order);

                if (!$order->getIsVirtual() and isset($invoice)) {
                    //If not Virtual, create shipment if indicated.
                    $createShipment = false;
                    $shippingMethod = explode('_', $order->getShippingMethod(), 3);

                    if (!isset($shippingMethod[2])) {
                        $shipmentShip = $shipmentConfig;
                    } else {
                        $shipmentShip = (int)Mage::getStoreConfig('carriers/' . $shippingMethod[2] . '/ship');
                    }
                    if ((1 === $shipmentConfig) || ((2 === $shipmentConfig) && (1 === $shipmentShip))) {
                        $createShipment = true;
                    }

                    if ($createShipment) {
                        $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())
                            ->prepareShipment($itemsShipmentQty);
                        $shipment->register();
                        if ($shipment) {
                            //$shipment->setCreatedAt($data['order_date']);
                            $shipment->setEmailSent($invoice->getEmailSent());
                            $transactionSave->addObject($shipment)
                                ->addObject($shipment->getOrder());
                        }
                    }
                }

                if ($order->getId() && isset($data['comments'])) {
                    $order->addStatusHistoryComment($data['comments'])
                        ->setCustomerNote($data['comments'])
                        ->setIsVisibleOnFront(true)
                        ->setCustomerNoteNotify(true);

                    $transactionSave->addObject($order);
                }

                //if(isset($invoice) or isset($shipment))
                $transactionSave->save();
            }

            $this->saveOrder($order, $data, $posOrder->getId());

            $returnData['order_id']     = (int)$order->getId();
            $returnData['order_number'] = $order->getIncrementId();
            $returnData['order_state']  = $order->getState();
            $returnData['order_status'] = $order->getStatusLabel();
            $returnData['order_data']   = $this->_createDataObject($order->getId());
            $posOrder
                ->setFailMessage('')
                ->save();

            //Inactivate quote.
            $service->getQuote()->save();
        } catch (Exception $e) {
            Mage::logException($e);

            //set quote as not active if the order fails. (->setIsActive(false))
            if (isset($quote)) {
                $quote->setIsActive(false)->save();
            }

            $posOrderId = (int)$posOrder->getId();
            $message = $e->getMessage();

            $returnData['order_id']      = $posOrderId;
            $returnData['order_number']  = $posOrderId;
            $returnData['order_state']   = "notsaved";
            $returnData['order_status']  = "notsaved";
            $returnData['error_message'] = $message;
            $posOrder->setFailMessage($message)
                ->save();

            $helper = $this->getHelper('bakerloo_restful');

            $this->getHelper('bakerloo_restful/sales')->notifyAdmin(
                array(
                'severity'      => Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
                'date_added'    => Mage::getModel('core/date')->date(),
                'title'         => $helper->__("POS order number #%s failed.", $posOrderId),
                'description'   => $helper->__($message),
                'url'           => null /*Mage::helper('adminhtml')->getUrl('adminhtml/bakerlooorders/', array('id' => $posOrderId))*/,
                )
            );
        }

        $this->saveOrder($order, $data, $posOrder->getId());

        //call observers after posOrder save
        if (isset($returnData['order_data'])) {
            $returnData['order_data'] = $this->returnDataObject($returnData['order_data']);
        }


        return $returnData;
    }

    /**
     *
     */
    public function getReturnDetails($returnedProducts)
    {
        $returnedProductDetails = array();

        foreach ($returnedProducts as $prod) {
            if (isset($prod['bundle_option']) && !empty($prod['bundle_option'])) {
                $bundleQty = $prod['qty'];

                $bundledProducts = $prod['bundle_option'];
                foreach ($bundledProducts as $bundledProd) {
                    foreach ($bundledProd['selections'] as $selectedProd) {
                        if ($selectedProd['selected'] == true) {
                            $productDetails = array(
                                'product_id' => $selectedProd['product_id'],
                                'product_qty' => $selectedProd['qty'] * $bundleQty
                            );
                            $returnedProductDetails[] = $productDetails;
                        }
                    }
                }
            } elseif (isset($prod['type']) && $prod['type'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) { // 'configurable'){
                $productDetails = array(
                    'product_id' => $prod['child_id'],
                    'product_qty' => $prod['qty']
                );
                $returnedProductDetails[] = $productDetails;
            } else {
                $productDetails = array(
                    'product_id' => $prod['product_id'],
                    'product_qty' => $prod['qty']
                );
                $returnedProductDetails[] = $productDetails;
            }
        }

        return $returnedProductDetails;
    }

    public function reportPriceOverride($order, $orderData)
    {
        $totalBefore = $orderData['total_amount'];
        $discount = $orderData['discount'];
        $totalAfter = $totalBefore - $discount;

        //save discount to custom price table
        $this->getModel('bakerloo_restful/customPrice')
            ->setId(null)
            ->setOrderId($order->getId())
            ->setOrderIncrementId($order->getIncrementId())
            ->setAdminUser($orderData['user'])
            ->setStoreId($order->getStoreId())
            ->setTotalDiscount($discount)
            ->setGrandTotalBeforeDiscount($totalBefore)
            ->setGrandTotalAfterDiscount($totalAfter)
            ->save();

        //check email config and send
        $helper = $this->getHelper('bakerloo_restful');
        $notifyFlag = $helper->config('custom_discount_email/enabled', $this->getStoreId());

        if ($notifyFlag) {
            $notifyPercent = (int)$helper->config('custom_discount_email/minimum_percent', $this->getStoreId());

            $orderPercent = ($totalBefore != 0) ? $discount/$totalBefore * 100 : 0;
            if ($orderPercent >= $notifyPercent) {
                $this->sendPriceOverrideEmail($order, $discount);
            }
        }

        return $this;
    }

    public function sendPriceOverrideEmail(Mage_Sales_Model_Order $order, $discountAmount)
    {
        /** @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = $this->getModel('core/email_template');
        $emailTemplate->loadDefault(self::PRICE_OVERRIDE_EMAIL_TEMPLATE);

        $adminName  = $this->getStoreConfig('trans_email/ident_general/name', $this->getStoreId());
        $adminEmail = $this->getStoreConfig('trans_email/ident_general/email', $this->getStoreId());

        $discount = sprintf('%s %d', $order->getBaseCurrencyCode(), $discountAmount);
        $emailTemplateVars = array(
            'order_id' => $order->getIncrementId(),
            'discount' => $discount
        );

        $emailTemplate->setSenderName($adminName);
        $emailTemplate->setSenderEmail($adminEmail);
        $emailTemplate->send($adminEmail, null, $emailTemplateVars);
    }

    /**
     * Cancel order
     */
    public function delete()
    {
        parent::delete();

        $orderId = $this->_getIdentifier();

        $order = $this->getModel($this->_model)->load($orderId);

        if ($order->getId()) {
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
            } else {
                Mage::throwException("Order can not be canceled.");
            }
        } else {
            Mage::throwException("Order does not exist.");
        }

        return array(
            'order_id'     => (int)$order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel()
        );
    }

    /**
     * @return Object|Varien_Data_Collection
     */
    protected function _getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = $this->getResourceModel('sales/order_collection');
        }

        return $this->_collection;
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param boolean $useOR
     */
    public function applyFilters($filters, $useOR = false)
    {
        if ($this->_pickUpSearch) {
            parent::applyFilters($filters, $this->_filterUseOR);
        }
        else if (count($filters) == 1) {
            $filter = $this->explodeFilter($filters[0]);

            if ("increment_id" == $filter[0] && $filter[1] == 'eq') {

                $orderByIncrementId = $this->getModel($this->_model)->loadByIncrementId($filter[2]);
                if ($orderByIncrementId->getId()) {
                    $this->_getCollection()->getSelect()->where('main_table.increment_id = ?', $filter[2]);
                } else {
                    $this->_filterByDeviceOrderId($filter[2]);
                }

            } else {
                if ("order_guid" == $filter[0] && $filter[1] == 'eq') {
                    $this->_getCollection()->getSelect()->joinLeft(
                        array('pos' => $this->_posTableName()),
                        'main_table.entity_id = pos.order_id',
                        array()
                    )->where('pos.order_guid = ?', $filter[2]);
                } else {
                    parent::applyFilters($filters, true);
                }
            }
        } else {
            $magicSearch = false;

            foreach ($filters as $_filterString) {
                $fl = $this->explodeFilter($_filterString);

                //Try search by device_order_id
                if ($fl[0] == 'increment_id') {
                    $collection = $this->getModel('bakerloo_restful/order')->getCollection();
                    $collection->addFieldToFilter('device_order_id', array($fl[1] => $fl[2]));

                    if (((int)$collection->getSize() > 0)) {
                        $magicSearch = true;
                        $this->_filterByDeviceOrderId($fl[2]);
                    }
                }
            }

            if (!$magicSearch) {

                // State filter should be applied with AND, while all other filters use OR.
                $stateFilterPosition = $this->getFilterByName('state', null, true);
                $stateFilter = $this->getFilterByName('state');
                if (!is_null($stateFilterPosition)) {
                    unset($filters[$stateFilterPosition]);
                }

                parent::applyFilters($filters, $this->_filterUseOR);

                if (!is_null($stateFilterPosition)) {
                    $stateFilter = $this->explodeFilter($stateFilter);
                    if (array_key_exists(2, $stateFilter) and !empty($stateFilter[2])){
                        $this->_getCollection()->addFieldToFilter($stateFilter[0], array($stateFilter[1] => $stateFilter[2]));
                    }
                }
            }
        }
    }

    protected function _posTableName()
    {
        return $this->getModel('core/resource', true)->getTableName('bakerloo_restful/order');
    }

    protected function _filterByDeviceOrderId($orderId)
    {
        $this->_collection->getSelect()->joinLeft(
            array('pos' => $this->_posTableName()),
            'main_table.entity_id = pos.order_id',
            array()
        )->where('pos.device_order_id LIKE ?', $orderId);
    }

    /**
     * Save order in local table POS > Orders.
     *
     * @param  int   $id      [description]
     * @param  Mage_Sales_Model_Order   $order   [description]
     * @param  stdClass $data    [description]
     * @param  string   $rawData [description]
     * @return Ebizmarts_BakerlooRestful_Model_Order            [description]
     */
    public function saveOrder($order, $data, $id = null, $rawData = null)
    {
//        Mage::log('Saving bakerloo order.');

        /** @var Ebizmarts_BakerlooRestful_Model_Order $_bakerlooOrder */
        $_bakerlooOrder = $this->getModel('bakerloo_restful/order');
        $headerId = (int)$this->_getRequestHeader('B-Order-Id');
        if ($headerId) {
            $id = $headerId;
        }

        if (!is_null($id)) {
            $_bakerlooOrder->load($id);
        } else {
            $this->validatePostData($data);

            //Store request headers in local table first time
            //so if it fails we can retry with all original data
            $requestHeaders = array();
            foreach ($this->getHelper('bakerloo_restful')->allPossibleHeaders() as $_rqh) {
                $value = (string)$this->_getRequestHeader($_rqh);
                if (!empty($value)) {
                    $requestHeaders[$_rqh] = $value;
                }
            }
            $_bakerlooOrder->setJsonRequestHeaders(json_encode($requestHeaders));
        }
        //Save order in custom table
        $_bakerlooOrder
            ->setOrderIncrementId($order->getIncrementId())
            ->setOrderId($order->getId())
            ->setAdminUser($data['user'])
            ->setLoginUser($this->getUsername())
            ->setLoginUserAuth($this->getUsernameAuth())
            ->setSalesperson((isset($data['salesperson']) ? $data['salesperson'] : null))
            ->setRemoteIp($this->getHelper('core/http')->getRemoteAddr())
            ->setDeviceId($this->getDeviceId())
            ->setUserAgent($this->getUserAgent())
            ->setRequestUrl($this->getHelper('core/url')->getCurrentUrl()); //@TODO: Check this.

        if (!is_null($rawData)) {
//            Mage::log('Order has raw data. ');

            $_rawData = json_decode($rawData, true);

            if (isset($_rawData['payment']['customer_signature'])) {
                $_bakerlooOrder->setCustomerSignature($_rawData['payment']['customer_signature']);
                unset($_rawData['payment']['customer_signature']);
            }

            if (isset($_rawData['timezone']) and !$_bakerlooOrder->getId()) {
                $_rawData['local_delivery_date'] = $this->getHelper('bakerloo_restful')
                    ->convertDateFromUTCtoTimezone($_rawData['delivery_date'], $_rawData['timezone']);
            }

            $_bakerlooOrder->setJsonPayload(json_encode($_rawData));
//            Mage::log('Json payload saved. ');
        }
        //Device Order ID
        if (isset($data['internal_id'])) {
            $_bakerlooOrder->setDeviceOrderId($data['internal_id']);
        }
        if (isset($data['order_guid'])) {
            $_bakerlooOrder->setOrderGuid($data['order_guid']);
        }
        if (isset($data['auth_user'])) {
            $_bakerlooOrder->setAdminUserAuth($data['auth_user']);
        }
        if (isset($data['customer']['is_default_customer'])) {
            $usesDefault = !is_null($data['customer']['is_default_customer']) ? $data['customer']['is_default_customer'] : 0;
            $_bakerlooOrder->setUsesDefaultCustomer($usesDefault);
        }
        if ($this->getLatitude()) {
            $_bakerlooOrder->setLatitude($this->getLatitude());
        }
        if ($this->getLongitude()) {
            $_bakerlooOrder->setLongitude($this->getLongitude());
        }

        //Store additional data.
        $additional = array(
            'store_id',
            'grand_total',
            'subtotal',
            'base_subtotal',
            'base_grand_total',
            'base_shipping_amount',
            'base_tax_amount',
            'base_to_global_rate',
            'base_to_order_rate',
            'base_currency_code',
            'tax_amount',
            'store_to_base_rate',
            'store_to_order_rate',
            'global_currency_code',
            'order_currency_code',
            'store_currency_code',
        );
        foreach ($additional as $_attribute) {
            $_bakerlooOrder->setData($_attribute, $order->getData($_attribute));
        }

        if ($order->getPayment()) {
            $_bakerlooOrder->setPaymentMethod($order->getPayment()->getMethod());
        }

        $_bakerlooOrder->save();

        if ($order->getId()) {
            $_bakerlooOrder->setRealCreatedAtToParent();
        }
//        Mage::log('Order saved. ');
        return $_bakerlooOrder;
    }

    private function validatePostData($data)
    {
        $helper = $this->getHelper('bakerloo_restful');

        // Verify mandatory fields are present
        $fields = array(
            'order_id', 'id', 'order_guid', 'internal_id'
        );

        foreach ($fields as $_field) {
            if (!array_key_exists($_field, $data)) {
                Mage::throwException($helper->__("Invalid order data."));
            }
        }

        // Check for duplicates by order_guid
        if (is_null($data['order_guid'])) {
            Mage::throwException($helper->__("Invalid order data."));
        }

        if (is_null($data['internal_id'])) {
            Mage::throwException($helper->__("Invalid order data."));
        }

        $duplicate = $this->getModel('bakerloo_restful/order')->load($data['order_guid'], 'order_guid');
        if ($duplicate->getId()) {
            Mage::throwException("Duplicate POST for `{$data['order_guid']}`.");
        }

    }

    /**
     * Given an order ID, send order email.
     *
     * @return array Email sending result
     */
    public function sendEmail()
    {

        //get data
        $orderId = (int)$this->_getQueryParameter('orderId');
        $customEmail = (string)$this->_getQueryParameter('email');
        $storeEmail = (string)Mage::app()->getStore()->getConfig('trans_email/ident_general/email');

        //Load order and check if exists.
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);

        if (!$order->getId()) {
            Mage::throwException("Order does not exist.");
        }

        Mage::app()->setCurrentStore($order->getStoreId());

        //send email if custom email is valid and different from store email
        $email = filter_var($customEmail, FILTER_VALIDATE_EMAIL) ? $customEmail : $order->getCustomerEmail();

        if ($storeEmail != $email) {
            $emailSent = $this->insertEmail($order, $email);
        } else {
            $emailSent = false;
        }

        //return a jSon object with order data and email status
        $result = array(
            'order_id'     => (int)$order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel(),
            'email_sent'   => $emailSent
        );
        return $result;
    }

    /**
     * @param $order
     */
    public function insertEmail(Mage_Sales_Model_Order $order, $customEmail = null)
    {

        $inserted = false;
        $helper = $this->getHelper('bakerloo_restful');

        if ($this->getRequest()->isPost()) {
            $data = $this->getJsonPayload();
        } else {
            $data = new stdClass();
        }

        $salesHelper = $this->getHelper('bakerloo_restful/sales');
        //Add customer from email if email is valid and customer is new
        $customer = $salesHelper->customerExists($customEmail, Mage::app()->getStore()->getWebsiteId());
        $createConfig = (int)$helper->config('checkout/create_customer');
        $customerInOrderIsGuestOrDefault = $salesHelper->customerInOrderIsGuestOrDefault($order);

        if ($customer === false) {
            if ($createConfig) {
                $this->addCustomer($customEmail, $order, false);
            }
        } elseif ($customerInOrderIsGuestOrDefault && $customer->getId()) {
            $this->setCustomerToOrder($customer, $order);
            $bakerlooOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');
            $bakerlooOrder->setUsesDefaultCustomer(0)->save();
        }

        //Register flag for workaround in Magento version 1.8 or lower.
        if (!Mage::registry('pos_send_email_to')) {
            Mage::register('pos_send_email_to', $order->getCustomerEmail());
        }

        $emailType = (string)$helper->config('pos_receipt/receipts', $this->getStoreId());
        $subscribeToNewsletter = (bool)$this->_getQueryParameter('subscribe_to_newsletter');

        //Add incidence to bakerloo_email/unsent_emails table
        $unsentQueue = $this->insertUnsentEmail($order, $emailType, $customEmail);
        if ($unsentQueue->getId()) {
            $inserted = true;

            //Add incidence to bakerloo_email/log table
            $queue = $this->logEmail($order, $emailType, $subscribeToNewsletter, null, $customEmail);

            //Save attachment if set
            if (isset($data->attachments) and is_array($data->attachments) and !empty($data->attachments)) {
                $receiptData = current($data->attachments);

                //Store image name in database.
                $unsentQueue->setAttachment($receiptData->name)->save();
                $queue->setAttachment($receiptData->name)->save();

                //Store receipt on disk.
                $receiptsStorage = $this->getHelper('bakerloo_restful/cli')->getPathToDb($order->getStoreId(), 'receipts', false);
                $contents = base64_decode($receiptData->content);
                $saved = false;

                if ($contents !== false) {
                    $saved = file_put_contents($receiptsStorage . DS . $receiptData->name, $contents);
                }

                if ($saved === false) {
                     $queue->setEmailResult(false)
                        ->setErrorMessage($helper->__("Receipt for order {$order->getId()} not saved. "))
                        ->save();
                }
            }
        }

        //Subscribe email to newsletter if indicated
        if ($subscribeToNewsletter) {
            $this->subscribeToNewsletter($customEmail);
        }

        $order->save();

        return $inserted;
    }

    public function logEmail($order, $emailType, $newsletterSubscription = null, $error = null, $emailTo = null)
    {
        $emailTo = is_null($emailTo) ? $order->getCustomerEmail() : $emailTo;

        $row = Mage::getModel('bakerloo_email/queue')
            ->setId(null)
            ->setOrderId($order->getId())
            ->setCustomerId($order->getCustomerId())
            ->setToEmail($emailTo)
            ->setEmailType($emailType)
            ->setSubscribeToNewsletter((int)$newsletterSubscription)
            ->setEmailResult(false)
            ->save();

        if (isset($error)) {
            $row->setEmailResult(false)
                ->setErrorMessage($error)
                ->save();
        }

        return $row;
    }

    public function insertUnsentEmail($order, $emailType, $customEmail = null)
    {

        $emailTo = is_null($customEmail) ? $order->getCustomerEmail() : $customEmail;

        $rows = Mage::getModel('bakerloo_email/unsent')
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq', $order->getId()))
            ->addFieldToFilter('to_email', array('eq', $emailTo));

        if ($rows->count() == 0) {
            $row = Mage::getModel('bakerloo_email/unsent')
                ->setId(null)
                ->setOrderId($order->getId())
                ->setCustomerId($order->getCustomerId())
                ->setToEmail($emailTo)
                ->setEmailType($emailType)
                ->save();
        } else {
            $row = $rows->getFirstItem();
            $row->setCustomerId($order->getCustomerId())
                ->setEmailType($emailType)
                ->save();
        }

        return $row;
    }

    public function subscribeToNewsletter($email)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);

        $subscriberCollection = Mage::getModel('newsletter/subscriber')->getCollection()
            ->addFieldToFilter('subscriber_email', array('eq' => $email));
        $duplicateSubscriber = current($subscriberCollection->getItems());

        if ($duplicateSubscriber !== false && !$duplicateSubscriber->getId()) {
            if ($customer->getId()) {
                $customer->setIsSubscribed(1);
                $customer->save();

                Mage::getModel('newsletter/subscriber')->subscribe($email);
                $subscribedCustomer = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                $subscribedCustomer->setCustomerId($customer->getId());
                $subscribedCustomer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $subscribedCustomer->save();
            }
        } else {
            Mage::getModel('newsletter/subscriber')->subscribe($email);
        }
    }

    /**
     * @param $email
     * @return mixed
     */
    public function customerExists($email)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        return $this->getHelper('bakerloo_restful/sales')->customerExists($email, $websiteId);
    }

    /**
     * @param $order
     * @param $newEmail
     * @return mixed
     */
    public function swapOrderEmail($order, $newEmail)
    {
        $validCustomEmail = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
        if ($validCustomEmail) {
            $order->setCustomerEmail($newEmail)->save();
        }
    }

    /**
     * @param $email
     * @param $order
     * @param $changedCustomer
     *
     * @return bool
     *
     * Adds a customer to Magento customers from supplied email
     */
    public function addCustomer($email, Mage_Sales_Model_Order $order, $changedCustomer = false)
    {
        $name = substr($email, 0, strpos($email, '@'));

        $customerData = array();
        $customerData['customer'] = array(
            'group_id'  => Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, Mage::app()->getStore()->getId()),
            'email'     => $email,
            'firstname' => $name,
            'lastname'  => $name
        );

        $websiteId = Mage::app()->getStore()->getWebsiteId();

        $newCustomer = $this->getHelper('bakerloo_restful')->createCustomer($websiteId, $customerData);
        //@TODO: Add addresses if not equal to store.

        $customerInOrderIsGuestOrDefault = $this->getHelper('bakerloo_restful/sales')->customerInOrderIsGuestOrDefault($order);

        //Associate customer to order.
        if ($newCustomer->getId() and $customerInOrderIsGuestOrDefault) {
            $this->setCustomerToOrder($newCustomer, $order);

            $this->getModel('bakerloo_restful/order')
                ->load($order->getId(), 'order_id')
                ->setUsesDefaultCustomer(0)
                ->save();

            $changedCustomer = true;
            unset($currentEmail);

            //Register flag for workaround in Magento version 1.8 or lower.
            Mage::register('pos_send_email_to', $newCustomer);
        }

        return $changedCustomer;
    }

    /**
     * Search orders by POS order number.
     *
     * @return array|Varien_Object
     */
    public function searchByPosOrderId()
    {

        $id = (int)$this->_getQueryParameter('id');

        $collection = Mage::getModel('bakerloo_restful/order')->getCollection();
        $collection->addFieldToFilter('id', $id);

        $order = new Varien_Object;
        if ($collection->getSize()) {
            $_order = $this->_createDataObject($collection->getFirstItem()->getOrderId());

            if (is_array($_order) and isset($_order['entity_id'])) {
                $order = $_order;
            }
        }

        return $order;
    }


    /**
     * @return int
     */
    public function processUnsentEmails()
    {
        //check email sending enabled
        $enabled = Mage::getStoreConfig('bakerloorestful/order_emails/enabled', Mage::app()->getStore());
        $sentEmails = 0;

        if ($enabled) {
            $unsentQueue = $this->getModel('bakerloo_email/unsent')->getCollection();

            foreach ($unsentQueue as $unsentEmail) {
                $emailType = $unsentEmail->getEmailType();

                $orderId = (int)$unsentEmail->getOrderId();

                /* @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->load($orderId);
                if (!$order->getId()) {
                    continue;
                }

                //swap order email if different from unsent email address
                $orderEmailAddress = $order->getCustomerEmail();
                $unsentEmailAddress = $unsentEmail->getToEmail();
                if (strcmp($unsentEmailAddress, $orderEmailAddress) != 0) {
                    $this->swapOrderEmail($order, $unsentEmailAddress);
                }

                $receiptsStorage = $this->getHelper('bakerloo_restful/cli')->getPathToDb($order->getStoreId(), 'receipts', false);
                $fullPath = $receiptsStorage . DS . $unsentEmail->getAttachment();
                $contents = file_get_contents($fullPath);
                $attachment = new stdClass();

                if ($contents !== false) {
                    $attachment->name = $unsentEmail->getAttachment();
                    $attachment->content = base64_encode($contents);
                    $attachment->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath);
                }

                $emailSent = false;
                try {
                    if ($emailType == 'magento') {
                        $order->sendNewOrderEmail();
                        $emailSent = (bool)$order->getEmailSent();
                    } elseif ($emailType == 'receipt') {
                        $receipt = $this->getHelper('bakerloo_restful/email')->sendReceipt($order, $attachment);
                        $emailSent = (bool)$receipt->getEmailSent();
                    } else {
                        $order->sendNewOrderEmail();
                        $receipt = $this->getHelper('bakerloo_restful/email')->sendReceipt($order, $attachment);
                        $emailSent = (bool)($order->getEmailSent() or $receipt->getEmailSent());
                    }

                    if ($emailSent) {
                        $this->updateEmailStatus($order, true);
                        $unsentEmail->delete();
                        $sentEmails++;

                    } else {
                        $this->updateEmailStatus($order, false);
                    }
                } catch (Exception $e) {
                    Mage::logException($e);

                    //Add row to email log reflecting failed attempt
                    $this->logEmail($order, $emailType, null, $e->getMessage());
                }

                //reset old order email
                $this->swapOrderEmail($order, $orderEmailAddress);
            }
        }

        return $sentEmails;
    }

    public function updateEmailStatus($order, $status)
    {

        //Add comment to order.
        if ($status) {
            $order->addStatusHistoryComment($this->getHelper('bakerloo_restful')->__("Order email sent to email address: \"%s\"", $order->getCustomerEmail()), false)
                ->setIsVisibleOnFront(false)
                ->setIsCustomerNotified(false)
                ->save();
        }

        //Set send in corresponding queue record
        $queuedEmails = Mage::getModel('bakerloo_email/queue')->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $order->getId()));

        foreach ($queuedEmails as $queuedEmail) {
            $queuedEmail->setEmailResult($status)->save();
        }
    }

    /**
     * PUT - Update original order JSON.
     */
    public function updateOrigOrder()
    {

        $id = (int)$this->_getQueryParameter('id');

        $posOrder = Mage::getModel('bakerloo_restful/order')->load($id);

        if (!$posOrder->getId()) {
            Mage::throwException('Order does not exist.');
        }

        $jsonObj = $this->getJsonPayload();

        $posOrder->setJsonPayload(json_encode($jsonObj));

        $posOrder->save();

        return $this->_createDataObject($posOrder->getOrderId());
    }

    /**
     * Return ready to pickup orders.
     *
     * @return array
     */
    public function readyToPickup()
    {
        $this->_pickUpSearch = true;
        
        //get page
        $page = $this->_getQueryParameter('page');
        if (!$page) {
            $page = 1;
        }

        //Retrieve orders not completed and placed with our shipping method.
        $myFilters = array(
            'shipping_method,eq,bakerloo_store_pickup_bakerloo_store_pickup',
            'state,neq,complete',
            'state,neq,closed',
            'total_paid,notnull,',
        );

        $filters = $this->_getQueryParameter('filters');

        if (is_null($filters)) {
            $filters = $myFilters;
        } else {
            $filters = array_merge($filters, $myFilters);
        }

        $this->_filterUseOR = false;

        return $this->_getAllItems($page, $filters);
    }

    private function _getOrderAddress($order, $type)
    {
        if ($type == 'billing') {
            $address = $order->getBillingAddress();
        } else {
            $address = $order->getShippingAddress();
        }

        if (!is_object($address)) {
            return null;
        }

        $return = array(
            "id"                  => $address->getId(),
            "firstname"           => $address->getFirstname(),
            "lastname"            => $address->getLastname(),
            "country_id"          => $address->getCountry(),
            "city"                => $address->getCity(),
            "street"              => $address->getStreet(1),
            "street1"             => $address->getStreet(2),
            "region"              => $address->getRegion(),
            "region_id"           => $address->getRegionId(),
            "postcode"            => $address->getPostcode(),
            "telephone"           => $address->getTelephone(),
            "fax"                 => $address->getFax(),
            "company"             => $address->getCompany(),
            "is_shipping_address" => (int)($type == 'shipping'),
            "is_billing_address"  => (int)($type == 'billing'),
        );

        return $return;
    }

    public function _getAssociatedData($orderId, $resource)
    {

        $api = Mage::getModel('bakerloo_restful/api_' . $resource);
        $api->parameters = array(
            'not_by_id'=>'not_by_id',
            'filters' => array('order_id,eq,' . $orderId)
        );

        $invoices = $api->get();

        if (is_array($invoices) and array_key_exists('page_data', $invoices)) {
            return $invoices['page_data'];
        } else {
            return array();
        }
    }

    private function _getJsonPayload(Ebizmarts_BakerlooRestful_Model_Order $order)
    {
        $payload = json_decode($order->getJsonPayload(), true);

        if ($payload) {
            $payload['payment']['customer_signature'] = null;
            $payload['payment']['customer_signature_type'] = null;
            $payload['payment']['customer_signature_file'] = null;

            $addedPayments = isset($payload['payment']['addedPayments']) ? $payload['payment']['addedPayments'] : array();
            foreach ($addedPayments as $_addedPayment) {
                $_addedPayment['customer_signature'] = null;
                $_addedPayment['customer_signature_type'] = null;
                $_addedPayment['customer_signature_file'] = null;
            }

            $payload['currency_rate'] = (float)$order->getBaseToOrderRate();
        }

        return $payload;
    }

    public function _getOrderPayments(Mage_Sales_Model_Order $order, Ebizmarts_BakerlooRestful_Model_Order $posOrder)
    {

        if (!$posOrder->getId()) {
            return;
        }

        $json = json_decode($posOrder->getJsonPayload(), true);
        $payment = $order->getPayment();
        $result = null;

        if (!is_null($json) and $payment->getId()) {
            $result = isset($json['payment']) ? $json['payment'] : array();

            $result['payment_id'] = (int)$payment->getId();
            $json['payment']['payment_id'] = (int)$payment->getId();

            if (isset($result['customer_signature'])) {
                $result['customer_signature'] = null;
            }
            if (isset($result['customer_signature_type'])) {
                $result['customer_signature_type'] = null;
            }
            if (isset($result['customer_signature_file'])) {
                $result['customer_signature_file'] = null;
            }

            if ($payment->getMethod() == Ebizmarts_BakerlooPayment_Model_Layaway::CODE) {
                if (isset($result['addedPayments']) and is_array($result['addedPayments'])) {
                    $installments = Mage::getModel('bakerloo_payment/installment')
                        ->getCollection()
                        ->addFieldToFilter('parent_id', array('eq' => $payment->getId()))
                        ->getItems();

                    $installments = array_values($installments);
                    $installmentKeys = array_keys($installments);

                    //added payments from json
                    $addedPayments = $result['addedPayments'];
                    $addedPaymentKeys = array_keys($addedPayments);

                    $result['addedPayments'] = array();
                    $result['refunds'] = array();

                    foreach ($installmentKeys as $_key) {
                        $_installment = $installments[$_key];
                        $installmentJson = unserialize($_installment->getPaymentData());

                        if ($installmentJson) {
                            $result['addedPayments'][$_key] = $installmentJson;
                        } else {
                            $result['addedPayments'][$_key] = $addedPayments[$_key];
                        }

                        $result['addedPayments'][$_key]['payment_id'] = $_installment->getPaymentId();

                        if (!empty($installmentJson['refunds'])) {
                            $installmentRefunds = $installmentJson['refunds'];
                        } else {
                            $installmentRefunds = $addedPayments[$_key]['refunds'];
                        }

                        foreach ($installmentRefunds as $_refundKey => $_refund) {
                            $installmentRefunds[$_refundKey]['refund_id'] = $_installment->getId();
                        }

                        $result['addedPayments'][$_key]['refunds'] = $installmentRefunds;
                    }

                    //check installments that may have failed
                    $diff = array_diff($addedPaymentKeys, $installmentKeys);
                    foreach ($diff as $_d) {
                        $result['addedPayments'][] = $addedPayments[$_d];
                    }


                }
            }
        }

        return $result;
    }

    public function setCustomerToOrder($customer, $order)
    {
        $order->setData('customer_id', $customer->getId());
        $order->setData('customer_is_guest', 0);
        $order->setData('customer_email', $customer->getEmail());
        $order->setData('customer_firstname', $customer->getFirstname());
        $order->setData('customer_lastname', $customer->getLastname());
        $order->setData('customer_group_id', $customer->getGroupId());
    }

    /**
     * @param $order
     * @return array
     */
    protected function getInvoiceAndShipmentConfig($order)
    {
        $invoiceConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("invoice");
        $shipmentConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("ship");

        if ($order->getPayment()->getMethod() == 'free') {
            $invoiceConfig = (int)Mage::getStoreConfig('payment/bakerloo_free/invoice', $this->getStore());
            $shipmentConfig = (int)Mage::getStoreConfig('payment/bakerloo_free/ship', $this->getStore());
        } elseif ($order->getPayment()->getMethod() == 'bakerloo_layaway') {
            $invoiceConfig = 0;
            $shipmentConfig = 0;
        }

        return array($invoiceConfig, $shipmentConfig);
    }

    /**
     * @param $order
     * @return array
     */
    private function getBundleItemQty($order)
    {
        $itemsShipmentQty = array();

        foreach ($order->getItemsCollection() as $item) {
            if ($item->getProductType() === 'bundle' && !$item->isShipSeparately()) {
                $itemsShipmentQty[$item->getId()] = $item->getQtyOrdered();
            } elseif (!is_null($item->getParentItem()) && $item->getParentItem()->getProductType() === 'bundle' && $item->isShipSeparately()) {
                $itemsShipmentQty[$item->getId()] = $item->getQtyOrdered();
            }
        }
        return $itemsShipmentQty;
    }
}
