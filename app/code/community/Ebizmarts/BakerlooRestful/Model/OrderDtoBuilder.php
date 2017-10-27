<?php

class Ebizmarts_BakerlooRestful_Model_OrderDtoBuilder
{
    const POS_ENTITY_ID     = 'pos_entity_id';
    const ENTITY_ID         = 'entity_id';
    const INCREMENT_ID      = 'increment_id';
    const STATUS            = 'status';
    const STATE             = 'state';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';
    const STORE_ID          = 'store_id';
    const STORE_NAME        = 'store_name';
    const STOREVIEW_NAME    = 'store_view_name';
    const CUSTOMER_ID       = 'customer_id';
    const CUSTOMER_EMAIL    = 'customer_email';
    const CUSTOMER_FIRSTNAME = 'customer_firstname';
    const CUSTOMER_LASTNAME = 'customer_lastname';
    const CUSTOMER_GROUP    = 'customer_group';
    const BASE_SUBTOTAL     = 'base_subtotal';
    const SUBTOTAL          = 'subtotal';
    const BASE_GRAND_TOTAL  = 'base_grand_total';
    const GRAND_TOTAL       = 'grand_total';
    const BASE_TOTAL_PAID   = 'base_total_paid';
    const TOTAL_PAID        = 'total_paid';
    const TAX_AMOUNT        = 'tax_amount';
    const DISCOUNT_AMOUNT   = 'discount_amount';
    const COUPON_CODE       = 'coupon_code';
    const SHIPPING_DESC     = 'shipping_description';
    const SHIPPING_AMT      = 'shipping_amount';
    const SHIPPING_AMT_REF  = 'shipping_amount_refunded';
    const CURRENCY_RATE     = 'currency_rate';
    const BASE_CURR_CODE    = 'base_currency_code';
    const ORDER_CURR_CODE   = 'order_currency_code';
    const SHIPPING_NAME     = 'shipping_name';
    const BILLING_NAME      = 'billing_name';
    const PRODUCTS          = 'products';
    const INVOICES          = 'invoices';
    const CREDITNOTES       = 'creditnotes';
    const BILLING_ADDRESS   = 'billing_address';
    const SHIPPING_ADDRESS  = 'shipping_address';
    const PAYMENT           = 'payment';
    const POS_ORDER         = 'pos_order';

    const ADDRESS_ID            = "id";
    const ADDRESS_FIRSTNAME     = "firstname";
    const ADDRESS_LASTNAME      = "lastname";
    const ADDRESS_COUNTRY_ID    = "country_id";
    const ADDRESS_CITY          = "city";
    const ADDRESS_STREET        = "street";
    const ADDRESS_STREET1       = "street1";
    const ADDRESS_REGION        = "region";
    const ADDRESS_REGION_ID     = "region_id";
    const ADDRESS_POSTCODE      = "postcode";
    const ADDRESS_TELEPHONE     = "telephone";
    const ADDRESS_FAX           = "fax";
    const ADDRESS_COMPANY       = "company";
    const ADDRESS_IS_SHIPPING   = "is_shipping_address";
    const ADDRESS_IS_BILLING    = "is_billing_address";

    /** @var Ebizmarts_BakerlooRestful_Helper_Data  */
    private $_helper;

    /** @var Ebizmarts_BakerlooRestful_Model_Api_Giftcards */
    private $_giftCardsApi;

    /** @var Ebizmarts_BakerlooGifting_Helper_Data  */
    private $_giftCardsHelper;

    public function __construct($args = array())
    {
        if (!isset($args['helper'])) {
            $this->_helper = Mage::helper('bakerloo_restful');
        } else {
            $this->_helper = $args['helper'];
        }

        if (!isset($args['gift_cards_api'])) {
            $this->_giftCardsApi = Mage::getModel('bakerloo_restful/api_giftcards');
        } else {
            $this->_giftCardsApi = $args['gift_cards_api'];
        }

        if (!isset($args['gift_cards_helper'])) {
            $this->_giftCardsHelper = Mage::helper('bakerloo_gifting');
        } else {
            $this->_giftCardsHelper = $args['gift_cards_helper'];
        }
    }

    public function getDataObject(Mage_Sales_Model_Order $order, Ebizmarts_BakerlooRestful_Model_Order $posOrder)
    {
        $dto = array();

        if ($order->getId()) {

            $dto[self::POS_ENTITY_ID]       = (int)$posOrder->getId();
            $dto[self::ENTITY_ID]           = (int)$order->getId();
            $dto[self::INCREMENT_ID]        = $order->getIncrementId();
            $dto[self::STATUS]              = $order->getStatusLabel();
            $dto[self::STATE]               = $order->getState();
            $dto[self::CREATED_AT]          = $this->_helper->formatDateISO($order->getCreatedAt());
            $dto[self::UPDATED_AT]          = $this->_helper->formatDateISO($order->getUpdatedAt());
            $dto[self::STORE_ID]            = (int)$order->getStoreId();
            $dto[self::STORE_NAME]          = $order->getStoreName();
            $dto[self::STOREVIEW_NAME]      = Mage::app()->getStore()->getName();
            $dto[self::CUSTOMER_ID]         = (int)$order->getCustomerId();
            $dto[self::CUSTOMER_EMAIL]      = (string)$order->getCustomerEmail();
            $dto[self::CUSTOMER_FIRSTNAME]  = (string)$order->getCustomerFirstname();
            $dto[self::CUSTOMER_LASTNAME]   = (string)$order->getCustomerLastname();
            $dto[self::CUSTOMER_GROUP]      = (int)$order->getCustomerGroupId();
            $dto[self::BASE_SUBTOTAL]       = (float)$order->getBaseSubtotal();
            $dto[self::SUBTOTAL]            = (float)$order->getSubtotal();
            $dto[self::BASE_GRAND_TOTAL]    = (float)$order->getBaseGrandTotal();
            $dto[self::GRAND_TOTAL]         = (float)$order->getGrandTotal();
            $dto[self::BASE_TOTAL_PAID]     = (float)$order->getBaseTotalPaid();
            $dto[self::TOTAL_PAID]          = (float)$order->getTotalPaid();
            $dto[self::TAX_AMOUNT]          = (float)$order->getTaxAmount();
            $dto[self::DISCOUNT_AMOUNT]     = (float)$order->getDiscountAmount();
            $dto[self::COUPON_CODE]         = (string)$order->getCouponCode();
            $dto[self::SHIPPING_DESC]       = (string)$order->getShippingDescription();
            $dto[self::SHIPPING_AMT]        = (float)$order->getShippingInclTax();
            $dto[self::SHIPPING_AMT_REF]    = (float)$order->getShippingRefunded() + (float)$order->getShippingTaxRefunded();
            $dto[self::CURRENCY_RATE]       = (float)$order->getBaseToOrderRate();
            $dto[self::BASE_CURR_CODE]      = $order->getBaseCurrencyCode();
            $dto[self::ORDER_CURR_CODE]     = $order->getOrderCurrencyCode();
            $dto[self::PRODUCTS]            = array_values($this->getOrderItems($order));
            $dto[self::INVOICES]            = $this->getOrderInvoices($order);
            $dto[self::CREDITNOTES]         = $this->getOrderCreditNotes($order);
            $dto[self::SHIPPING_ADDRESS]    = array();
            $dto[self::BILLING_ADDRESS]     = array();
            $dto[self::PAYMENT]             = $this->getOrderPayments($order, $posOrder);
            $dto[self::POS_ORDER]           = $this->getJsonPayload($posOrder);

            if ($order->getShippingAddress()) {
                $dto[self::SHIPPING_NAME]    = (string)$order->getShippingAddress()->getName();
                $dto[self::SHIPPING_ADDRESS] = $this->getAddressData($order->getShippingAddress(), 'shipping');
            }

            if ($order->getBillingAddress()) {
                $dto[self::BILLING_NAME]     = (string)$order->getBillingAddress()->getName();
                $dto[self::SHIPPING_ADDRESS] = $this->getAddressData($order->getBillingAddress(), 'billing');
            }
        }

        return $dto;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @param $type
     * @return array
     */
    public function getAddressData(Mage_Sales_Model_Order_Address $address, $type)
    {
        $addressDto = array(
            self::ADDRESS_ID          => $address->getId(),
            self::ADDRESS_FIRSTNAME   => $address->getFirstname(),
            self::ADDRESS_LASTNAME    => $address->getLastname(),
            self::ADDRESS_COUNTRY_ID  => $address->getCountry(),
            self::ADDRESS_CITY        => $address->getCity(),
            self::ADDRESS_STREET      => $address->getStreet(1),
            self::ADDRESS_STREET1     => $address->getStreet(2),
            self::ADDRESS_REGION      => $address->getRegion(),
            self::ADDRESS_REGION_ID   => $address->getRegionId(),
            self::ADDRESS_POSTCODE    => $address->getPostcode(),
            self::ADDRESS_TELEPHONE   => $address->getTelephone(),
            self::ADDRESS_FAX         => $address->getFax(),
            self::ADDRESS_COMPANY     => $address->getCompany(),
            self::ADDRESS_IS_SHIPPING => (int)($type == 'shipping'),
            self::ADDRESS_IS_BILLING  => (int)($type == 'billing'),
        );

        return $addressDto;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getOrderItems(Mage_Sales_Model_Order $order)
    {
        $orderItems = array();

        $childrenAux = array();

        /** @var Mage_Sales_Model_Order_Item $item */
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

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

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
                'options'        => $this->getOrderOptionsForItem($item),
                'image_url'      => $this->getOrderItemImage($product),
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
                        'options'        => $this->getOrderOptionsForItem($child),
                        'image_url'      => ""
                    );
                }

            }

            $giftTypes = $this->_giftCardsHelper->getSupportedTypes();
            if (array_key_exists($item->getProductType(), $giftTypes)) {
                $orderItems[$item->getId()]['gift_card_options'] = $this->_giftCardsApi->getOrderItemData($item);
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
                        if (abs($value) > abs($orderItems[$itemId][$key])) {
                            $orderItems[$itemId][$key] = $value;
                        }
                    }
                }
            }
        }

        return $orderItems;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getOrderItemImage(Mage_Catalog_Model_Product $product)
    {
        $url = '';

        // Mage_Catalog_Helper_Image throws Exception with message 'Image file was not found' if placeholder is not set for admin
        try {
            $url = $product->getImageUrl();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $url;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function getOrderOptionsForItem(Mage_Sales_Model_Order_Item $item)
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

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Ebizmarts_BakerlooRestful_Model_Order $posOrder
     * @return array
     */
    public function getOrderPayments(Mage_Sales_Model_Order $order, Ebizmarts_BakerlooRestful_Model_Order $posOrder)
    {
        $result = array();

        if ($posOrder->getId()) {
            $json = json_decode($posOrder->getJsonPayload(), true);
            $payment = $order->getPayment();

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
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getOrderInvoices(Mage_Sales_Model_Order $order)
    {
        /** @var Ebizmarts_BakerlooRestful_Model_Api_Invoices $invoicesApi */
        $invoicesApi = Mage::getModel('bakerloo_restful/api_invoices');
        $invoicesApi->parameters = array(
            'not_by_id'=>'not_by_id',
            'filters' => array('order_id,eq,' . $order->getId())
        );

        $invoices = $invoicesApi->get();

        if (is_array($invoices) and array_key_exists('page_data', $invoices)) {
            return $invoices['page_data'];
        } else {
            return array();
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getOrderCreditNotes(Mage_Sales_Model_Order $order)
    {
        /** @var Ebizmarts_BakerlooRestful_Model_Api_Creditnotes $creditnotesApi */
        $creditnotesApi = Mage::getModel('bakerloo_restful/api_creditnotes');
        $creditnotesApi->parameters = array(
            'not_by_id'=>'not_by_id',
            'filters' => array('order_id,eq,' . $order->getId())
        );

        $creditnotes = $creditnotesApi->get();

        if (is_array($creditnotes) and array_key_exists('page_data', $creditnotes)) {
            return $creditnotes['page_data'];
        } else {
            return array();
        }
    }

    /**
     * @param Ebizmarts_BakerlooRestful_Model_Order $order
     * @return array
     */
    public function getJsonPayload(Ebizmarts_BakerlooRestful_Model_Order $order)
    {
        $payload = json_decode($order->getJsonPayload(), true);

        if ($payload) {
            $payload['payment']['customer_signature'] = null;
            $payload['payment']['customer_signature_type'] = null;
            $payload['payment']['customer_signature_file'] = null;

            $addedPayments = isset($payload['payment']['addedPayments']) ? $payload['payment']['addedPayments'] : array();
            foreach ($addedPayments as $_index => $_addedPayment) {
                $payload['payment']['addedPayments'][$_index]['customer_signature'] = null;
                $payload['payment']['addedPayments'][$_index]['customer_signature_type'] = null;
                $payload['payment']['addedPayments'][$_index]['customer_signature_file'] = null;
            }

            $payload['currency_rate'] = (float)$order->getBaseToOrderRate();
        } else {
            $payload = array();
        }

        return $payload;
    }
}