<?php

class Ebizmarts_BakerlooRestful_Helper_Sales extends Mage_Core_Helper_Abstract
{
    const CART_ITEM_RETURN_BEFORE = 'pos_cart_item_return_before';
    const CART_DATA_RETURN_BEFORE = 'pos_cart_data_return_before';

    /** @var Mage_Sales_Model_Quote  */
    private $_quote         = null;
    private $_loyalty       = null;
    private $_defaultAttr   = array('name', 'weight','price','price_type','special_price','special_from_date','special_to_date','tax_class_id');

    /**
     * Build quote for order.
     *
     * @param $storeId
     * @param $data
     * @param bool $onlyQuote
     * @return mixed
     */
    public function buildQuote($storeId, $data, $onlyQuote = false)
    {
        Varien_Profiler::start('POS::' . __METHOD__);

        $store = Mage::app()->getStore();

        $this->getStore()->setCurrentCurrencyCode($data['currency_code']);

        Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($storeId);

        $quote = $this->getQuoteSales()
            ->setStoreId($storeId)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->save();

        $this->setQuote($quote);

        //Get customer
        $customerId = (int)$data['customer']['customer_id'];

        $customerExistsByEmail = $this->customerExists($data['customer']['email'], $store->getWebsiteId());
        if (false !== $customerExistsByEmail) {
            $customerId = $customerExistsByEmail->getId();
        }
        $customer = $this->getModelCustomer()->load($customerId);
        //Get customer

        /* Save data to session for extensions compatibility */
        if ($customer->getId()) {
            $this->setCustomerSessionVariables($data, $customer);
        }

        //Adding products to Quote
        if (!is_array($data['products']) or empty($data['products'])) {
            $this->throwBuildQuoteException(Mage::helper('bakerloo_restful')->__('ALERT: No products provided on order.'));
        }

        $this->_addProductsToQuote($data['products']);
        //Adding products to Quote

        if (!$this->getQuote()->isVirtual()) {
            $this->setShippingAddressToQuote($data);
        }

        if ($onlyQuote) {
            if ($customer->getId()) {
                $this->getQuote()->setCustomer($customer);
            }

            $this->collectQuoteTotals();

            /* prevent totals from collecting twice if using Magestore Extensions */
            if (Mage::helper('bakerloo_gifting')->getIntegrationFromConfig() == 'Magestore_Giftvoucher' and !empty($giftCards)
                || $data['payment']['method'] == 'bakerloo_magestorecredit') {
                $quote->setTotalsCollectedFlag(true);
            }

            return $this->getQuote();
        }

        $canUseLoyalty = Mage::helper('bakerloo_loyalty')->canUse();
        if ($canUseLoyalty && $customerId) {
            $this->_loyalty = Mage::getModel(
                'bakerloo_restful/integrationDispatcher',
                array(
                    'integration_type' => 'loyalty',
                    'customer_id'      => $customerId,
                    'website_id'       => Mage::app()->getStore()->getWebsiteId()
                )
            );

            /*Fix for TBT_Rewards, points not saved to customer otherwise.*/
            if (Mage::helper('bakerloo_loyalty')->isSweetTooth($this->_loyalty)) {
                $this->getQuote()->save();
            }
            /*Fix for TBT_Rewards, points not saved to customer otherwise.*/
        }

        if (Mage_Checkout_Model_Type_Onepage::METHOD_GUEST == $data['customer']['mode'] && (false === $customerExistsByEmail)) {
            $ownerEmail = (string)Mage::app()->getStore()->getConfig('trans_email/ident_general/email');

            if ((((string)$data['customer']['email']) != $ownerEmail) and (1 === (int)Mage::helper('bakerloo_restful')->config('checkout/create_customer'))) {
                //Involve new customer if the one provided does not exist
                $this->_involveNewCustomer($data);
            } else {
                $this->getQuote()->setCheckoutMethod($data['customer']['mode']);

                $this->getQuote()
                    ->setCustomerEmail($data['customer']['email'])
                    ->setCustomerId(null)
                    ->setCustomerIsGuest(true)
                    ->setCustomerFirstname($data['customer']['firstname'])
                    ->setCustomerLastname($data['customer']['lastname']);

                $this->getQuote()->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            }
        } else {
            $this->getQuote()
                ->setCustomer($customer)
                ->setPasswordHash($customer->encryptPassword($customer->getPassword()));
        }

        if ($data['shipping'] == 'bakerloo_ship_to_store_bakerloo_ship_to_store') {
            Mage::getSingleton('checkout/session')->setPosShipToStoreId($data['location_id']);
        }

        if ($data['total_amount'] == 0 && !$this->getQuote()->isVirtual()) {
            $this->getQuote()->getShippingAddress()
                ->setShippingMethod($data['shipping']);
        } else {
            if ($this->getQuote()->isVirtual()) {
                $this->getQuote()->getBillingAddress()
                    ->setPaymentMethod($data['payment']['method']);
            } else {
                $this->getQuote()->getShippingAddress()
                    ->setPaymentMethod($data['payment']['method'])
                    ->setShippingMethod($data['shipping']);
            }
        }

        $billingAddress  = $this->_getAddress($data['customer']['billing_address'], $data['customer']['email']);
        $this->getQuote()
            ->getBillingAddress()
            ->addData($billingAddress);

        //Apply coupon if present
        $checkCouponOK = $this->checkAndSetCoupon($data);

        //Apply gift cards if present
        $giftCards = isset($data['gift_card']) ? $data['gift_card'] : array();
        $this->setGiftCardsToQuote($storeId, $giftCards);

        //Apply loyalty rules if present
        $loyalty = isset($data['loyalty']) ? $data['loyalty'] : array();
        $this->setRewardsToQuote($loyalty);

        $this->collectQuoteTotals();
        
        /* prevent totals from collecting twice if using Magestore Extensions */
        if ((Mage::helper('bakerloo_gifting')->getIntegrationFromConfig() == 'Magestore_Giftvoucher' and !empty($giftCards))
            || $data['payment']['method'] == 'bakerloo_magestorecredit' || !empty($data['loyalty'])
        ) {
            $this->getQuote()->setTotalsCollectedFlag(true);
        }
        
        if ($data['total_amount'] != 0) {
            $this->setPaymentMethodToQuote($data);
        } else {
            /* workaround in case full payment is made with gift card */
            $this->setFreePaymentMethodToQuote(!empty($giftCards));
        }

        $this->getQuote()->save();

        //If coupon was provided and does not validate, throw error.
        if ($checkCouponOK and !$this->getQuote()->getCouponCode()) {
            $this->throwBuildQuoteException(Mage::helper('bakerloo_restful')->__('Discount coupon could not be applied, please try again.'));
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $this->getQuote();
    }


    public function _addProductsToQuote($products)
    {
        Varien_Profiler::start('POS::' . __METHOD__);

        $useSimplePrice = (int)Mage::helper('bakerloo_restful')->config('general/simple_configurable_prices', Mage::app()->getStore()->getId());
        $fastProducts   = (int)Mage::helper('bakerloo_restful')->config('checkout/fast_product_load', Mage::app()->getStore()->getId());

        if (((int)Mage::helper('bakerloo_restful')->config('catalog/allow_backorders', $this->getQuote()->getStoreId()))) {
            if (!Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES)) {
                Mage::register(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES, true);
            }
        }

        $productsById = $this->getProductsById($products);
        $productItems = $this->getProductItems(array_keys($productsById), $fastProducts);

        foreach ($productsById as $_id => $_products) {

            foreach ($_products as $_product) {
                $product = clone $productItems[$_id];

                if (!$product->getId()) {
                    $this->throwBuildQuoteException('Product ID: ' . $_id . " does not exist.");
                }

                if ($fastProducts) {
                    if ($product->getHasOptions()) {
                        foreach ($product->getProductOptionsCollection() as $option) {
                            $option->setProduct($product);
                            $product->addOption($option);
                        }
                    }
                } else {
                    $product->load($_id);
                }
                $buyInfo = $this->getBuyInfo($_product, $product);
                try {
                    //Skip stock checking
                    if (Mage::helper('bakerloo_restful')->dontCheckStock()) {
                        $product->getStockItem()->setData('manage_stock', 0);
                        $product->getStockItem()->setData('use_config_manage_stock', 0);
                    }

                    //if simple_configurable_product enabled, use child's price
                    if (isset($_product['child_id']) and !is_null($_product['child_id']) and $_product['child_id'] !== -1 and $useSimplePrice === 1) {
                        $product->setPrice($_product['price']);
                        $product->setSpecialPrice('');
                    }

                    if (isset($_product['no_tax']) and $_product['no_tax']) {
                        $_taxHelper = Mage::helper('tax');
                        $_finalPriceExclTax = $_taxHelper->getPrice($product, $product->getFinalPrice(), false);
                        $product->setTaxClassId('0');
                        $product->setPrice($_finalPriceExclTax);
                        $product->setSpecialPrice('');
                    }

                    $quoteItem = $this->getQuote()->addProduct($product, new Varien_Object($buyInfo));

                    if (is_string($quoteItem) or is_null($quoteItem)) {
                        $this->throwBuildQuoteException($quoteItem . ' Product ID: ' . $_product['product_id']);
                    }

                    //Rewards integrations
                    $this->applyRewardsToQuoteItem($_product, $product, $quoteItem);


                } catch (Exception $qex) {
                    $this->throwBuildQuoteException("An error occurred, Product SKU: {$product->getSku()}. Error Message: {$qex->getMessage()}");
                }

                if (isset($_product['guid'])) {
                    $quoteItem->setPosItemGuid($_product['guid']);
                }

                //@TODO: Discount amount per line, see discount.
                if (array_key_exists('is_custom_price', $_product)) {
                    if ((int)$_product['is_custom_price'] === 1) {
                        $this->_applyCustomPrice($quoteItem, $_product['price']);
                    }
                } elseif (isset($_product['price'])) {
                    $this->_applyCustomPrice($quoteItem, $_product['price']);
                }

                //Discount reasons
                $this->setPosDiscountReasonToQuoteItem($_product, $quoteItem);

                //Save item json
                $posProductLine = serialize($_product);
                if ($quoteItem->getParentItem()) {
                   $quoteItem->getParentItem()->setPosProductLine($posProductLine);
                } else {
                    $quoteItem->setPosProductLine($posProductLine);
                }

                unset($product);
            }
        }//foreach

        foreach ($this->getQuote()->getAllItems() as $item) {
            $item->save();
        }

        $this->reloadQuote();
        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    protected function _loadStockForProducts($products)
    {

        $productIds = array_keys($products);
        
        // START Allure Fixes - Add Stock Filter
        $stock = Mage::getModel('cataloginventory/stock');

        $stockItemCol = Mage::getResourceModel('cataloginventory/stock_item_collection')
            ->addStockFilter($stock)
            ->addFieldToFilter('product_id', array('in' => $productIds));
        // END Allure Fixes

        foreach ($stockItemCol as $_sItem) {
            $product = $products[$_sItem->getProductId()];
            $product->setStockItem($_sItem);
        }

        return $products;
    }

    public function clearSessions()
    {
        Mage::getSingleton('checkout/session')->unsetAll();
        Mage::getSingleton('customer/session')->unsetAll();

        if (Mage::helper('bakerloo_loyalty')->canUse() and Mage::helper('bakerloo_loyalty')->isSweetTooth()) {
            Mage::getSingleton('rewards/session')->unsetAll();
        }
    }

    public function getBuyInfo($_product, $product = null)
    {
        $buyInfo = array();

        $buyInfo['qty'] = isset($_product['qty']) ? ($_product['qty'] * 1) : 1;
        $productType = isset($_product['type']) ? (string)$_product['type'] : "";

        //Configurable attributes
        if (isset($_product['super_attribute'])) {
            $superAttribute = $_product['super_attribute'];
            if (is_array($superAttribute) && !empty($superAttribute)) {
                $superRequest = array();

                foreach ($superAttribute as $_at) {
                    $attribute = $this->getResourceEavAttribute()
                        ->loadByCode(Mage_Catalog_Model_Product::ENTITY, (string)$_at['attribute_code']);

                    $superRequest[$attribute->getId()] = (string)$_at['value_index'];
                }

                $buyInfo['super_attribute'] = $superRequest;
            }
        }

        //Grouped product
        if (isset($_product['super_group'])) {
            $superGroup = array();

            foreach ($_product['super_group'] as $_sg) {
                $_sgQty = isset($_sg['qty']) ? (int)$_sg['qty'] : 0;

                if ($_sgQty > 0) {
                    $superGroup[$_sg['product_id']] = $_sgQty;
                }
            }
            $buyInfo['super_group'] = $superGroup;
        }

        //Bundle product
        if (isset($_product['bundle_option']) and is_array($_product['bundle_option'])) {
            $buyInfo += $this->buyInfoAddBundleOptions($_product);
        }

        //@TODO: Support FILES.
        //Product custom options
        if (isset($_product['options']) and is_array($_product['options'])) {
            $options = $_product['options'];

            $optionsRequest = array();

            foreach ($options as $_opt) {
                $selected = (int)$_opt['option_type_id'];

                if ($selected) {
                    $optionsRequest[$_opt['option_id']] = $selected;
                } else {
                    if (isset($_opt['text'])) {
                        if ($_opt['type'] == 'date' or $_opt['type'] == 'date_time' or $_opt['type'] == 'time') {
                            $optionsRequest[$_opt['option_id']] = $this->getOptionForDateTime($_opt['text']);
                        } else {
                            $optionsRequest[$_opt['option_id']] = (string)$_opt['text'];
                        }
                    } else {
                        if ($_opt['type'] == 'multiple' or $_opt['type'] == 'checkbox') {
                            $optionsRequest[$_opt['option_id']] = $_opt['option_type_ids'];
                        }
                    }
                }
            }

            $buyInfo['options'] = $optionsRequest;
        }

        $giftcard = Mage::helper('bakerloo_gifting')->getGiftcard($productType);
        if (!is_null($giftcard)) {
            if (Mage::registry('haitv_product_' . $product->getId())) {
                Mage::unregister('haitv_product_' . $product->getId());
            }

            $giftcardOptions = $giftcard->getBuyInfoOptions($_product);
            $buyInfo = array_merge($buyInfo, $giftcardOptions);
        }
        
        if (isset($_product['store_credit_options']) and is_array($_product['store_credit_options'])) {
            $storeCreditOptions = array();

            $storeCreditOptions['customer_name'] = $_product['store_credit_options']['customer_name'];
            $storeCreditOptions['send_friend'] = $_product['store_credit_options']['send_friend'];
            $storeCreditOptions['recipient_name'] = $_product['store_credit_options']['recipient_name'];
            $storeCreditOptions['recipient_email'] = $_product['store_credit_options']['recipient_email'];
            $storeCreditOptions['message'] = $_product['store_credit_options']['message'];
            $storeCreditOptions['amount'] = $_product['store_credit_options']['amount'];

            $buyInfo += $storeCreditOptions;
        }

        $buyInfo = new Varien_Object($buyInfo);
        Mage::dispatchEvent(
            'pos_add_product_to_cart',
            array('info_buy_request' => $buyInfo, 'product' => $product, 'pos_product' => $_product)
        );


        return $buyInfo->getData();
    }

    //Product options date, date-time and time
    public function getOptionForDateTime($elements)
    {
        return date_parse($elements);
    }

    public function buyInfoAddBundleOptions($_product)
    {
        $buyInfo = array();

        $buyInfo['product']         = $_product['product_id'];
        $buyInfo['related_product'] = '';

        $buyInfo['bundle_option']     = array();
        $buyInfo['bundle_option_qty'] = array();

        foreach ($_product['bundle_option'] as $bundle) {
            $optionType = $bundle['type'];
            $optionId   = (int)$bundle['id'];
            $selections = $bundle['selections'];

            if (is_array($selections) and !empty($selections)) {
                foreach ($selections as $_sel) {
                    if (isset($_sel['selected'])) {
                        if (1 === ((int)$_sel['selected'])) {
                            $selectedId = (int)$_sel['id'];

                            if ($this->isBundleItemOptionMultiSelect($optionType)) {
                                $buyInfo['bundle_option'][$optionId][] = $selectedId;
                            } else {
                                if ($this->isBundleItemOptionSingleSelect($optionType)) {
                                    $buyInfo['bundle_option'][$optionId]     = $selectedId;
                                    $buyInfo['bundle_option_qty'][$optionId] = ($_sel['qty'] * 1);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $buyInfo;
    }

    public function isBundleItemOptionMultiSelect($optionType)
    {
        return ($optionType == 'multi' or $optionType == 'checkbox');
    }

    public function isBundleItemOptionSingleSelect($optionType)
    {
        return ($optionType == 'radio' or $optionType == 'select');
    }

    private function _applyCustomPrice($quoteItem, $price)
    {

        //Cannot apply custom price on dynamic bundle, Magento does not allow it.

        if ($quoteItem->getParentItem()) {
            $quoteItem->getParentItem()->setCustomPrice($price);
            $quoteItem->getParentItem()->setOriginalCustomPrice($price);
            $quoteItem->getParentItem()->setBaseRowTotal($price);
        } else {
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setBaseRowTotal($price);
        }
    }

    protected function _getCustomerAddress($addressId)
    {
        $address = Mage::getModel('customer/address')->load((int)$addressId);
        if (is_null($address->getId())) {
            return null;
        }

        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }
        return $address;
    }

    public function _getAddress($data, $email = "")
    {

        $id = (int)$data['customer_address_id'];
        $_address = $this->_getCustomerAddress($id);

        if ($_address) {
            $address = $_address->getData();
        } else {
            $street = array($data['street'], (string)$data['street1']);
            $street = implode("\n", $street);

            $address = array(
                //'customer_address_id' => $id,
                'firstname' => $data['firstname'],
                'lastname'  => $data['lastname'],
                'email'     => $email,
                'is_active' => 1,
                'street'    => $street,
                'street1'   => (string)$data['street'],
                'street2'   => (string)$data['street2'],
                'city'      => $data['city'],
                'region_id' => $data['region_id'],
                'region'    => $data['region'],
                'company'   => $data['company'],
                'postcode'  => $data['postcode'],
                'country_id' => $data['country_id'],
                'telephone'  => $data['telephone']
            );
        }

        return $address;
    }

    /**
     * Create new customer if the one provided does not exist.
     *
     * @param  string $data JSON data
     * @return void
     */
    protected function _involveNewCustomer($data)
    {

        $email = (string)$data['customer']['email'];

        $this->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);

        /* @see Mage_Checkout_Model_Type_Onepage::_validateCustomerData */
        /* @var $customerForm Mage_Customer_Model_Form */

        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customer  = $this->customerExists($email, $websiteId);

        if (false === $customer) {
            $password     = substr(uniqid(), 0, 8);
            $customer     = Mage::helper('bakerloo_restful')->createCustomer($websiteId, $data, $password);
            $passwordHash = $customer->hashPassword($password);

            $addAddress = true;

            //Billing Address
            $address  = $this->_getAddress($data['customer']['billing_address'], $data['customer']['email']);

            //Check that the address provided is not the store's, if thats the case, ignore it.
            $storeAddress = Mage::helper('bakerloo_restful')->getStoreAddress(Mage::app()->getStore()->getId());
            if (is_array($storeAddress) and !empty($storeAddress)) {
                $eqPostcode  = ($storeAddress['postal_code'] == $address['postcode']);
                $eqCountry   = ($storeAddress['country'] == $address['country_id']);
                $eqTelephone = ($storeAddress['telephone'] == $address['telephone']);
                $eqRegion    = ($storeAddress['region_id'] == $address['region_id']);

                $addAddress = !($eqPostcode and $eqCountry and $eqTelephone and $eqRegion);
            }

            if ($addAddress) {
                $newAddress = Mage::getModel('customer/address');
                $newAddress->addData($address);
                $newAddress->setId(null)
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);
                $customer->addAddress($newAddress);

                $addressErrors = $newAddress->validate();
                if (is_array($addressErrors)) {
                    $this->throwBuildQuoteException(implode("\n", $addressErrors));
//                    Mage::throwException(implode("\n", $addressErrors));
                }
            }

            //@TODO: Check this, Magento should save customer when checkout method is REGISTER
            //its not doing it so we call save() manually
            $customer->save();

            $this->getQuote()->setPasswordHash($passwordHash);
            $this->getQuote()->setCustomerGroupId($customer->getGroupId());
            $this->getQuote()->setCustomerIsGuest(false);

            // copy customer data to quote
            Mage::helper('core')->copyFieldset('customer_account', 'to_quote', $customer, $this->getQuote());
        }
    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int $websiteId
     * @return false|Mage_Customer_Model_Customer
     */
    public function customerExists($email, $websiteId = null)
    {
        $customer = $this->getModelCustomer();
        if ($websiteId != null) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    public function getQuote()
    {
        return $this->_quote;
    }

    public function setQuote($aQuote)
    {
        $this->_quote = $aQuote;
    }

    public function reloadQuote()
    {
        $this->getQuote()->save();
        $quoteId = $this->getQuote()->getId();
        if ($quoteId) {
            $this->_quote = Mage::getModel('sales/quote')->load($quoteId);
        }
    }

    /**
     * Put notification on admin panel.
     *
     * @param  array  $notification
     * @return void
     */
    public function notifyAdmin(array $notification)
    {
        if (!empty($notification)) {
            Mage::getModel('adminnotification/inbox')->parse(array($notification));
        }
    }

    public function getCartData($quote, $includeAddress = false, $checkAppliedRules = false)
    {
        $this->setQuote($quote);
        
        $cartData = array(
            'quote_currency_code'         => $quote->getQuoteCurrencyCode(),
            'grand_total'                 => (double)$quote->getGrandTotal(),
            'base_grand_total'            => (double)$quote->getBaseGrandTotal(),
            'sub_total'                   => (double)$quote->getSubtotal(),
            'base_subtotal'               => (double)$quote->getBaseSubtotal(),
            'subtotal_with_discount'      => (double)$quote->getSubtotalWithDiscount(),
            'base_subtotal_with_discount' => (double)$quote->getBaseSubtotalWithDiscount(),
            'discount'                    => (double)$quote->getSubtotal() - $quote->getSubtotalWithDiscount(),
            'base_discount'               => (double)$quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount(),
            'total_tax'                   => 0,
            'items'                       => array()
        );

        $quoteTotals = $quote->getTotals();

        if (isset($quoteTotals['tax'])) {
            $quoteTaxTotal = $quoteTotals['tax'];
            $cartData['total_tax'] = $quoteTaxTotal->getValue();
        }

        if ($includeAddress) {
            $cartData['billing_address']  = array();
            $cartData['shipping_address'] = array();

            $billing  = $quote->getBillingAddress();
            if ($billing->getId()) {
                $cartData['billing_address'] = array(
                    'customer_address_id' => $billing->getId(),
                    'firstname'           => $billing->getFirstname(),
                    'lastname'            => $billing->getLastname(),
                    'email'               => $billing->getEmail(),
                    'is_active'           => 1,
                    'street'              => '', //$street,
                    'street1'             => (string)$billing->getStreet1(),
                    'street2'             => (string)$billing->getStreet2(),
                    'city'                => $billing->getCity(),
                    'region_id'           => $billing->getRegionId(),
                    'region'              => $billing->getRegion(),
                    'company'             => $billing->getCompany(),
                    'postcode'            => $billing->getPostcode(),
                    'country_id'          => $billing->getCountryId(),
                    'telephone'           => $billing->getTelephone()
                );
            }

            $shipping = $quote->getShippingAddress();
            if ($shipping->getId()) {
                $cartData['shipping_address'] = array(
                    'customer_address_id' => $shipping->getId(),
                    'firstname'           => $shipping->getFirstname(),
                    'lastname'            => $shipping->getLastname(),
                    'email'               => $shipping->getEmail(),
                    'is_active'           => 1,
                    'street'              => '', //$street,
                    'street1'             => (string)$shipping->getStreet1(),
                    'street2'             => (string)$shipping->getStreet2(),
                    'city'                => $shipping->getCity(),
                    'region_id'           => $shipping->getRegionId(),
                    'region'              => $shipping->getRegion(),
                    'company'             => $shipping->getCompany(),
                    'postcode'            => $shipping->getPostcode(),
                    'country_id'          => $shipping->getCountryId(),
                    'telephone'           => $shipping->getTelephone()
                );
            }
        }

        $priceIncludesTax      = (bool)(int)Mage::getStoreConfig('tax/calculation/price_includes_tax');
        $applyTaxAfterDiscount = (bool)(int)Mage::getStoreConfig('tax/calculation/apply_after_discount');
        $discPricesInclTax     = (bool)(int)Mage::getStoreConfig('tax/calculation/discount_tax');

        $quoteItems = $quote->getItemsCollection(false)->getItems();
        $quoteItemTaxes = $quote->getTaxesForItems();
        $childrenAux = $this->getChildDiscounts($quoteItems);

        foreach ($quoteItems as $quoteItem) {
            $itemTaxes = $quoteItem->getTaxRates();

            if (!is_array($itemTaxes)) {
                $itemTaxes = isset($quoteItemTaxes[$quoteItem->getId()]) ? $quoteItemTaxes[$quoteItem->getId()] : array();
            }

            $cartData['items'][$quoteItem->getId()] = $this->getCartItemData($quoteItem, $itemTaxes, $childrenAux, $priceIncludesTax, $applyTaxAfterDiscount, $discPricesInclTax);
        }

        $cartData['items'] = array_values($cartData['items']);

        $cartData = new Varien_Object($cartData);

        Mage::dispatchEvent(self::CART_DATA_RETURN_BEFORE, array('quote' => $quote, 'cart_data' => $cartData));

        return $cartData->getData();
    }

    public function getChildDiscounts($quoteItems)
    {

        $childDiscounts = array();

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                $parentId = $quoteItem->getParentItemId();

                if (array_key_exists($parentId, $childDiscounts)) {
                    $childDiscounts[$parentId]['discount_amount'] += $quoteItem->getDiscountAmount();
                } else {
                    $childDiscounts[$parentId] = array('discount_amount' => $quoteItem->getDiscountAmount());
                }
            }
        }

        return $childDiscounts;
    }

    public function getCartItemData($quoteItem, $taxRates, $childDiscounts = array(), $priceIncludesTax = false, $applyTaxAfterDiscount = false, $discPricesInclTax = false)
    {

        $guid = $quoteItem->getPosItemGuid();

        if ($quoteItem->getParentItem()) {
            $guid = '';
        }

        if ($children = $quoteItem->getChildren()) {
            $lastChild = $children[count($children) - 1];
            $guid      = $lastChild->getPosItemGuid();
        }

        $currencyCode  = (string)$this->getQuote()->getQuoteCurrencyCode();
        $appliedVats    = array();
        $netAmount      = $quoteItem->getPrice();
        $qty            = $quoteItem->getQty() ? $quoteItem->getQty() : 1;
        $discountAmount = (float)$quoteItem->getDiscountAmount() / $qty;

        if (array_key_exists($quoteItem->getId(), $childDiscounts)) {
            $discountAmount = max($discountAmount, $childDiscounts[$quoteItem->getId()]['discount_amount']);
        }

        $discountAmountOriginal = $discountAmount; //need to keep original to display item total

        if ($priceIncludesTax) {
            $discountAmount = ($discountAmount * 100) / (100 + $quoteItem->getTaxPercent()); //remove tax from discount amount if included
        }
        if ($applyTaxAfterDiscount) {
            $netAmount -= $discountAmount;
        }

        foreach ($taxRates as $rate) { //assumes rates are ordered by priority

            $rate       = $rate['rates'][0];
            $taxPercent = (double)$rate['percent'];
            $taxAmount  = $netAmount * ($taxPercent / 100);

            $appliedVats[] = array(
                'description'    => (string)$rate['code'],
                'currency_code'  => $currencyCode,
                'tax_break'      => array(
                    array(
                        'code'       => (string)$rate['code'],
                        'rate'       => (double)$taxPercent,
                        'priority'   => (int)$rate['priority'],
                        'tax_amount' => round($taxAmount * $qty, 2, PHP_ROUND_HALF_UP),
                        'net_amount' => round($netAmount * $qty, 2, PHP_ROUND_HALF_UP)
                    )
                )
            );

            $netAmount += $taxAmount;
        }

        $itemTotal = $quoteItem->getRowTotalInclTax();
        $itemTotal -= ($discountAmountOriginal * $qty);

        if ($applyTaxAfterDiscount === false and $discPricesInclTax === true) {
            $itemTotal += $quoteItem->getDiscountTaxCompensation();
        } elseif ($discPricesInclTax === false) {
            $itemTotal -= $quoteItem->getDiscountTaxCompensation();
        }

        $item = array(
            'item_id'                 => (int)$quoteItem->getId(),
            'parent_item_id'          => (int)$quoteItem->getParentItemId(),
            'item_guid'               => $guid,
            'sku'                     => $quoteItem->getSku(),
            'product_id'              => (int)$quoteItem->getProductId(),
            'qty'                     => ($quoteItem->getQty() * 1),
            'price'                   => $quoteItem->getPrice(),
            'custom_price'            => (float)$quoteItem->getCustomPrice(),
            'price_incl_tax'          => $quoteItem->getPriceInclTax(),
            'base_price_incl_tax'     => (float)$quoteItem->getBasePriceInclTax(),
            'row_total'               => $quoteItem->getRowTotal(),
            'base_row_total'          => $quoteItem->getBaseRowTotal(),
            'row_total_with_discount' => $quoteItem->getRowTotalAfterRedemptions() ? (float)$quoteItem->getRowTotalAfterRedemptions() : (float)$quoteItem->getRowTotalWithDiscount(),
            'row_total_incl_tax'      => $quoteItem->getRowTotalAfterRedemptionsInclTax() ? (float)$quoteItem->getRowTotalAfterRedemptionsInclTax() : (float)$quoteItem->getRowTotalInclTax(),
            'discount_amount'         => round($discountAmountOriginal * $qty, 2, PHP_ROUND_HALF_UP),
            'tax_amount'              => (float)$quoteItem->getTaxAmount(),
            'tax_percent'             => (double)$quoteItem->getTaxPercent(),
            'tax_of_discount'         => (float)$quoteItem->getHiddenTaxAmount(),
            'grand_total'             => round($itemTotal, 2, PHP_ROUND_HALF_UP),
            'applied_vats'            => $appliedVats,
            'product'                 => Mage::getModel('bakerloo_restful/api_products')->_createDataObject((int)$quoteItem->getProductId())
        );

        return $this->addCartItemOptions($quoteItem, $item);
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return array
     */
    public function addCartItemOptions(Mage_Sales_Model_Quote_Item $quoteItem, $item)
    {
        $buyRequest = $quoteItem->getBuyRequest();
        $options = $quoteItem->getProduct()->getOptions();
        if ($buyRequest->getOptions()) {
            foreach ($buyRequest->getOptions() as $optCode => $optValue) {
                if (is_null($optValue)) {
                    $auxOptValue = null;
                } elseif (isset($options[$optCode]) && ($options[$optCode]->getType() === 'date_time' || $options[$optCode]->getType() === 'date' || $options[$optCode]->getType() === 'time')) {
                    $auxOptValue = isset($optValue['date_internal']) ? array(date('d-m-Y H:i:s', strtotime($optValue['date_internal']))) : null;
                } elseif (is_array($optValue)) {
                    $auxOptValue = $optValue;
                } else {
                    $auxOptValue = array($optValue);
                }
                $item['options'][] = array(
                    'option_id'    => $optCode,
                    'option_value' => $auxOptValue,
                    'option_type'  => isset($options[$optCode]) ? $options[$optCode]->getType() : ''
                );
            }
        }

        if ($buyRequest->getSuperAttribute() and !$quoteItem->getParentItem()) {
            foreach ($buyRequest->getSuperAttribute() as $optCode => $optValue) {
                $auxAttribute = Mage::getModel('eav/entity_attribute')->load($optCode);
                $item['super_attribute'][] = array(
                    'option_id'    => $optCode,
                    'option_code'  => $auxAttribute->getId() ? $auxAttribute->getAttributeCode() : '',
                    'option_value' => $optValue,
                    'option_type'  => isset($options[$optCode]) ? $options[$optCode]->getType() : ''
                );
            }
        }

        if ($buyRequest->getBundleOption() and !$quoteItem->getParentItem()) {
            $boQty = $buyRequest->getBundleOptionQty();
            foreach ($buyRequest->getBundleOption() as $optCode => $optValue) {
                if (is_null($optValue)) {
                    $auxOptValue = null;
                } elseif (isset($options[$optCode]) && ($options[$optCode]->getType() === 'date_time' || $options[$optCode]->getType() === 'date' || $options[$optCode]->getType() === 'time')) {
                    $auxOptValue = isset($optValue['date_internal']) ? array(date('d-m-Y H:i:s', strtotime($optValue['date_internal']))) : null;
                } elseif (is_array($optValue)) {
                    $auxOptValue = $optValue;
                } else {
                    $auxOptValue = array($optValue);
                }
                $item['bundle_option'][] = array(
                    'option_id'    => $optCode,
                    'option_value' => $auxOptValue,
                    'option_type'  => isset($options[$optCode]) ? $options[$optCode]->getType() : '',
                    'option_qty'   => isset($boQty[$optCode]) ? $boQty[$optCode] : 1
                );
            }
        }

        $posItem = new Varien_Object($item);
        Mage::dispatchEvent(self::CART_ITEM_RETURN_BEFORE, array('quote_item' => $quoteItem, 'pos_item' => $posItem));

        return $posItem->getData();
    }

    /**
     * Check if the customer associated to an order is guest.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function customerInOrderIsGuest(Mage_Sales_Model_Order $order)
    {
        return ((int)$order->getCustomerIsGuest() === 1);
    }


    /**
     * Check if the customer associated to an order is guest
     * or is the default customer from POS device.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function customerInOrderIsGuestOrDefault(Mage_Sales_Model_Order $order)
    {
        $isGuest = $this->customerInOrderIsGuest($order);
        $isDefault = 0;

        $posOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');
        if ($posOrder->getId()) {
            $isDefault = (int)$posOrder->getUsesDefaultCustomer();
        }

        return ($isGuest or $isDefault);
    }

    /**
     * @param $data
     * @param $customer
     * @param $customerId
     */
    public function setCustomerSessionVariables($data, $customer)
    {
        $session = Mage::getSingleton('checkout/session');

        $session->setCustomer($customer)
            ->setCustomerId($customer->getId());

        Mage::getSingleton('customer/session')
            ->setCustomer($customer)
            ->setCustomerGroupId($customer->getGroupId());

        $payments = (array)$data['payment']['addedPayments'];
        if ($data['payment']['method'] == 'bakerloo_magestorecredit') {

            $session->setBaseCustomerCreditAmount($data['payment']['amount']);
            Mage::register('pos_credit_amount', $data['payment']['amount']);

        } elseif (!empty($payments)) {

            foreach ($payments as $_payment) {
                if ($_payment['method'] == 'bakerloo_magestorecredit') {
                    $session->setBaseCustomerCreditAmount($_payment['amount']);
                    Mage::register('pos_credit_amount', $_payment['amount']);
                }
            }

        }
    }

    /**
     * @param $data
     */
    public function setShippingAddressToQuote($data)
    {
        $shippingAddress = $this->_getAddress($data['customer']['shipping_address'], $data['customer']['email']);

        $discardPromotions = false;

        if (!isset($data['discard_promotions'])) {
            $data['discard_promotions'] = false;
        }

        if ($data['discard_promotions'] === true and empty($data['coupon_code'])) {
            $discardPromotions = $data['discard_promotions'];
        }

        $this->getQuote()->getShippingAddress()
            ->addData($shippingAddress)
            ->setFreeShipping(false)
            ->setDiscardPromotions($discardPromotions)
            ->save();

        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true)->save();
    }

    /**
     * @param $storeId
     * @param $giftCards
     */
    protected function setGiftCardsToQuote($storeId, $giftCards)
    {
        if (empty($giftCards))
            return;

        Mage::getSingleton('checkout/session')
            ->unsetData('gift_codes')
            ->unsetData('codes_discount');

        foreach ($giftCards as $_giftCardCode) {
            $integrationData = array('integration_type' => 'gifting', 'store_id' => $storeId, 'code' => $_giftCardCode);
            Mage::getModel('bakerloo_restful/integrationDispatcher', $integrationData)
                ->addToCart($this->getQuote());
        }

    }

    /**
     * @param $rules
     */
    protected function setRewardsToQuote($rules)
    {
        if (empty($rules) or is_null($this->_loyalty)) {
            return;
        }

        $this->_loyalty->applyRewardsToQuote($this->getQuote(), $rules);
    }

    /**
     * @param $data
     * @return bool
     */
    protected function checkAndSetCoupon($data)
    {
        if (isset($data['coupon_code']) && !empty($data['coupon_code'])) {
            $couponCode = $data['coupon_code'];

            $this->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '');

            return true;
        }

        return false;
    }

    /**
     * @param bool $hasGiftCards
     */
    protected function setFreePaymentMethodToQuote($hasGiftCards = false)
    {
        if ($hasGiftCards) {
            $this->getQuote()->getPayment()->importData(array('method' => 'free'));
        } else {
            $this->getQuote()->getPayment()->importData(array('method' => 'bakerloo_free'));
        }
    }

    /**
     * @param $data
     */
    public function setPaymentMethodToQuote($data)
    {
        //Use Reward Points
        if (isset($data['payment']['use_reward_points']) and ((int)$data['payment']['use_reward_points'] === 1)) {
            $this->getQuote()->setUseRewardPoints(true);
        }

        //Use Customer Balance
        if (isset($data['payment']['use_customer_balance']) and ((int)$data['payment']['use_customer_balance'] === 1)) {
            $this->getQuote()->setUseCustomerBalance(true);
        }

        $this->getQuote()->getPayment()->importData((array)$data['payment']);
    }

    protected function collectQuoteTotals()
    {
        if ($this->getQuote()->isVirtual()) {
            $this->getQuote()->getBillingAddress()->getTotals();
        } else {
            $this->getQuote()->getShippingAddress()->collectTotals();
        }
    }

    /**
     * @param $products
     * @return array
     */
    protected function getProductsById($products)
    {
        $productsById = array();

        foreach ($products as $_product) {
            if (isset($productsById[$_product['product_id']])) {
                $productsById[$_product['product_id']][] = $_product;
            } else {
                $productsById[$_product['product_id']] = array($_product);
            }
        }

        return $productsById;
    }

    /**
     * @param $productIds
     * @return array
     */
    protected function getProductItems($productIds, $fastProducts = false)
    {
        $attributes = Mage::helper('bakerloo_restful')->config('checkout/fast_product_attributes', Mage::app()->getStore()->getId());
        $attributes = explode(',', $attributes);
        $attributes = array_unique(array_merge($this->_defaultAttr, $attributes));

        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('entity_id', array('in' => $productIds))
            ->setPageSize(count($productIds))
            ->setCurPage(1);

        foreach ($attributes as $attr) {
            $productCollection->addAttributeToSelect($attr);
        }

        $productItems = $productCollection->getItems();

        if ($fastProducts) {
            $productItems = $this->_loadStockForProducts($productItems);
        }

        return $productItems;
    }

    /**
     * @param $_product
     * @param $product
     * @param $quoteItem
     */
    protected function applyRewardsToQuoteItem($_product, $product, $quoteItem)
    {
        if (!isset($_product['loyalty']) or empty($_product['loyalty'])) {
            return;
        }

        foreach ($_product['loyalty'] as $rule) {
            Mage::getSingleton('rewards/catalogrule_saver')->writePointsToQuote(
                $product,
                (int)$rule['rule_id'],
                $rule['rule_uses'],
                1,
                $quoteItem
            );
        }

    }

    /**
     * @param $_product
     * @param $quoteItem
     */
    protected function setPosDiscountReasonToQuoteItem($_product, $quoteItem)
    {
        if (!isset($_product['discount_reason'])) {
            return;
        }

        if ($quoteItem->getParentItem()) {
            $quoteItem->getParentItem()->setPosDiscountReason($_product['discount_reason']);
        } else {
            $quoteItem->setPosDiscountReason($_product['discount_reason']);
        }

    }

    /**
     * Always delete quote if throwing exception during buildQuote process.
     * Otherwise, active quotes are left in the customer's name.
     *
     * @param $message
     * @throws Mage_Core_Exception
     */
    protected function throwBuildQuoteException($message)
    {
        if ($this->getQuote()) {
            $this->getQuote()->delete();
        }

        Mage::throwException($message);
    }

    /**
     * @return array
     */
    public function getResourceEavAttribute()
    {
        return Mage::getModel('catalog/resource_eav_attribute');
    }

    public function getModelCustomer()
    {
        return Mage::getModel('customer/customer');
    }

    public function getQuoteSales()
    {
        return Mage::getModel('sales/quote');
    }

    public function getStore()
    {
        return Mage::app()->getStore();
    }
}
