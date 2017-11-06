<?php

class Ebizmarts_BakerlooPrices_Model_Observer
{
    private $_bannedPaymentMethods = array(
        'bakerloo_storecredit', 'bakerloo_magestorecredit'
    );

    public function isPosRequest()
    {
        return Mage::helper('bakerloo_restful')->isPosRequest(Mage::app()->getRequest());
    }

    public function getRequestJson()
    {
        $request = Mage::app()->getRequest();
        if ($request->getControllerName() == Ebizmarts_BakerlooRestful_Helper_Data::BAKERLOO_ORDERS_CONTROLLER) {
            if ($request->getActionName() == Ebizmarts_BakerlooRestful_Helper_Data::BAKERLOO_ORDERS_ACTION) {
                return $this->getRequestJsonFromForm();
            } else {
                return $this->getJsonFromOrder();
            }
        } else {
            return $this->getRequestJsonFromPostBody();
        }
    }

    private function getRequestJsonFromPostBody()
    {
        return Mage::helper('bakerloo_restful/http')->getJsonPayload(Mage::app()->getRequest(), true);
    }

    private function getRequestJsonFromForm()
    {
        $postData = Mage::app()->getRequest()->getPost('order', array());

        if (isset($postData['json_payload'])) {
            $postData = $postData['json_payload'];
        }

        if (is_string($postData) and !empty($postData)) {
            $postData = json_decode($postData, true);
        }

        if (!is_array($postData)) {
            $postData = array();
        }

        return $postData;
    }

    private function getJsonFromOrder() {
        $order = Mage::registry(Ebizmarts_BakerlooRestful_Model_OrderManagement::ORDER_REGISTRY_KEY);
        if (!$order or !($order instanceof Ebizmarts_BakerlooRestful_Model_Order)) {
            return null;
        }

        $postData = $order->getJsonPayload();
        if (is_string($postData) and !empty($postData)) {
            $postData = json_decode($postData, true);
        }

        return $postData;
    }

    public function shouldMatchPrices(Mage_Sales_Model_Quote $quote)
    {
        $shouldMatch = false;

        if ($this->isPosRequest() and $this->priceMatchEnabled()) {
            $payment = $quote->getPayment();

            if (!is_null($payment) and !in_array($payment->getMethod(), $this->_bannedPaymentMethods)) {
                $shouldMatch = true;
            }
        }

        return $shouldMatch;
    }

    public function priceMatchEnabled()
    {
        return (bool)(int)Mage::getStoreConfig('bakerloorestful/checkout/price_override', Mage::app()->getStore());
    }

    public function priceIncludesTax()
    {
        return (int)$this->getConfigValue('tax/calculation/price_includes_tax');
    }

    public function applyTaxAfterDiscount()
    {
        return (int)$this->getConfigValue('tax/calculation/apply_after_discount');
    }

    public function applyDiscountOnPricesIncludingTax()
    {
        return (int)$this->getConfigValue('tax/calculation/discount_tax');
    }

    /**
     * @return Mage_Directory_Model_Currency
     */
    protected function getBaseCurrency()
    {
        return Mage::app()->getStore()->getBaseCurrency();
    }

    /**
     * @return Mage_Directory_Model_Currency
     */
    protected function getCurrentCurrency()
    {
        return Mage::app()->getStore()->getCurrentCurrency();
    }

    protected function getConfigValue($path)
    {
        return Mage::getStoreConfig($path);
    }

    public function priceMatch(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        $orderItem = $observer->getEvent()->getOrderItem();

        /** @var Mage_Sales_Model_Quote_Item $item */
        $item      = $observer->getEvent()->getItem();

        if ($this->shouldMatchPrices($item->getQuote())) {

            if ($item->getParentItem()) {
                return $this;
            }

            $baseCurrency    = $this->getBaseCurrency();
            $currentCurrency = $this->getCurrentCurrency();
            $priceIncludesTax      = $this->priceIncludesTax();
            $applyTaxAfterDiscount = $this->applyTaxAfterDiscount();
            $applyDiscountOnPricesInclTax = $this->applyDiscountOnPricesIncludingTax();

            $product = $item->getPosProductLine();

            if (is_null($product)) {
                return $this;
            }

            $product = unserialize($product);

            $hiddenTax = $this->getHiddenTaxAmount($product);
            $lineTotal = round((float)$product['order_line']['subtotal'], 2);
            $price     = round((float)$product['order_line']['unit_price'], 2);
            $discount  = round((float)$product['order_line']['total_discount'], 2);
            $qty       = $product['qty'];

            //get tax rate from vat_breakdown, as POS tax_rate is unreliable
            list($taxPercent, $taxesForItem) = $this->getTaxesForItem($product['order_line']);
            $taxAmount = round((float)$product['order_line']['tax_amount'], 2);

            //update item tax rates in quote
            $this->setItemTaxesToQuote($item, $taxesForItem);
            $item->setPosAppliedTaxes(serialize($taxesForItem));
            $orderItem->setPosAppliedTaxes(serialize($taxesForItem));

            $priceWTax = $priceIncludesTax ? $price : $price + ($taxAmount / $qty);

            if ($item->getHiddenTaxAmount()) {
                if ($applyTaxAfterDiscount and !$applyDiscountOnPricesInclTax) {
                    $taxAmount += ($item->getHiddenTaxAmount() - $hiddenTax);
                }
            }

            $orderItem->setBasePrice($price);
            $orderItem->setBasePriceInclTax($priceWTax);
            $orderItem->setBaseDiscountAmount($discount);
            $orderItem->setBaseTaxAmount($taxAmount);
            $orderItem->setBaseHiddenTaxAmount($hiddenTax);
            $orderItem->setBaseRowTotal($lineTotal);
            $orderItem->setBaseRowTotalInclTax($lineTotal + $taxAmount);

            $item->setBaseCalculationPrice($price);
            $item->setBaseOriginalPrice($price);
            $item->setBasePriceInclTax($priceWTax);
            $item->setBaseDiscountAmount($discount);
            $item->setBaseTaxAmount($taxAmount);
            $item->setBaseHiddenTaxAmount($hiddenTax);
            $item->setBaseRowTotal($lineTotal);
            $item->setBaseRowTotalInclTax($lineTotal + $taxAmount);

            if ($baseCurrency->getCode() != $currentCurrency->getCode()) {
                /** @var Ebizmarts_BakerlooPayment_Helper_Data $h */
                $h = Mage::helper('bakerloo_payment');

                $taxAmount  = $h->convertFromBaseCurrency($taxAmount, $baseCurrency, $currentCurrency);
                $lineTotal  = $h->convertFromBaseCurrency($lineTotal, $baseCurrency, $currentCurrency);
                $price      = $h->convertFromBaseCurrency($price, $baseCurrency, $currentCurrency);
                $priceWTax  = $h->convertFromBaseCurrency($priceWTax, $baseCurrency, $currentCurrency);
                $discount   = $h->convertFromBaseCurrency($discount, $baseCurrency, $currentCurrency);
                $hiddenTax  = $h->convertFromBaseCurrency($hiddenTax, $baseCurrency, $currentCurrency);
            }

            $orderItem->setPrice($price);
            $orderItem->setDiscountAmount($discount);
            $orderItem->setTaxPercent($taxPercent);
            $orderItem->setTaxAmount($taxAmount);
            $orderItem->setHiddenTaxAmount($hiddenTax);
            $orderItem->setPriceInclTax($priceWTax);
            $orderItem->setRowTotal($lineTotal);
            $orderItem->setRowTotalInclTax($lineTotal + $taxAmount);

            $item->setCalculationPrice($price);
            $item->setOriginalPrice($price);
            $item->setDiscountAmount($discount);
            $item->setTaxPercent($taxPercent);
            $item->setTaxAmount($taxAmount);
            $item->setHiddenTaxAmount($hiddenTax);
            $item->setTaxRates($taxesForItem);
            $item->setPriceInclTax($priceWTax);
            $item->setRowTotal($lineTotal);
            $item->setRowTotalInclTax($lineTotal + $taxAmount);
        }

        return $this;
    }

    private function getHiddenTaxAmount($product) {
        $lineTotal = round((float)$product['order_line']['subtotal'], 2);
        $discount  = round((float)$product['order_line']['total_discount'], 2);
        $hiddenTax = max((float)$product['order_line']['taxOfDiscount'], (float)$product['order_line']['taxOfCustomDiscount']);
        $calculatedHiddenTax = round(($product['order_line']['grand_total'] - ($lineTotal + $product['order_line']['tax_amount'] - $discount)), 2);

        if ($product['is_custom_price']) {
            $hiddenTax = $calculatedHiddenTax;
        }
        elseif (isset($product['order_line']['calculatedOnline']) and $product['order_line']['calculatedOnline'] === true) {
            $hiddenTax = $calculatedHiddenTax;
        }

        return $hiddenTax;
    }

    public function priceMatchAddress(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order   = $observer->getEvent()->getOrder();

        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $observer->getEvent()->getAddress();

        if ($this->shouldMatchPrices($address->getQuote())) {

            $json  = $this->getRequestJson();

            if (!is_array($json)) {
                return $this;
            }

            list($taxPercent, $appliedTaxes) = $this->getTaxesForQuoteAddress($json, $address);
            $address->setAppliedTaxes($appliedTaxes);

            $order->setAppliedTaxes($appliedTaxes);
            $order->setConvertingFromQuote(true);

            $subtotal = round((float)$json['subtotal_amount'], 2);
            $tax      = round((float)$json['tax_amount'], 2);
            $total    = round((float)$json['total_amount'], 2);
            $discount = round((float)$json['discount'], 2);
            $shipping = round((float)$json['shipping_amount'], 2);
            $hiddenTax = 0;
            $shippingTax = 0;

            if (isset($json['shipping_tax_amount'])) {
                $shippingTax = round((float)$json['shipping_tax_amount'], 2);
            }

            if ($discount != 0) {
                if ($this->priceIncludesTax() and empty($json['returns'])) {
                    $hiddenTax = $total - ($subtotal + $tax - $discount);
                    //$hiddenTax = round($discount - (($discount * 100) / (100 + $taxPercent)), 2);
                }

                $discount = -$discount;
            }

            $order->setTaxAmount($tax);
            $order->setHiddenTaxAmount($hiddenTax);
            $order->setDiscountAmount($discount);
            $order->setSubtotalInclTax($subtotal + $tax);
            $order->setSubtotal($subtotal);
            $order->setGrandTotal($total);
            $order->setShippingAmount($shipping);
            $order->setShippingTaxAmount($shippingTax);

            $baseCurrency = $this->getBaseCurrency();
            $currentCurrency = $this->getCurrentCurrency();

            if ($baseCurrency->getCode() != $currentCurrency->getCode()) {
                /** @var Ebizmarts_BakerlooPayment_Helper_Data $h */
                $h = Mage::helper('bakerloo_payment');

                $discount    = $h->convertToBaseCurrency($discount, $baseCurrency, $currentCurrency);
                $subtotal    = $h->convertToBaseCurrency($subtotal, $baseCurrency, $currentCurrency);
                $total       = $h->convertToBaseCurrency($total, $baseCurrency, $currentCurrency);
                $tax         = $h->convertToBaseCurrency($tax, $baseCurrency, $currentCurrency);
                $hiddenTax   = $h->convertToBaseCurrency($hiddenTax, $baseCurrency, $currentCurrency);
                $shipping    = $h->convertToBaseCurrency($shipping, $baseCurrency, $currentCurrency);
                $shippingTax = $h->convertToBaseCurrency($shippingTax, $baseCurrency, $currentCurrency);
            }

            $order->setBaseTaxAmount($tax);
            $order->setBaseHiddenTaxAmount($hiddenTax);
            $order->setBaseDiscountAmount($discount);
            $order->setBaseSubtotalInclTax($subtotal + $tax);
            $order->setBaseSubtotal($subtotal);
            $order->setBaseGrandTotal($total);
            $order->setBaseShippingAmount($shipping);
            $order->setBaseShippingTaxAmount($shippingTax);
        }

        return $this;
    }

    public function getTaxesForItem($orderLine)
    {
        $taxPercent   = 0;
        $taxAmount    = 0;
        $taxBreakdown = array();
        $qty = $orderLine['qty'] * 1;

        foreach ($orderLine['applied_vats'] as $vat) {

            $taxInfo = !isset($vat['tax_break']) ?
                !isset($vat['taxBreak']) ? array() : $vat['taxBreak']
                : $vat['tax_break'];

            if (count($taxInfo) == 1) {

                $taxInfo = $taxInfo[0];

                if ($taxPercent == 0) {
                    $taxPercent = 1 + ((double)$taxInfo['rate'] / 100);
                } else {
                    $taxPercent = $taxPercent * (1 + ((double)$taxInfo['rate']) / 100);
                }

                $taxAmount += $taxInfo['tax_amount'];
                $taxCode = $taxInfo['code'];

                $taxRate = $this->getCalculationRate()->loadByCode($taxCode);
                $taxBreakdown[] = array(
                    'rates' => array(
                        array(
                            'code'     => $taxCode,
                            'title'    => $taxCode,
                            'percent'  => $taxInfo['rate'],
                            'position' => $taxInfo['priority'],
                            'priority' => $taxInfo['priority'],
                            'rule_id'  => (int)$taxRate->getId()
                        )
                    ),
                    'percent' => $taxInfo['rate'],
                    'id'      => $taxCode
                );
            }

            //@TODO: support multiple rates for tax rule
        }

        if ($taxPercent > 0) {
            $taxPercent = 100 * ($taxPercent - 1);
        }

        return array(round($taxPercent, 4), $taxBreakdown);
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @param array $taxesForItem
     *
     * @return Mage_Sales_Model_Quote
     */
    public function setItemTaxesToQuote($item, $taxesForItem)
    {
        $quote     = $item->getQuote();
        $itemTaxes = $quote->getTaxesForItems();

        if (is_null($itemTaxes)) {
            $itemTaxes = array();
        }

        $itemTaxes[$item->getId()] = $taxesForItem;
        $quote->setTaxesForItems($itemTaxes);

        return $quote;
    }

    /**
     * Calculate accumulated taxes by tax rule code.
     *
     * @param array $json
     * @param Mage_Sales_Model_Quote_Address $quote
     * @return array
     */
    public function getTaxesForQuoteAddress($json, Mage_Sales_Model_Quote_Address $address)
    {
        $products = $json['products'];

        $appliedTaxes = array();
        $taxPercent = 0;

        // add item level tax rates
        foreach ($products as $product) {
            $appliedVats = $product['order_line']['applied_vats'] ? $product['order_line']['applied_vats'] : array();

            foreach ($appliedVats as $vat) {

                $taxBreaks = !isset($vat['tax_break']) ?
                    !isset($vat['taxBreak']) ? array() : $vat['taxBreak']
                    : $vat['tax_break'];

                if (count($taxBreaks) == 1) {
                    $taxCode = $taxBreaks[0]['code'];
                    $taxRate = $this->getCalculationRate()->loadByCode($taxCode);

                    if (isset($appliedTaxes[$taxCode])) {
                        $rateInfo = $appliedTaxes[$taxCode];
                        $rateInfo['amount']      += $taxBreaks[0]['tax_amount'];
                        $rateInfo['base_amount'] += $taxBreaks[0]['tax_amount']; //@TODO: convert to base amount
                    } else {

                        if ($taxPercent == 0) {
                            $taxPercent = 1 + ((double)$taxBreaks[0]['rate'] / 100);
                        } else {
                            $taxPercent = $taxPercent * (1 + ((double)$taxBreaks[0]['rate']) / 100);
                        }

                        $rateInfo = array(
                            'rates'     => array(
                                array(
                                    'code'      => $taxCode,
                                    'title'     => $taxCode,
                                    'percent'   => $taxBreaks[0]['rate'],
                                    'position'  => $taxBreaks[0]['priority'],
                                    'priority'  => $taxBreaks[0]['priority'],
                                    'rule_id'   => (int)$taxRate->getId(),
                                )
                            ),
                            'percent'   => $taxBreaks[0]['rate'],
                            'id'        => (int)$taxRate->getId(),
                            'process'   => 0,
                            'amount'    => $taxBreaks[0]['tax_amount'],
                            'base_amount' => $taxBreaks[0]['tax_amount'],
                        );
                    }

                    $appliedTaxes[$taxCode] = $rateInfo;
                }

                //@TODO: support multiple rates for tax rule
            }
        }

        if ($taxPercent > 0) {
            $taxPercent = 100 * ($taxPercent - 1);
        }

        // add shipping tax rate if applicable
        if (isset($json['shipping_tax_amount']) and $json['shipping_tax_amount'] > 0) {
            $appliedTaxes += $this->getAppliedShippingTaxes($address, $appliedTaxes);
        }

        return array($taxPercent, $appliedTaxes);
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @param array $appliedTaxes
     * @return array
     */
    public function getAppliedShippingTaxes(Mage_Sales_Model_Quote_Address $address, $appliedTaxes)
    {
        $addressTaxes = $address->getAppliedTaxes();

        foreach ($appliedTaxes as $_code => $_rate) {
            if (isset($appliedTaxes[$_code])) {
                unset($addressTaxes[$_code]);
            }
        }

        return $addressTaxes;
    }

    /**
     * @return Mage_Tax_Model_Calculation_Rate
     */
    public function getCalculationRate()
    {
        return Mage::getModel('tax/calculation_rate');
    }
}
