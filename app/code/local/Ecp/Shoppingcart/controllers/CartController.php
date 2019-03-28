<?php

//@GC Cart
include_once("Mage/Checkout/controllers/CartController.php");

class Ecp_Shoppingcart_CartController extends Mage_Checkout_CartController
{
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
        
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        if (!Mage::getSingleton('checkout/session')->getContinueShoppingUrl(true)) {
            Mage::getSingleton('checkout/session')->setContinueShoppingUrl($_SERVER['HTTP_REFERER']);
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);
        
        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
    
    /**
    *	store gift message
    */
    private function storeGiftMessage($specialInstruction){
    	$product = $this->_initProduct();
    	$quote = $this->_getCart()->getQuote();
    	
    	if ($quote && $quote->getId()) {
    		$giftMessage = Mage::getModel('giftmessage/message');
    		//commented by Allure
            //$quoteItem = $quote->getItemByProduct($product);
    		
    		//temp solution added by Allure
    		$quoteItem = null;
    		foreach ($quote->getAllItems() as $item) {
    			if ($item->getProductId()==$product->getId()) {
    				$quoteItem =  $item;
    			}
    		}
    		
    		if($quoteItem && $quoteItem->getGiftMessageId()) {
    			$giftMessage->load($quoteItem->getGiftMessageId());
    		}

    		$customerSession = Mage::getSingleton('customer/session');
    		if($customerSession->isLoggedIn()){
    			$giftMessage->setCustomerId($customerSession->getCustomerId());
    		}
    		
    		if ($specialInstruction != '') {
	    		try {
	    			$giftMessage
	    				->setMessage($specialInstruction)
	    				->save();
	    			$quoteItem->setGiftMessageId($giftMessage->getId())->save();
	    		}
	    		catch (Exception $e) { }
    		}

    		if($giftMessage->getId() && $giftMessage->getMessage() == '') {
    			try{
    				$giftMessage->delete();
    				$quoteItem->setGiftMessageId(0)
    				->save();
    			}
    			catch (Exception $e) { }
    		}
    	}
    }
    private  function storePurchasedFromCategory($purchasedFrom){
        $product = $this->_initProduct();
        $quote = $this->_getCart()->getQuote();
        
        if ($quote && $quote->getId()) {
          
            $quoteItem = null;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductId()==$product->getId()) {
                    $quoteItem =  $item;
                }
            }
            if ($purchasedFrom != '') {
                try {
                    $quoteItem->setPurchasedFrom($purchasedFrom)->save();
                }
                catch (Exception $e) {
                    Mage::log("Exception".$e->getMessage(),Zend_log::DEBUG,'excpetion.log',true);
                }
            }
        }
    }
    
    /**
     * Add product to shopping cart action
     */
     public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();  
        $ajax = $this->getRequest()->isXmlHttpRequest();
        $specialInstruction = (isset($params['gift-special-instruction']) && !empty($params['gift-special-instruction'])) ? trim($params['gift-special-instruction']) : false;
        $purchasedFrom = (isset($params['purchased_from_cat']) && !empty($params['purchased_from_cat'])) ? trim($params['purchased_from_cat']) : false;
        
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                if ($ajax) {
                    $this->getResponse()->setBody(false);
                    exit;
                } else {
                    $this->_goBack();
                    return;
                }
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            if ($specialInstruction) {
            	$this->storeGiftMessage($specialInstruction);
            }
            if ($purchasedFrom) {
                $this->storePurchasedFromCategory($purchasedFrom);
            }
            
            $this->_getSession()->setCartWasUpdated(true);
            
            if ($ajax) {
                
                $content = $this->getLayout()
                        ->createBlock('checkout/cart_sidebar')
                        ->setTemplate('checkout/cart/sidebar.phtml')                    
                        ->toHtml();
                $this->getResponse()->setBody($content);
                $cookie = Mage::getSingleton('core/cookie');
                $_json = json_decode($cookie->get('current_cart'),true);
                $_json[$product->getId()] = $product->getPrice();
                $cookie->set('current_cart',json_encode($_json),time()+60*60*24*15);
                return;
            } else {
                
                /**
                 * @todo remove wishlist observer processAddToCart
                */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
                
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()){
                        $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($message);
                    }
                    $this->_goBack();
                }
            }
            
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

        
    /**
     * Add product to shopping cart action
     */
    public function eaddAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $specialInstruction = (isset($params['gift-special-instruction']) && !empty($params['gift-special-instruction'])) ? trim($params['gift-special-instruction']) : false;
        
        if(isset($params['sp'])){
            $params['super_attribute'] = unserialize($params['sp']);
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            if ($specialInstruction) {
            	$this->storeGiftMessage($specialInstruction);
            }

            $this->_getSession()->setCartWasUpdated(true);
            
            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
    
    public function addListsAction()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');

        $groupProducts = $this->getRequest()->getParam('order_items', array());
        $response = array('succes' => false);
        $cart = $this->_getCart();

        $quote = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();

        if (empty($quote)) {
            //echo "no trae nada";
            $this->_makeRielQuote();
        }

        $params['qty'] = 1;
        $productsIds = array();
        $productsQty = array();
        $groupProducts = explode(",", $groupProducts);
        //var_dump($groupProducts);
        foreach ($groupProducts as $product) {
            $groupProducts2 = explode(":", $product);

            $productsIds[] = $groupProducts2[0];
            $productsQty[] = $groupProducts2[1];
            //var_dump($groupProducts2);

        }
//        echo "y los ids";
//        var_dump($productsIds);
//        echo "y los qty";
//            var_dump($productsQty);
        $items_array = array();


        if (isset($productsIds)) {
            if (is_array($productsIds)) {
                $itemsCollection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addIdFilter($productsIds)
                    ->load();
                /* @var $itemsCollection Mage_Sales_Model_Mysql4_Order_Item_Collection */
                $ind = 0;
                $i = 0;
                $array_size = 0;
                foreach ($itemsCollection as $product) {
                    $id = $product->getId();
                    $array_size = count($productsIds);
                    for ($i = 0; $i < $array_size; $i++) {
                        if ($id == $productsIds[$i]) {
                            $params['qty'] = $productsQty[$i];
                        }
                    }
                    //$params['qty'] = ($productsQty[$ind]) ? $productsQty[$ind] : '1';

                    /*$exists = false;

                    $ses = Mage::getSingleton('checkout/session');
                    foreach ($ses->getQuote()->getAllItems() as $item) {
                        if($item->getProduct()->getAttributeSetId() == 10 && $item->getProductId() == $product->getId()){
                            $exists = true;
                            break;
                        }
                    }*/

                    if (isset($params['qty'])) {
                        $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
                        );
                        $params['qty'] = $filter->filter($params['qty']);
                    }
                    try {
                        $product = $this->_loadRielProducts($id);
                        //var_dump($product->getData());
                        $related = $this->getRequest()->getParam('related_product');


                        if (!$product) {
                            $response['message'][] = 'Producto no disponible, intente mas tarde.';
                            die(Mage::helper('core')->jsonEncode($response));
                        }

                        //        if(!$exists){
                        $cart->addProduct($product, $params);
                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }
                        //}
                        $cart->save();

                        $response['succes'] = true;
                        $response['multiple'] = true;
                        $response['product']['sku'] = $product->getSku();
                        $response['product']['id'] = $product->getId();
                        $response['product']['url'] = $product->getProductUrl();
                        $response['product']['img'] = (string)Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(140, 105);
                        //$response['product']['img'] = (string) Mage::helper('catalog/image')->init($product, 'small_image')->resize(140, 105);
                        $response['product']['name'] = $product->getName();
                        $response['product']['qty'] = $product->getQty();
                        $product_category = $product->getCategoryIds(); // ? $product->getCategory()->getName() : 'no category';
                        $response['product']['category'] = $product_category;
                        //$response['product']['price'] = 'ZZZ';
                        $response['product']['normalPrice'] = Mage::helper('core')->currency($product->getPrice(), true, false);

                        $discount = $product->getPrice() - $product->getFinalPrice();
                        if ($discount != 0)
                            $response['product']['discount'] = Mage::helper('core')->currency($product->getPrice() - $product->getFinalPrice(), true, false);
                        else
                            $response['product']['discount'] = false;

                        $response['product']['priceOnly'] = Mage::helper('core')->currency($product->getFinalPrice(), true, false);
                        //$response['product']['price'] = $product->getFinalPrice();
                        $response['product']['currency'] = Mage::helper('core')->currency($product->getQty() * $product->getFinalPrice(), true, false);

                        //$response['product']['editurl'] = Mage::getUrl('checkout/cart/configure',array('id' => $product->getEntityId()));
                        $response['product']['editurl'] = $product->getProductUrl();
                        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
                        foreach ($totals as $key => $total)
                            $response['cart'][$key] = Mage::helper('core')->currency($total->getValue(), true, false);
                        $this->_getSession()->setCartWasUpdated(true);

                        ///////////////////////////////////////////////////////////////////////////////////////
                        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                        //$subtotal = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal();
                        $onlyProducts = Mage::getSingleton('checkout/session')->getQuote()->getItemsCount();
                        Mage::getSingleton('core/session')->setMyProducts($onlyProducts);
                        $response['cart']['allItems'] = Mage::helper('checkout/cart')->getCart()->getItemsCount();
                        //$response['cart']['allItems'] = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
                        //$response['cart']['allItems'] = Mage::getSingleton('core/session')->getMyProducts();
                        //$totales =  Mage::getSingleton('checkout/session')->getQuote()->getSubtotalInclTax();

                        $response['cart']['totalQty'] = 0;
                        $response['cart']['grand_total'] = 0;
                        $response['cart']['iva'] = 0;
                        $response['cart']['subTotal'] = 0;
                        $response['cart']['onlyProducts'] = $onlyProducts;
                        $response['product']['stock'] = 0;
                        $incl_tax = Mage::helper('tax')->priceIncludesTax();
                        $totalIva = 0;
                        $rate_id = null;
                        $porcentaje_iva = 0;
                        $configurable_product = Mage::getModel('catalog/product_type_configurable');
                        $priceTotal = 0;
                        $subtotal = 0;
                        $totalQty = 0;

                        foreach ($items as $item) {
                            $itemQty = $item->getQty();
                            $rate_id = null;
                            $porcentaje_iva = 0;
//                            $item->setPriceDiscountAmount($item->getProduct()->getPrice()-$item->getProduct()->getFinalPrice());
//                            $item->save();
                            if ($item->getProduct()->getTypeId() == 'simple') {

                                $thisProduct = Mage::getModel('catalog/product')->load($item->getProduct()->getId());

                                $parentId = $configurable_product->getParentIdsByChild($item->getProduct()->getId());
                                if (!empty($parentId)) {
                                    $temp = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                                    foreach ($temp as $configurable) {
                                        if ($configurable->getProduct()->getId() == $parentId[0] && $item->getSku() == $configurable->getSku()) {
                                            $response['product']['name'] = $thisProduct->getName();
                                            $thisQty = $configurable->getQty();

                                            $tax_class = Mage::getModel('tax/calculation')->getCollection()
                                                ->addFieldToFilter('product_tax_class_id', $configurable->getProduct()->getTaxClassId());
                                            foreach ($tax_class as $items) {
                                                $rate_id = $items->getTaxCalculationRateId();
                                            }

                                            if ($rate_id != null) {
                                                $rate = Mage::getModel('tax/calculation_rate')->load($rate_id);
                                                $porcentaje_iva = $rate->getRate() / 100;
                                            }

                                            $response['porcentajeIva'] = $porcentaje_iva;
                                            $totalQty += $configurable->getQty();

                                            if ($product->getSku() == $item->getProduct()->getSku()) {

                                                if (!$incl_tax) {
                                                    $ivaThisProduct = $configurable->getProduct()->getFinalPrice() * $porcentaje_iva;
                                                    $totalIvaThisProduct = $ivaThisProduct * $configurable->getQty();
                                                    $response['product']['iva'] = Mage::helper('core')->currency($totalIvaThisProduct, true, false);
                                                    $response['product']['priceOnly'] = Mage::helper('core')->currency($configurable->getProduct()->getFinalPrice(), true, false);
                                                    $priceTotal = $configurable->getQty() * $configurable->getProduct()->getFinalPrice();

                                                    $subtotal += $priceTotal;
                                                    $totalIva += $totalIvaThisProduct;
                                                } else {

                                                    $precioMenosIva = $configurable->getProduct()->getFinalPrice() / (1 + $porcentaje_iva);
                                                    $ivaThisProduct = $configurable->getProduct()->getFinalPrice() - $precioMenosIva;
                                                    $totalIvaThisProduct = $ivaThisProduct * $configurable->getQty();

                                                    $response['product']['priceOnly'] = Mage::helper('core')->currency($precioMenosIva, true, false);
                                                    $response['product']['iva'] = $totalIvaThisProduct;

                                                    $priceTotal = $configurable->getQty() * $precioMenosIva;

                                                    $subtotal += $priceTotal;
                                                    $totalIva += $totalIvaThisProduct;

                                                }

                                            } else {

                                                if (!$incl_tax) {

                                                    $ivaThisProduct = $configurable->getProduct()->getFinalPrice() * $porcentaje_iva;
                                                    $totalIvaThisProduct = $ivaThisProduct * $configurable->getQty();

                                                    $subtotal += $configurable->getQty() * $configurable->getProduct()->getFinalPrice();
                                                } else {

                                                    $precioMenosIva = $configurable->getProduct()->getFinalPrice() / (1 + $porcentaje_iva);
                                                    $ivaThisProduct = $configurable->getProduct()->getFinalPrice() - $precioMenosIva;
                                                    $totalIvaThisProduct = $ivaThisProduct * $thisQty;

                                                    $subtotal += $configurable->getQty() * $precioMenosIva;
                                                }
                                                $totalIva += $totalIvaThisProduct;
                                            }

                                            $response['product']['deleteurl'] = Mage::getUrl('checkout/cart/delete', array('id' => $item->getId()));
                                        }
                                    }
                                } else {

                                    $tax_class = Mage::getModel('tax/calculation')->getCollection()
                                        ->addFieldToFilter('product_tax_class_id', $item->getProduct()->getTaxClassId());
                                    foreach ($tax_class as $items) {
                                        $rate_id = $items->getTaxCalculationRateId();
                                    }

                                    if ($rate_id != null) {
                                        $rate = Mage::getModel('tax/calculation_rate')->load($rate_id);
                                        $porcentaje_iva = $rate->getRate() / 100;
                                    }

                                    $response['porcentajeIva'] = $porcentaje_iva;
                                    $totalQty += $item->getQty();

                                    if ($thisProduct->getId() == $product->getId()) {

                                        $response['product']['name'] = $thisProduct->getName();
                                        //$response['product']['priceOnly'] = $product->getFinalPrice();

                                        $discount = $product->getPrice() - $product->getFinalPrice();
                                        if ($discount != 0)
                                            $response['product']['discount'] = Mage::helper('core')->currency($product->getPrice() - $product->getFinalPrice(), true, false);
                                        else
                                            $response['product']['discount'] = false;

                                        if (!$incl_tax) {
                                            $response['product']['iva'] = $product->getFinalPrice() * $porcentaje_iva * $item->getQty();
                                            $priceTotal = $item->getQty() * $product->getFinalPrice();
                                            $totalIva += $response['product']['iva'];
                                            $response['product']['priceOnly'] = Mage::helper('core')->currency($product->getFinalPrice(), true, false);
                                        } else {
                                            $precioMenosIva = $product->getFinalPrice() / (1 + $porcentaje_iva);
                                            $ivaThisProduct = $product->getFinalPrice() - $precioMenosIva;
                                            $totalIvaThisProduct = $ivaThisProduct * $item->getQty();

                                            $response['product']['iva'] = Mage::helper('core')->currency($totalIvaThisProduct, true, false);
                                            $response['product']['priceOnly'] = Mage::helper('core')->currency($precioMenosIva, true, false);
                                            $priceTotal = $item->getQty() * $precioMenosIva;
                                            $totalIva += $totalIvaThisProduct;
                                        }

                                        $subtotal += $priceTotal;

                                    } else {
                                        if (!$incl_tax) {

                                            $response['thisproductFinalPrice'] = $thisProduct->getFinalPrice();
                                            $response['totalIva'] = $thisProduct->getFinalPrice() * $porcentaje_iva;
                                            $totalIva += $thisProduct->getFinalPrice() * $porcentaje_iva * $item->getQty();
                                            $subtotal += $item->getQty() * $thisProduct->getFinalPrice();
                                        } else {

                                            $precioMenosIva = $item->getProduct()->getFinalPrice() / (1 + $porcentaje_iva);
                                            $ivaThisProduct = $item->getProduct()->getFinalPrice() - $precioMenosIva;
                                            $totalIvaThisProduct = $ivaThisProduct * $item->getQty();

                                            $totalIva += $totalIvaThisProduct;
                                            $subtotal += $item->getQty() * $precioMenosIva;

                                        }
                                    }

                                    $response['product']['deleteurl'] = Mage::getUrl('checkout/cart/delete', array('id' => $item->getId()));

                                }
                                $response['product']['price'] = Mage::helper('core')->currency($priceTotal, true, false);

                                $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
                                foreach ($totals as $key => $total)
                                    $response['cart'][$key] = Mage::helper('core')->currency($total->getValue(), true, false);

                                Mage::getSingleton('core/session')->setMyProducts($onlyProducts);

                                $response['cart']['allItems'] = Mage::helper('checkout/cart')->getCart()->getItemsCount();
                                //$response['cart']['allItems'] = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
                                //$response['cart']['allItems'] = Mage::getSingleton('core/session')->getMyProducts();

                                $response['Product-getId'] = $product->getId();
                                $response['item']['qty'] = $itemQty;
                                //$response['product']['stock'] = (int) Mage::getModel('cataloginventory/stock_item')->load($item->getProduct()->getId())->getQty();

                                //$response['cart']['grand_total'] += $item->getQty() * $product->getFinalPrice();

                            }
                        }

                        //$total = $response['cart']['grand_total'];

                        //$iva = $total * 0.16;
                        //$response['product']['price'] = Mage::helper('core')->currency($item->getQty() * $product->getFinalPrice(),true,false);

                        $response['cart']['iva'] = Mage::helper('core')->currency($totalIva, true, false);

                        $response['cart']['grand_total'] = Mage::helper('core')->currency($subtotal + $totalIva, true, false);

                        $response['cart']['subTotal'] = Mage::helper('core')->currency($subtotal, true, false);

                        $response['cart']['totalQty'] = $totalQty;
                        /**
                         * @todo remove wishlist observer processAddToCart
                         */
                        Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                        );

                        if (!$this->_getSession()->getNoCartRedirect(true)) {
                            if (!$cart->getQuote()->getHasError()) {
                                $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                                $response['message'][] = $message;
                                //$this->_getSession()->addSuccess($message);
                                $items_array['item'][] = $response['product'];
                                $items_array['succes'] = $response['succes'];
                                $items_array['message'] = $response['message'];
                                $items_array['multiple'] = $response['multiple'];
                                $items_array['cart'] = $response['cart'];

                            }
                            //$this->_goBack();
                            //die(Mage::helper('core')->jsonEncode($response));
                        }
                    } catch (Mage_Core_Exception $e) {
                        if ($this->_getSession()->getUseNotice(true)) {
                            //$this->_getSession()->addNotice($e->getMessage());
                            $response['message'][] = $e->getMessage();
                        } else {
                            $messages = array_unique(explode("\n", $e->getMessage()));
                            foreach ($messages as $message) {
                                //$this->_getSession()->addError($message);
                                $response['message'][] = $e->getMessage();
                                $items_array['message'] = $response['message'];
                            }
                        }
                        $response['succes'] = false;
                        $items_array['succes'] = $response['succes'];
                        die(Mage::helper('core')->jsonEncode($response));

                        /* $url = $this->_getSession()->getRedirectUrl(true);
                          if ($url) {
                         *
                          $this->getResponse()->setRedirect($url);
                          } else {
                          $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
                          } */
                    } catch (Exception $e) {
                        $response['succes'] = false;
                        $response['message'][] = $e->getMessage();
                        $response['message'][] = $this->__('Cannot add the item to shopping cart.');
                        $items_array['succes'] = $response['succes'];
                        $items_array['message'] = $response['message'];
                        //$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                        Mage::logException($e);
                        //$this->_goBack();
                        die(Mage::helper('core')->jsonEncode($response));
                    }
                    $ind++;
                }

                die(Mage::helper('core')->jsonEncode($items_array));

            }
        }
    }

    protected function _loadRielProducts($id)
    {
        $productId = (int)$id;
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    //When the quote isn´t loading, this method help to generate it
    protected function _makeRielQuote()
    {
        $checkout = Mage::getSingleton('checkout/session');
        $quote = $checkout->getQuote();
        $session = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {
            // look up customer
            $customerSession = $session->getCustomer();
            $customer = Mage::getModel('customer/customer')
                ->load($customerSession->getId());

            $quote->assignCustomer($customer);
            $quote->setIsMultiShipping(false);
            $quote->save();
        } else {
            $quote->setIsMultiShipping(false);
            $quote->save();
        }
    }

    protected function addGrouped()
    {
        $itemsCollection = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        $cart = $this->_getCart();
        $response['cart']['totalQty'] = 0;
        $response['cart']['grand_total'] = 0;
        $response['cart']['iva'] = 0;
        $response['cart']['subTotal'] = 0;
        $items_array = array();
        $items_array['totalItems'] = count($itemsCollection);
        $totalIva = 0;
        $priceTotal = 0;
        $subtotal = 0;
        $incl_tax = Mage::helper('tax')->priceIncludesTax();
        $response['incltax'] = $incl_tax;

        foreach ($itemsCollection as $item) {
            if ($item->getProduct()->getTypeId() == 'simple') {
                try {
                    $product = $item->getProduct();

                    $response['succes'] = true;
                    $response['multiple'] = true;
                    $response['product']['stock'] = 0;
                    $response['product']['qty'] = $product->getQty();
                    $response['product']['sku'] = $product->getSku();
                    $response['product']['url'] = $product->getProductUrl();
                    $response['product']['editurl'] = $product->getProductUrl();
                    $response['product']['img'] = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(140, 105);
                    $response['product']['normalPrice'] = Mage::helper('core')->currency($product->getPrice(), true, false);

                    $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();

                    $itemQty = $item->getQty();
                    $rate_id = null;
                    $porcentaje_iva = 0;
                    $item->setPriceDiscountAmount($item->getProduct()->getPrice() - $item->getProduct()->getFinalPrice());

                    $thisProduct = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                    $response["parent"] = $item->getParentItemId();

                    if ($item->getParentItemId() != null) {
                        $temp = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                        foreach ($temp as $configurable) {
                            if ($configurable->getItemId() == $item->getParentItemId() && $item->getSku() == $configurable->getSku()) {
                                $response['product']['name'] = $thisProduct->getName();
                                $thisQty = $configurable->getQty();

                                $tax_class = Mage::getModel('tax/calculation')->getCollection()
                                    ->addFieldToFilter('product_tax_class_id', $configurable->getProduct()->getTaxClassId());
                                foreach ($tax_class as $items) {
                                    $rate_id = $items->getTaxCalculationRateId();
                                }

                                if ($rate_id != null) {
                                    $rate = Mage::getModel('tax/calculation_rate')->load($rate_id);
                                    $porcentaje_iva = $rate->getRate() / 100;
                                }

                                if (!$incl_tax) {

                                    $ivaThisProduct = $configurable->getProduct()->getFinalPrice() * $porcentaje_iva;
                                    $totalIvaThisProduct = $ivaThisProduct * $configurable->getQty();

                                    $subtotal += $configurable->getQty() * $configurable->getProduct()->getFinalPrice();
                                } else {

                                    $precioMenosIva = $configurable->getProduct()->getFinalPrice() / (1 + $porcentaje_iva);
                                    $ivaThisProduct = $configurable->getProduct()->getFinalPrice() - $precioMenosIva;
                                    $totalIvaThisProduct = $ivaThisProduct * $thisQty;

                                    $subtotal += $configurable->getQty() * $precioMenosIva;
                                }
                                $totalIva += $totalIvaThisProduct;
                            }

                        }
                    } else {

                        $tax_class = Mage::getModel('tax/calculation')->getCollection()
                            ->addFieldToFilter('product_tax_class_id', $item->getProduct()->getTaxClassId());
                        foreach ($tax_class as $items) {
                            $rate_id = $items->getTaxCalculationRateId();
                        }

                        if ($rate_id != null) {
                            $rate = Mage::getModel('tax/calculation_rate')->load($rate_id);
                            $porcentaje_iva = $rate->getRate() / 100;
                        }

                        $response['porcentajeIva'] = $porcentaje_iva;

                        $response['product']['name'] = $thisProduct->getName();

                        $discount = $product->getPrice() - $product->getFinalPrice();
                        if ($discount != 0)
                            $response['product']['discount'] = Mage::helper('core')->currency($product->getPrice() - $product->getFinalPrice(), true, false);
                        else
                            $response['product']['discount'] = false;

                        if (!$incl_tax) {
                            $response['product']['iva'] = $product->getFinalPrice() * $porcentaje_iva * $item->getQty();
                            $priceTotal = $itemQty * $product->getFinalPrice();
                            $totalIva += $response['product']['iva'];
                            $response['product']['priceOnly'] = Mage::helper('core')->currency($product->getFinalPrice(), true, false);
                        } else {
                            $precioMenosIva = $product->getFinalPrice() / (1 + $porcentaje_iva);
                            $ivaThisProduct = $product->getFinalPrice() - $precioMenosIva;
                            $totalIvaThisProduct = $ivaThisProduct * $item->getQty();

                            $response['product']['iva'] = Mage::helper('core')->currency($totalIvaThisProduct, true, false);
                            $response['product']['priceOnly'] = Mage::helper('core')->currency($precioMenosIva, true, false);
                            $priceTotal = $itemQty * $precioMenosIva;
                            $totalIva += $totalIvaThisProduct;
                        }

                        $subtotal += $priceTotal;

                        $response['product']['deleteurl'] = Mage::getUrl('checkout/cart/delete', array('id' => $item->getId()));

                    }


//                $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
//                foreach ($totals as $key => $total){
//                    if($key=="grand_total")
//                        $response['cart']['grand_total'] = Mage::helper('core')->currency($total->getValue(), true, false);
//                    else if($key=="subtotal")
//                        $response['cart']['subTotal'] = Mage::helper('core')->currency($total->getValue(), true, false);
//                    else if($key=="tax")
//                        $response['cart']['iva'] = Mage::helper('core')->currency($total->getValue(), true, false);
//                    $response['cart'][$key] = Mage::helper('core')->currency($total->getValue(), true, false);
//                }                

                    $response['Product-getId'] = $product->getId();
                    $response['item']['qty'] = $itemQty;

                    $response['product']['price'] = Mage::helper('core')->currency($priceTotal, true, false);
//                $total = $response['cart']['grand_total'];               

                    Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                    );

                    if (!$this->_getSession()->getNoCartRedirect(true)) {
                        if (!$cart->getQuote()->getHasError()) {
                            $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                            $response['message'][] = $message;
                            $items_array['item'][] = $response['product'];
                            $items_array['succes'] = $response['succes'];
                            $items_array['message'] = $response['message'];
                            $items_array['multiple'] = $response['multiple'];
                        }
                    }

                } catch (Mage_Core_Exception $e) {
                    if ($this->_getSession()->getUseNotice(true)) {
                        $response['message'][] = $e->getMessage();
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $response['message'][] = $e->getMessage();
                            $items_array['message'] = $response['message'];
                        }
                    }
                    $response['succes'] = false;
                    $items_array['succes'] = $response['succes'];
                    die(Mage::helper('core')->jsonEncode($response));

                } catch (Exception $e) {
                    $response['succes'] = false;
                    $response['message'][] = $e->getMessage();
                    $response['message'][] = $this->__('Cannot add the item to shopping cart.');
                    $items_array['succes'] = $response['succes'];
                    $items_array['message'] = $response['message'];
                    Mage::logException($e);
                    die(Mage::helper('core')->jsonEncode($response));
                }
            }
        }
        $onlyProducts = Mage::getSingleton('checkout/session')->getQuote()->getItemsCount();
        Mage::getSingleton('core/session')->setMyProducts($onlyProducts);

        $response['cart']['onlyProducts'] = $onlyProducts;
        $response['cart']['totalQty'] = (int)Mage::getModel('checkout/cart')->getQuote()->getItemsQty();
        $response['cart']['allItems'] = Mage::helper('checkout/cart')->getCart()->getItemsCount();

        $response['cart']['grand_total'] = Mage::helper('core')->currency($totalIva + $subtotal, true, false);
        $response['cart']['subTotal'] = Mage::helper('core')->currency($subtotal, true, false);
        $response['cart']['iva'] = Mage::helper('core')->currency($totalIva, true, false);
        $items_array['cart'] = $response['cart'];

        die(Mage::helper('core')->jsonEncode($items_array));
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $country = (string)$this->getRequest()->getParam('country_id');
        $postcode = (string)$this->getRequest()->getParam('estimate_postcode');
        $city = (string)$this->getRequest()->getParam('estimate_city');
        $regionId = (string)$this->getRequest()->getParam('region_id');
        $region = (string)$this->getRequest()->getParam('region');
        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();

        $cart = $this->_getCart();
        $cart->init();
        $cart->save();

        $_helper = Mage::helper('shoppingcart/data');
        $_shippingRateGroups = $this->_getQuote()->getShippingAddress()->getGroupedAllShippingRates();

        $this->getLayout()->getUpdate()->addHandle('checkout_cart_index');
        $this->loadLayout();

        $ship = $this->getLayout()->getBlock('checkout.cart.shipping');
        $ship->setTemplate('checkout/cart/shipping_json.phtml');
        $ship->setEstimateRates($_shippingRateGroups);
        if($ship->toHtml() == '')
            echo $this->__('Sorry, no quotes are available for this order at this time.');
        else 
            echo $ship->toHtml();
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        $isAjax = $this->getRequest()->getParam('ajax', false);
        $response = array(
            'error' => true,
            'message' => '',
            'disable' => false
        );

        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            if (!$isAjax) $this->_goBack();
            else die(json_encode($response));
            return;
        }

        $couponCode = (string)$this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            if (!$isAjax) $this->_goBack();
            else die(json_encode($response));
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    if (!$isAjax) {
                        $this->_getSession()->addSuccess(
                            $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                        );
                    } else {
                        $response['error'] = false;
                        $response['message'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                        $response['disable'] = true;
                    }
                } else {
                    if (!$isAjax) {
                        $this->_getSession()->addError(
                            $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                        );
                    } else {
                        $response['error'] = true;
                        $response['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                    }

                }
            } else {
                if (!$isAjax) {
                    $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
                } else {
                    $response['error'] = false;
                    $response['message'] = $this->__('Coupon code was canceled.');
                }

            }

        } catch (Mage_Core_Exception $e) {
            if (!$isAjax) {
                $this->_getSession()->addError($e->getMessage());
            } else {
                $response['error'] = true;
                $response['message'] = $e->getMessage();
            }
        } catch (Exception $e) {
            if (!$isAjax) {
                $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            } else {
                $response['error'] = true;
                $response['message'] = $this->__('Cannot apply the coupon code.');
            }
            Mage::logException($e);
        }

        if (!$isAjax)
            $this->_goBack();
        else
            die(json_encode($response));
    }

    public function refreshtotalsAction()
    {
        $params = $this->getRequest()->getParams();
        if(!empty($params)){
        	$data = $params['shipping_method'];
	        Mage::getSingleton('checkout/type_onepage')->saveShippingMethod($data);
        }
	    $this->_getQuote()->collectTotals()->save();
	    $this->loadLayout();
	    $this->renderLayout();
    }
    
    public function removeItemAjaxAction()
    {
    	if ($this->_validateFormKey()) {
    		$id = (int)$this->getRequest()->getParam('id');
    		if ($id) {
    			try {
    				$this->_getCart()->removeItem($id)
    				->save();
    				$result['success'] = 1;
    				$result['message'] = $this->__('Item was removed successfully.');
    				
    				$this->loadLayout('myaccount_checkout_cart_layout');
    				$html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
    				$result['html']  = $html;
    				
    				$content = $this->getLayout()
    				->createBlock('checkout/cart_sidebar')
    				->setTemplate('checkout/cart/sidebar.phtml')
    				->toHtml();
    				$result['top_cart'] = $content;
    				
    				$result['top_qty'] = Mage::helper('checkout/cart')->getItemsQty();
    				
    			} catch (Exception $e) {
    				$result['success'] = 0;
    				$result['error'] = $this->__('Can not remove the item.');
    			}
    		}
    	} else {
    		$result['success'] = 0;
    		$result['error'] = $this->__('Can not remove the item.');
    	}
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    
    public function updateQtyAjaxAction()
    {
    	if (!$this->_validateFormKey()) {
    		$this->_redirect('*/*/');
    		return;
    	}
    	try {
    		$cartData = $this->getRequest()->getParam('cart');
    		$cartData = json_decode($cartData,true);
    		if (is_array($cartData)) {
    			$filter = new Zend_Filter_LocalizedToNormalized(
    					array('locale' => Mage::app()->getLocale()->getLocaleCode())
    					);
    			foreach ($cartData as $index => $data) {
    				if (isset($data['qty'])) {
    					$cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
    				}
    			}
    			$cart = $this->_getCart();
    			if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
    				$cart->getQuote()->setCustomerId(null);
    			}
    			
    			$cartData = $cart->suggestItemsQty($cartData);
    			$cart->updateItems($cartData)
    			->save();
    			$result['success'] = 1;
    			$result['message'] = $this->__('Qty Updated successfully.');
    			
    			$content = $this->getLayout()
    				->createBlock('checkout/cart_sidebar')
    				->setTemplate('checkout/cart/sidebar.phtml')
    				->toHtml();
    			$result['top_cart'] = $content;
    			
    			$result['top_qty'] = Mage::helper('checkout/cart')->getItemsQty();
    			
    			$this->loadLayout('myaccount_checkout_cart_layout');
    			$html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
    			$result['html']  = $html;
    			
    		}
    		$this->_getSession()->setCartWasUpdated(true);
    	} catch (Mage_Core_Exception $e) {
    		$result['success'] = 0;
    		$result['message'] = $this->__('Qty not updated.');
    	} catch (Exception $e) {
    		$result['success'] = 0;
    		$result['message'] = $this->__('Qty not updated.');
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    
    public function couponPostAjaxAction()
    {
    	/**
    	 * No reason continue with empty shopping cart
    	 */
    	if (!$this->_getCart()->getQuote()->getItemsCount()) {
    		$this->_goBack();
    		return;
    	}
    	
    	$couponCode = (string) $this->getRequest()->getParam('coupon_code');
    	if ($this->getRequest()->getParam('remove') == 1) {
    		$couponCode = '';
    	}
    	$oldCouponCode = $this->_getQuote()->getCouponCode();
    	
    	if (!strlen($couponCode) && !strlen($oldCouponCode)) {
    		//$this->_goBack();
    		//return;
    	}
    	
    	try {
    		$codeLength = strlen($couponCode);
    		$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
    		
    		$this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
    		$this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
    		->collectTotals()
    		->save();
    		
    		if ($codeLength) {
    			if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
    				$result['success'] = 1;
    				$result['message'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')
    							->escapeHtml($couponCode));
    				$this->_getSession()->setCartCouponCode($couponCode);
    			} else {
    				$result['success'] = 0;
    				$result['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode));
    				$this->_getSession()->setCartCouponCode($couponCode);
    			}
    			
    		} else {
    			$result['success'] = 0;
    			$result['message'] = $this->__('Coupon code was canceled.');
    		}
    		
    		if ($this->getRequest()->getParam('remove') == 1) {
    			$result['success'] = 1;
    		}
    		
    		$this->loadLayout('myaccount_checkout_cart_layout');
    		$html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
    		$result['html']  = $html;
    		
    	} catch (Mage_Core_Exception $e) {
    		$result['success'] = 0;
    		$result['message'] = $this->__('Coupon code not applied.');
    	} catch (Exception $e) {
    		$result['success'] = 0;
    		$result['message'] = $this->__('Cannot apply the coupon code.');
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    }
    
    private function _setSessionVars($card)
    {
    	$oSession = Mage::getSingleton('giftcards/session');
    	
    	$giftCardsIds = $oSession->getGiftCardsIds();
    	
    	//append applied gift card id to gift card session
    	//append applied gift card balance to gift card session
    	if (!empty($giftCardsIds)) {
    		$giftCardsIds = $oSession->getGiftCardsIds();
    		if (!array_key_exists($card->getId(), $giftCardsIds)) {
    			$giftCardsIds[$card->getId()] =  array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
    			$oSession->setGiftCardsIds($giftCardsIds);
    			
    			$newBalance = $oSession->getGiftCardBalance() + $card->getCardBalance();
    			$oSession->setGiftCardBalance($newBalance);
    		}
    	} else {
    		$giftCardsIds[$card->getId()] = array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
    		$oSession->setGiftCardsIds($giftCardsIds);
    		
    		$oSession->setGiftCardBalance($card->getCardBalance());
    	}
    }
    
    public function activateGiftCardAjaxAction()
    {
    	$giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
    	$card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');
    	
    	if ($card->getId() && ($card->getCardStatus() == 1)) {
    		
    		Mage::getSingleton('giftcards/session')->setActive('1');
    		$this->_setSessionVars($card);
    		$this->_getQuote()->collectTotals();
    		$result['message'] =  $this->__('Gift Card used');
    		$result['success'] = 1;
    	}else {
    		$result['success'] = 0;
    		if($card->getId() && ($card->getCardStatus() == 2)) {
    			$result['message'] = $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode));
    		} else {
    			$result['message'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
    		}
    	}
    	
    	$this->loadLayout('myaccount_checkout_cart_layout');
    	$html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
    	$result['html']  = $html;
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function deActivateGiftCardAjaxAction()
    {
    	$oSession = Mage::getSingleton('giftcards/session');
    	$cardId = $this->getRequest()->getParam('id');
    	$cardIds = $oSession->getGiftCardsIds();
    	$sessionBalance = $oSession->getGiftCardBalance();
    	$newSessionBalance = $sessionBalance - $cardIds[$cardId]['balance'];
    	unset($cardIds[$cardId]);
    	if(empty($cardIds))
    	{
    		Mage::getSingleton('giftcards/session')->clear();
    	}
    	$oSession->setGiftCardBalance($newSessionBalance);
    	$oSession->setGiftCardsIds($cardIds);
    	
    	$this->_getQuote()->collectTotals()->save();
    	
    	$result['success'] = 1;
    	$result['message'] = $this->__('Gift card Removed');
    	
    	$this->loadLayout('myaccount_checkout_cart_layout');
    	$html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
    	$result['html']  = $html;
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
}
