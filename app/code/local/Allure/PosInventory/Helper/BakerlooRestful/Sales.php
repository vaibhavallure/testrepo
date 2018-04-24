<?php

class Allure_PosInventory_Helper_BakerlooRestful_Sales extends Ebizmarts_BakerlooRestful_Helper_Sales
{
	var $_allureHelper;
	
	private function _getHelper() {
		if (!isset($this->_allureHelper)) {
			$this->_allureHelper = Mage::helper('allure_posinventory');
		}
		
		return $this->_allureHelper;
	}
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
        
        
        // Skip Add Inventory for Date Ranges
        $isSkippedDate = false;
        if ($this->_getHelper()->isEnabled()) {
        	
	        	if ($this->_getHelper()->getSkipStockBefore() || $this->_getHelper()->getSkipStockAfter()) {
	        		
	        		$dateOrdered = $data['order_date'];
	        		
	        		$orderDate = new DateTime($dateOrdered);
	        		
	        		$beforeDate = $afterDate = null;
        			
        			$beforeDateConfig = $this->_getHelper()->getSkipStockBeforeDate();
        			$afterDateConfig = $this->_getHelper()->getSkipStockAfterDate();
        			
        			//var_dump($beforeDateConfig);
        			//var_dump($afterDateConfig);
        			
        			if ($this->_getHelper()->getSkipStockBefore() && !empty($beforeDateConfig)) {
        				$beforeDate = new DateTime($beforeDateConfig);
        				//var_dump($beforeDate->format('Y-m-d H:i:s'));
        			}
        			
        			if ($this->_getHelper()->getSkipStockAfter() && !empty($afterDateConfig)) {
        				$afterDate = new DateTime($afterDateConfig);
        				//var_dump($afterDate->format('Y-m-d H:i:s'));
        			}
        			
        			if ($beforeDate || $afterDate) {
        				$skipBeforeDate = $skipAfterDate = false;
        				
        				if ($beforeDate && ($orderDate < $beforeDate)) $skipBeforeDate = true;
        				if ($afterDate && ($orderDate >= $afterDate)) $skipAfterDate = true;
        				
        				if ($beforeDate && $afterDate) {
        					$isSkippedDate = ($skipBeforeDate && $skipAfterDate);
        				} else {
        					$isSkippedDate = ($skipBeforeDate || $skipAfterDate);
        				}
        				
        				//echo "skipBeforeDate::";var_dump($skipBeforeDate);
        				//echo "skipAfterDate::";var_dump($skipAfterDate);
        			}
        			
        			//echo "isSkippedDate::";var_dump($isSkippedDate);
        			
        			//var_dump($orderDate->format('Y-m-d H:i:s'));
        		}
        }
        
        //var_dump($this->getQuote()->getData());
        
        if ($isSkippedDate) {
	        	if (!Mage::registry('allure_posinventory_skipped_date')) {
	        		Mage::register('allure_posinventory_skipped_date', true);
	        	}
        		$this->getQuote()->setIsProcessed(true);
        }
        
        //var_dump($this->getQuote()->getData());
        
        //var_dump($data);die;

        //allure-02 start 
        if(count($data['returns']) > 0){
            $data['products'] = array_merge($data['products'],$data['returns']);   
        }
        //allure-02 end
        
        
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
                    ->setPaymentMethod($data['payment']['method']);
                    $this->getQuote()->getShippingAddress()->setShippingMethod($data['shipping']);
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
            || $data['payment']['method'] == 'bakerloo_magestorecredit'
        ) {
            $quote->setTotalsCollectedFlag(true);
        }
        
        if ($data['total_amount'] != 0) {
            $this->setPaymentMethodToQuote($data);
        } else {
            /* workaround in case full payment is made with gift card */
            $this->setFreePaymentMethodToQuote(!empty($giftCards));
        }

        //Commented on January, 6 2015 to fix issue with coupons applied twice on bundle products.
        //$this->getQuote()->collectTotals()->save();
        if(empty($this->getQuote()->getShippingAddress()->getShippingMethod()))
            $this->getQuote()->getShippingAddress()->setShippingMethod($data['shipping']);
        $this->getQuote()->save();

        //If coupon was provided and does not validate, throw error.
        if ($checkCouponOK and !$this->getQuote()->getCouponCode()) {
            $this->throwBuildQuoteException(Mage::helper('bakerloo_restful')->__('Discount coupon could not be applied, please try again.'));
        }

        Varien_Profiler::stop('POS::' . __METHOD__);
        
        $this->getQuote()->collectTotals()->save(); //allure-02
        return $this->getQuote();
    }


    public function _addProductsToQuote($products)
    {
        Varien_Profiler::start('POS::' . __METHOD__);

        $useSimplePrice = (int)Mage::helper('bakerloo_restful')->config('general/simple_configurable_prices', Mage::app()->getStore()->getId());
        $fastProducts   = (int)Mage::helper('bakerloo_restful')->config('checkout/fast_product_load', Mage::app()->getStore()->getId());

        if (((int)Mage::helper('bakerloo_restful')->config('catalog/allow_backorders'))) {
            if (!Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES)) {
                Mage::register(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES, true);
            }
        }

        $productsById = $this->getProductsById($products);
        $productItems = $this->getProductItems(array_keys($productsById), $fastProducts);
        
        //echo "<pre>";print_r(array_keys($productsById));print_r($productItems);die;

        foreach ($productsById as $_id => $_products) {

            foreach ($_products as $_product) {
            		if (!$productItems || !isset($productItems[$_id])) {
            			$this->throwBuildQuoteException("Product ID: {$_id} does not exist.");
            		}
            		
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
                    // Update by Allure - Skip Stock Check true always
                    if (Mage::helper('bakerloo_restful')->dontCheckStock()) {
                        $product->getStockItem()->setData('use_config_manage_stock', 0);
                        $product->getStockItem()->setData('manage_stock', 0);
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

                    //allure-02 start 
                   /*  if(array_key_exists("options", $buyInfo)){
                        if(count($buyInfo['options']) > 0){
                            if(array_key_exists("super_attribute", $buyInfo)){
                                foreach ($buyInfo['super_attribute'] as $keyC => $valueC){
                                    if($keyC != 209){
                                        unset($buyInfo['super_attribute'][$keyC]);
                                    }
                                }
                            }
                        }
                    } */
                    if($buyInfo['qty'] > 0){
                        $quoteItem = $this->getQuote()->addProduct($product, new Varien_Object($buyInfo));
                    }else{
                        $quoteItem = $this->getQuote()->addProductAsNew($product, new Varien_Object($buyInfo));
                    }
                    //allure-02 end 
                    
                    //old code
                    //$quoteItem = $this->getQuote()->addProduct($product, new Varien_Object($buyInfo));
                    
                    //Rewards integrations
                    $this->applyRewardsToQuoteItem($_product, $product, $quoteItem);

                } catch (Exception $qex) {
                    $this->throwBuildQuoteException("An error occurred, Product SKU: {$product->getSku()}. Error Message: {$qex->getMessage()}");
                }

                if (is_string($quoteItem)) {
                    $this->throwBuildQuoteException($quoteItem . ' Product ID: ' . $_product['product_id']);
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
            $item->setRowTotalWithDiscount($item->getRowTotal());
            $item->save();
        }

        $this->reloadQuote();
        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    protected function _loadStockForProducts($products)
    {

        $productIds = array_keys($products);
        
        $stock = Mage::getModel('cataloginventory/stock');

        $stockItemCol = Mage::getResourceModel('cataloginventory/stock_item_collection')
            ->addStockFilter($stock)
            ->addFieldToFilter('product_id', array('in' => $productIds));

        foreach ($stockItemCol as $_sItem) {
            $product = $products[$_sItem->getProductId()];
            $product->setStockItem($_sItem);
        }

        return $products;
    }
    
    public function processFailedOrder($id){
        if(Mage::registry('failed_ebiz_order'))
            Mage::unregister('failed_ebiz_order');
        Mage::register('failed_ebiz_order', $id);
        
        $orderProcessor = new POS_Order();
        $orderProcessor->place($id);
        
    }
    
    /**
     * @param $data
     * @return bool
     */
    protected function checkAndSetCoupon($data)
    {
        if (isset($data['coupon_code']) && !empty($data['coupon_code'])) {
            $couponCode = $data['coupon_code'];
            //allure-02 start
            if(preg_match("/POS/", $couponCode)){
                return false;
            }
            //allure-02 end
            $this->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '');
            
            return true;
        }
        
        return false;
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
}
