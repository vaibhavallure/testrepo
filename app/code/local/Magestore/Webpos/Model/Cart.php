<?php

class Magestore_Webpos_Model_Cart
{
	protected function _getCart(){
		return Mage::getSingleton('checkout/cart');
	}
	
	protected function _getSession(){
		return Mage::getSingleton('checkout/session');
	}
	
	protected function _getQuote(){
		return $this->_getCart()->getQuote();
	}
	
	protected function _initProduct($params){
		$productId = (int)$params['product'];
		if ($productId) {
			$product = Mage::getModel('catalog/product')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($productId);
			if ($product->getId()) return $product;
		}
		return false;
	}
	
	public function catalogProductView($observer){
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'){
			$categoryId = (int)$action->getRequest()->getParam('category',false);
			$productId = (int)$action->getRequest()->getParam('id');
			$specifyOptions = $action->getRequest()->getParam('options');
			
			$viewHelper = Mage::helper('catalog/product_view');
			
			$params = new Varien_Object();
			$params->setCategoryId($categoryId);
			$params->setSpecifyOptions($specifyOptions);
			
			$productHelper = Mage::helper('catalog/product');
			
			$result = array();
			try {
				$product = $productHelper->initProduct($productId,$action,$params);
				if (!$product) {
					$this->_getSession->addError($viewHelper->__('Product is not loaded'));
				}
				Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
				$viewHelper->initProductLayout($product,$action);
				/* Daniel - doesn't allow add out of stock product to cart */
				if(!$product->isAvailable()){
					$result['outofstock'] = true;
				}
				/* end */
			} catch (Exception $e) {
				return $this;
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$params = $action->getRequest()->getParams();
			try {
				$result['hasOptions'] = true;
				if (isset($params['groupmessage']) && $action->getLayout()->getMessagesBlock()){
					$action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
					$action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
					$result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
				} else {
					$this->_getSession()->getMessages(true);
					$this->_getSession()->getEscapeMessages(true);
				}
				if ($typeBlock = Mage::getStoreConfig("webpos/product/{$product->getTypeId()}"))
					$productBlock = $action->getLayout()->createBlock($typeBlock,'ajaxcart_product_view');
				else
					$productBlock = $action->getLayout()->createBlock('webpos/product_view','ajaxcart_product_view');
				/* add all js
				$result['optionjs'] = $productBlock->getJsItems();
				*/
				
				/* Daniel - updated - js from colorselectorplus */
				$optionjss = array();
				/* add js from other extensions - for example: 
				$optionjss[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'cjm/colorselectorplus/colorselected.js';
				$optionjss[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);.'js/colorselectorplus/colorselected.js';
				*/
				
				$result['optionjs'] = $optionjss;
				/* end */
					
				$result['optionhtml'] = $productBlock->toHtml();
			} catch (Exception $e){}
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartAdd($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'){
			$cart = $this->_getCart();
			$params = $action->getRequest()->getParams();
			$result = array();
			try {
				if (isset($params['qty'])){
					$filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
					$params['qty'] = $filter->filter($params['qty']);
				}
				$product = $this->_initProduct($params);
				if(isset($params['related_product']))
				$related = $params['related_product'];
				
				if ($product){
					$cart->addProduct($product,$params);
					if (!empty($related)) $cart->addProductsByIds(explode(',',$related));
					$cart->save();
					
					$this->_getSession()->setCartWasUpdated(true);
					Mage::dispatchEvent('checkout_cart_add_product_complete',array('product' => $product, 'request' => $action->getRequest(), 'response' => $action->getResponse()));
					
					if (!$cart->getQuote()->getHasError()){
						$this->_getSession()->addSuccess(Mage::helper('checkout')->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())));
					}
				} else {
					$this->_getSession()->addError(Mage::helper('checkout')->__('Product not found!'));
				}
			} catch (Mage_Core_Exception $e) {
				$result['hasOptions'] = true;
				/* Daniel - updated - check error */
				if(!isset($params['tempadd']))
					$result['error'] = $e->getMessage();
				/* end */
			} catch (Exception $e){
				$this->_getSession()->addError(Mage::helper('checkout')->__('Cannot add item to shopping cart!'));
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			if (isset($result['hasOptions'])){
				$redirectUrl = Mage::getUrl('catalog/product/view',array(
					'id'				=> $product->getId(),
					'related_product'	=> isset($params['related_product'])?$params['related_product']:""
				));
				$result['redirectUrl'] = $redirectUrl;
			} elseif ( (isset($params['groupmessage']) && $params['groupmessage']) || (isset($params['minicart']) && $params['minicart'])|| (isset($params['webposlinks']) && $params['webposlinks'])|| (isset($params['iswebposcartpage']) && $params['iswebposcartpage'])){
				$action->loadLayout();
				try {
					if (isset($params['minicart']) && $params['minicart'] && $action->getLayout()->getBlock('cart_sidebar')){
						$result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
					}
					if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()){
						$action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
						$action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
						$result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
					} else {
						$this->_getSession()->getMessages(true);
						$this->_getSession()->getEscapeMessages(true);
					}
					if (isset($params['webposlinks']) && $params['webposlinks'] && $action->getLayout()->getBlock('top.links')){
						$result['webposlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
					}
					if (isset($params['iswebposcartpage']) && $params['iswebposcartpage']){
						$result['hasOptions'] = true;
						$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
					}
				} catch (Exception $e){}
			}
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartDelete($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'){
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$params = $action->getRequest()->getParams();
			$result = array();
			if ($id = (int)$action->getRequest()->getParam('id')){
				try {
					$this->_getCart()->removeItem($id)->save();
					$this->_getSession()->addSuccess(Mage::helper('checkout')->__('Item was removed successfully.'));
				} catch (Exception $e) {
					$this->_getSession()->addError(Mage::helper('checkout')->__('Cannot remove the item.'));
				}
			}
			if ((isset($params['groupmessage']) && $params['groupmessage']) || (isset($params['minicart']) && $params['minicart'])  ||  (isset($params['webposlinks']) && $params['webposlinks']) || (isset($params['iswebposcartpage']) && $params['iswebposcartpage']) ){
				$action->loadLayout();
				try {
					if ((isset($params['minicart']) && $params['minicart']) && $action->getLayout()->getBlock('cart_sidebar')){
						$result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
					}
					if ((isset($params['groupmessage']) && $params['groupmessage']) && $action->getLayout()->getMessagesBlock()){
						$action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
						$action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
						$result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
					} else {
						$this->_getSession()->getMessages(true);
						$this->_getSession()->getEscapeMessages(true);
					}
					if ( (isset($params['webposlinks']) && $params['webposlinks'])&& $action->getLayout()->getBlock('top.links')){
						$result['webposlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
					}
					if ((isset($params['iswebposcartpage']) && $params['iswebposcartpage'])){
						$result['hasOptions'] = true;
						if(count($this->_getQuote()->getAllItems()) <= 0)
							$result['redirectUrl'] = Mage::getUrl('webpos/index/index');
						else
							$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
					}
				} catch (Exception $e){}
			}
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartConfigure($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'){
			$id = (int)$action->getRequest()->getParam('id');
			$quoteItem = null;
			$cart = $this->_getCart();
			if ($id) $quoteItem = $cart->getQuote()->getItemById($id);
			
			$result = array();
			if (!$quoteItem){
				$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
				return $action->getResponse()->setBody('');
			}
			$viewHelper = Mage::helper('catalog/product_view');
			
			$params = new Varien_Object();
			$params->setCategoryId(false);
			$params->setConfigureMode(true);
			$params->setBuyRequest($quoteItem->getBuyRequest());
			
			$productHelper = Mage::helper('catalog/product');
			$productId = $quoteItem->getProduct()->getId();
			try {
				$product = $productHelper->initProduct($productId,$action,$params);
				if (!$product){
					$this->_getSession()->addError($viewHelper->__('Product is not loaded'));
				} else {
					if ($buyRequest = $params->getBuyRequest())
						$productHelper->prepareProductOptions($product,$buyRequest);
					$product->setConfigureMode(true);
					Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
					$viewHelper->initProductLayout($product,$action);
					$result['hasOptions'] = true;
					/* Daniel - doesn't allow add out of stock product to cart */
					if(!$product->isAvailable()){
						$result['outofstock'] = true;
					}
					/* end */
				}
			} catch (Exception $e){
				$this->_getSession()->addError(Mage::helper('checkout')->__('Cannot configure product.'));
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$params = $action->getRequest()->getParams();
			try {
				if (isset($params['groupmessage']) && $action->getLayout()->getMessagesBlock()){
					$action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
					$action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
					$result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
				} else {
					$this->_getSession()->getMessages(true);
					$this->_getSession()->getEscapeMessages(true);
				}
				if (isset($result['hasOptions'])){
					if ($typeBlock = Mage::getStoreConfig("webpos/product/{$product->getTypeId()}"))
						$productBlock = $action->getLayout()->createBlock($typeBlock,'ajaxcart_product_view');
					else
						$productBlock = $action->getLayout()->createBlock('webpos/product_view','ajaxcart_product_view');
					$productBlock->setData('submit_route_data',array(
						'route' => 'checkout/cart/updateItemOptions',
						'params'	=> array('id' => $id),
					));
					/* add all js
					$result['optionjs'] = $productBlock->getJsItems();
					*/
					
					/* Daniel - updated - js from colorselectorplus */
					$optionjss = array();
					/* add js from other extensions - for example: 
					$optionjss[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'cjm/colorselectorplus/colorselected.js';
					$optionjss[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);.'js/colorselectorplus/colorselected.js';
					*/
					
					$result['optionjs'] = $optionjss;
					/* end */
					
					$result['optionhtml'] = $productBlock->toHtml();
				}
			} catch (Exception $e){}
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartUpdateItemOptions($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'){
			$cart = $this->_getCart();
			$id = (int)$action->getRequest()->getParam('id');
			$params = $action->getRequest()->getParams();
			$result = array();
			if (!isset($params['options'])) $params['options'] = array();
			try {
				if (isset($params['qty'])){
					$filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
					$params['qty'] = $filter->filter($params['qty']);
				}
				$quoteItem = $cart->getQuote()->getItemById($id);
				if ($quoteItem){
					$item = $cart->updateItem($id,new Varien_Object($params));
					if (is_string($item)){
						$this->_getSession()->addError($item);
					} elseif ($item->getHasError()){
						$this->_getSession()->addError($item->getMessage());
					} else {
						$related = $action->getRequest()->getParam('related_product');
						if (!empty($related)) $cart->addProductsByIds(explode(',',$related));
						$cart->save();
						$this->_getSession()->setCartWasUpdated(true);
						Mage::dispatchEvent('checkout_cart_update_item_complete',array('item' => $item, 'request' => $action->getRequest(), 'response' => $action->getResponse()));
						if (!$cart->getQuote()->getHasError()){
							$message = Mage::helper('checkout')->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
							$this->_getSession()->addSuccess($message);
						}
					}
				} else {
					$this->_getSession()->addError(Mage::helper('checkout')->__('Quote item is not found.'));
				}
			} catch (Mage_Core_Exception $e) {
				if ($this->_getSession()->getUseNotice(true)){
					$this->_getSession()->addNotice($e->getMessage());
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message)
						$this->_getSession()->addError($message);
				}
			} catch (Exception $e){
				$this->_getSession()->addError(Mage::helper('checkout')->__('Cannot update the item.'));
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			if ($params['groupmessage'] || $params['minicart'] || $params['webposlinks'] || $params['iswebposcartpage']){
				$action->loadLayout();
				try {
					if ($params['minicart'] && $action->getLayout()->getBlock('cart_sidebar')){
						$result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
					}
					if ($params['groupmessage'] && $action->getLayout()->getMessagesBlock()){
						$action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
						$action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
						$result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
					} else {
						$this->_getSession()->getMessages(true);
						$this->_getSession()->getEscapeMessages(true);
					}
					if ($params['webposlinks'] && $action->getLayout()->getBlock('top.links')){
						$result['webposlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
					}
					if ($params['iswebposcartpage']){
						$result['hasOptions'] = true;
						$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
					}
				} catch (Exception $e){}
			}
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartIndex($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'
			&& $action->getRequest()->getParam('iswebposcartpage') == 1){
			$cart = $this->_getCart();
			if ($cart->getQuote()->getItemsCount()){
				$cart->init();
				$cart->save();
				if (!$this->_getQuote()->validateMinimumAmount()){
					$warning = Mage::getStoreConfig('sales/minimum_order/description');
					$this->_getSession()->addNotice($warning);
				}
			}
			$messages = array();
			foreach ($cart->getQuote()->getMessages() as $message)
				if ($message instanceof Mage_Core_Model_Message_Abstract)
					$this->_getSession()->addMessage($message);
			$this->_getSession()->setCartWasUpdated(true);
			
			$action->loadLayout();
			if ($action->getLayout()->getMessagesBlock()){
				$action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
				$action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			
			$result = array();
			if ($action->getRequest()->getParam('webposlinks') && $action->getLayout()->getBlock('top.links'))
				$result['webposlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
			if ($cartBlock = $action->getLayout()->getBlock('checkout.cart')){
                            if(Mage::helper('webpos')->showEarningPointsCart()){
                                $template = 'webpos/admin/webpos/checkout/cart/item/default.phtml';
                                $nameModule = 'rewardpointsrule';
                            }
                            else{
                                $template = 'checkout/cart/item/default.phtml';
                                $nameModule = 'checkout';
                            }
                            $cartHtml = $cartBlock
                                    ->setTemplate('webpos/admin/webpos/checkout/cart.phtml')
                                    ->addItemRender('default', $nameModule.'/cart_item_renderer', $template)
                                    ->addItemRender('simple', $nameModule.'/cart_item_renderer', $template)
                                    ->addItemRender('grouped', $nameModule.'/cart_item_renderer_grouped', $template)
                                    ->addItemRender('configurable', $nameModule.'/cart_item_renderer_configurable', $template)
                                    ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', $template)
                                    /* Daniel - add downloadable item renderer */
									->addItemRender('downloadable','downloadable/checkout_cart_item_renderer', 'downloadable/checkout/cart/item/default.phtml')
                                    /* end */
									->toHtml();
                            
                            $result['cartPage'] = $cartHtml;
			}
			if (!$cart->getQuote()->getItemsCount())
				$result['emptyCart'] = true;
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartUpdatePost($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'
			&& $action->getRequest()->getParam('iswebposcartpage') == 1){
			try {
				$cartData = $action->getRequest()->getParam('cart');
				if (is_array($cartData)){
					$filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
					foreach ($cartData as $index => $data)
						if (isset($data['qty']))
							$cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
					$cart = $this->_getCart();
					if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId())
						$cart->getQuote()->setCustomerId(null);
					$cartData = $cart->suggestItemsQty($cartData);
					$cart->updateItems($cartData)->save();
					$this->_getSession()->setCartWasUpdated(true);
				}
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			} catch (Exception $e){
				$this->_getSession()->addError(Mage::helper('checkout')->__('Cannot update shopping cart.'));
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$result = array();
			$result['hasOptions'] = true;
			if(count($this->_getQuote()->getAllItems()) <= 0)
				$result['redirectUrl'] = Mage::getUrl('webpos/index/index');
			else
				$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartEstimateUpdatePost($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'
			&& $action->getRequest()->getParam('iswebposcartpage') == 1){
			$code = (string)$action->getRequest()->getParam('estimate_method');
			if (!empty($code))
				$this->_getQuote()->getShippingAddress()->setShippingMethod($code)->save();
			
			$result = array();
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$result['hasOptions'] = true;
			$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartCouponPost($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'
			&& $action->getRequest()->getParam('iswebposcartpage') == 1
			&& $this->_getCart()->getQuote()->getItemsCount()){
			$couponCode = (string)$action->getRequest()->getParam('coupon_code');
			if ($action->getRequest()->getParam('remove') == 1) $couponCode = '';
			$oldCouponCode = $this->_getQuote()->getCouponCode();
			$result = array();
			if (strlen($couponCode) || strlen($oldCouponCode)){
				try {
					$this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
					$this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')->collectTotals()->save();
					if ($couponCode){
						if ($couponCode == $this->_getQuote()->getCouponCode())
							$this->_getSession()->addSuccess(Mage::helper('checkout')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
						else
							$this->_getSession()->addError(Mage::helper('checkout')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));
					} else {
						$this->_getSession()->addSuccess(Mage::helper('checkout')->__('Coupon code was canceled.'));
					}
				} catch (Mage_Core_Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				} catch (Exception $e){
					$this->_getSession()->addError(Mage::helper('checkout')->__('Cannot apply the coupon code.'));
				}
				$result['hasOptions'] = true;
				$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
			} else{
				$result['nothing'] = true;
			}
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	public function checkoutCartEstimatePost($observer){
		
		$action = $observer->getEvent()->getControllerAction();
		if ($action->getRequest()->getParam('iswebposcart') == 'true'
			&& $action->getRequest()->getParam('iswebposcartpage') == 1){
			$country 	= (string)$action->getRequest()->getParam('country_id');
			$postcode 	= (string)$action->getRequest()->getParam('estimate_postcode');
			$city 		= (string)$action->getRequest()->getParam('estimate_city');
			$regionId 	= (string)$action->getRequest()->getParam('region_id');
			$region		= (string)$action->getRequest()->getParam('region');
			
			$this->_getQuote()->getShippingAddress()
				->setCountryId($country)
				->setCity($city)
				->setPostcode($postcode)
				->setRegionId($regionId)
				->setRegion($region)
				->setCollectShippingRates(true);
			$this->_getQuote()->save();
			
			$result = array();
			$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
			$result['hasOptions'] = true;
			$result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
			$action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
}
