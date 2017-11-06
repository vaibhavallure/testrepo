<?php
include_once("Mage/Wishlist/controllers/IndexController.php");
class Allure_MyAccount_Wishlist_IndexController extends Mage_Wishlist_IndexController{
	public function updateItemAjaxAction(){
		if (!$this->_validateFormKey()) {
			$this->_redirect('*/*/');
			return;
		}
		
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
			return ;
		}
		
		try {
			$updatedItems = 0;
			
			$cartData = $this->getRequest()->getParam('cart');
			$cartData = json_decode($cartData,true);
			if (is_array($cartData)) {
				foreach ($cartData as $index => $data) {
					$item = Mage::getModel('wishlist/item')->load($index);
					if ($item->getWishlistId() != $wishlist->getId()) {
						continue;
					}
					
					$qty = null;
					if (isset($data['qty'])) {
						$qty = $this->_processLocalizedQty($data['qty']);
					}
					
					if (is_null($qty)) {
						$qty = $item->getQty();
						if (!$qty) {
							$qty = 1;
						}
					} elseif (0 == $qty) {
						try {
							$item->delete();
						} catch (Exception $e) {
							Mage::logException($e);
						}
					}
					
					$description = '';
					if (isset($data['description'])) {
						$description= $data['description'];
					}
					
					if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
						continue;
					}
					
					//if(!empty($description))
						$item->setDescription($description);
					$item->setQty($qty)
						->save();
					$updatedItems++;
				}
				if ($updatedItems) {
					$wishlist->save();
					Mage::helper('wishlist')->calculate();
				}
				$result['success'] = 1;
				$result['message'] = $this->__('Qty updated.');
			}else{
				$result['success'] = 1;
				$result['message'] = $this->__('Qty not updated.');
			}
			
			$this->loadLayout('myaccount_wishlist_layout');
			$html = $this->getLayout()->getBlock('customer.wishlist_myaccount')->toHtml();
			$result['html']  = $html;
		} catch (Mage_Core_Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Qty not updated.');
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Qty not updated.');
			$result['error'] = $e->getMessage();
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
	
	public function updateAllItemAjaxAction(){
		
		
		if (!$this->_validateFormKey()) {
			$this->_redirect('*/*/');
			return;
		}
		
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
			return ;
		}
		
		try {
			$updatedItems = 0;
			$params = $this->getRequest()->getParams();
			$cartData = $params['cart'];
			//$cartData = json_decode($cartData,true);
			if (is_array($cartData)) {
				foreach ($cartData as $index => $data) {
					$item = Mage::getModel('wishlist/item')->load($index);
					if ($item->getWishlistId() != $wishlist->getId()) {
						continue;
					}
					
					$qty = null;
					if (isset($data['qty'])) {
						$qty = $this->_processLocalizedQty($data['qty']);
					}
					
					if (is_null($qty)) {
						$qty = $item->getQty();
						if (!$qty) {
							$qty = 1;
						}
					} elseif (0 == $qty) {
						try {
							$item->delete();
						} catch (Exception $e) {
							Mage::logException($e);
						}
					}
					
					$description = '';
					if (isset($params['description'][$index])) {
						$description= $params['description'][$index];
					}
					
					if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
						continue;
					}
					
					if(!empty($description))
						$item->setDescription($description);
						$item->setQty($qty)
						->save();
						$updatedItems++;
				}
				if ($updatedItems) {
					$wishlist->save();
					Mage::helper('wishlist')->calculate();
				}
				$result['success'] = 1;
				$result['message'] = $this->__('Qty updated.');
			}else{
				$result['success'] = 1;
				$result['message'] = $this->__('Qty not updated.');
			}
			
			$this->loadLayout('myaccount_wishlist_layout');
			$html = $this->getLayout()->getBlock('customer.wishlist_myaccount')->toHtml();
			$result['html']  = $html;
		} catch (Mage_Core_Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Qty not updated.');
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Qty not updated.');
			$result['error'] = $e->getMessage();
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
	public function addAllCartAjaxAction()
	{
	
		$params = $this->getRequest()->getParams();
		$cartData = $params['cart'];
		
		if (!$this->_validateFormKey()) {
			return $this->_redirect('*/*');
		}
		
		/* @var $item Mage_Wishlist_Model_Item */
		
		
		/* @var $session Mage_Wishlist_Model_Session */
		$session    = Mage::getSingleton('wishlist/session');
		
		$cart       = Mage::getSingleton('checkout/cart');
		
		$messages   = array();
		$addedItems = array();
		$notSalable = array();
		$hasOptions = array();
		
		$wishlist   = $this->_getWishlist();
		if (!$wishlist) {
			$this->_forward('noRoute');
			return;
		}
		
		$isOwner    = $wishlist->isOwner(Mage::getSingleton('customer/session')->getCustomerId());
		
		$collection = $wishlist->getItemCollection()
		->setVisibilityFilter();
		
		try {
			$addCnt = 0;
			foreach ($collection as $item) {
				/** @var Mage_Wishlist_Model_Item */
				try {
					$disableAddToCart = $item->getProduct()->getDisableAddToCart();
					$item->unsProduct();
					
					// Set qty
					if (isset($cartData[$item->getId()]['qty'])) {
						$qty = $this->_processLocalizedQty($cartData[$item->getId()]['qty']);
						if ($qty) {
							$item->setQty($qty);
						}
					}
					$item->getProduct()->setDisableAddToCart($disableAddToCart);
					// Add to cart
					if ($item->addToCart($cart, $isOwner)) {
						$addedItems[] = $item->getProduct();
						$addCnt++;
					}
					
				} catch (Mage_Core_Exception $e) {
					if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
						$notSalable[] = $item;
					} else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
						$hasOptions[] = $item;
					} else {
						$messages[] = $this->__('%s for "%s".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
					}
					
					$cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
					if ($cartItem) {
						$cart->getQuote()->deleteItem($cartItem);
					}
					$message =  Mage::helper('wishlist')->__('Cannot add the item to shopping cart.');
				} catch (Exception $e) {
					$messages[] = Mage::helper('wishlist')->__('Cannot add the item to shopping cart.');
					$message =  Mage::helper('wishlist')->__('Cannot add the item to shopping cart.');
				}
			}
			
			
			if($addCnt > 0){
				$message = $this->__('All Items added to cart');
				try {
					$wishlist->save();
					Mage::helper('wishlist')->calculate();
					$result['success'] = 1;
					$result['message'] = $message;
				}
				catch (Exception $e) {
					$result['success'] = 0;
					$message = $this->__('Cannot update wishlist');
				}
			}else{
				$result['success'] = 0;
				$message = $this->__('Items not added to your shopping cart.');
				$result['message'] = $message;
			}
			
		} catch (Mage_Core_Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Cannot add item to shopping cart');
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Cannot add item to shopping cart');
			$result['error'] = $e->getMessage();
		}
		
		
		if ($notSalable) {
			$products = array();
			foreach ($notSalable as $item) {
				$products[] = '"' . $item->getProduct()->getName() . '"';
			}
			$result['success'] = 0;
			$message = Mage::helper('wishlist')->__('Unable to add the following product(s) to shopping cart: %s.', join(', ', $products));
		}
		
		if ($hasOptions) {
			$products = array();
			foreach ($hasOptions as $item) {
				$products[] = '"' . $item->getProduct()->getName() . '"';
			}
			$result['success'] = 0;
			$message =  Mage::helper('wishlist')->__('Product(s) %s have required options. Each of them can be added to cart separately only.', join(', ', $products));
		}
		
		if ($messages) {
			$isMessageSole = (count($messages) == 1);
			if ($isMessageSole && count($hasOptions) == 1) {
				$item = $hasOptions[0];
				if ($isOwner) {
					$item->delete();
				}
				$redirectUrl = $item->getProductUrl();
			} else {
				$wishlistSession = Mage::getSingleton('wishlist/session');
				foreach ($messages as $message) {
					$wishlistSession->addError($message);
				}
				$redirectUrl = $indexUrl;
			}
		}
		
		if ($addedItems) {
			// save wishlist model for setting date of last update
			try {
				$wishlist->save();
			}
			catch (Exception $e) {
				Mage::getSingleton('wishlist/session')->addError($this->__('Cannot update wishlist'));
				$redirectUrl = $indexUrl;
			}
			
			$products = array();
			foreach ($addedItems as $product) {
				$products[] = '"' . $product->getName() . '"';
			}
			
			// save cart and collect totals
			$cart->save()->getQuote()->collectTotals();
			
			$this->loadLayout('myaccount_wishlist_layout');
			$html = $this->getLayout()->getBlock('customer.wishlist_myaccount')->toHtml();
			$result['html']  = $html;
			
			$content = $this->getLayout()
			->createBlock('checkout/cart_sidebar')
			->setTemplate('checkout/cart/sidebar.phtml')
			->toHtml();
			$result['top_cart'] = $content;
			
			$result['top_qty'] = Mage::helper('checkout/cart')->getSummaryCount();
			
			$this->loadLayout('myaccount_checkout_cart_layout');
			$cart_html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
			
			$result['cart_html'] = $cart_html;
			
			
			$result['success'] = 1;
			$result['message'] = Mage::helper('wishlist')->__('%d product(s) have been added to shopping cart: %s.', count($addedItems), join(', ', $products));
			
		}
		
		Mage::helper('wishlist')->calculate();
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
	public function addCartAjaxAction()
	{
		if (!$this->_validateFormKey()) {
			return $this->_redirect('*/*');
		}
		$itemId = (int) $this->getRequest()->getParam('item');
		
		/* @var $item Mage_Wishlist_Model_Item */
		$item = Mage::getModel('wishlist/item')->load($itemId);
		if (!$item->getId()) {
			return ;
		}
		$wishlist = $this->_getWishlist($item->getWishlistId());
		if (!$wishlist) {
			return ;
		}
		
		// Set qty
		$qty = $this->getRequest()->getParam('qty');
		if (is_array($qty)) {
			if (isset($qty[$itemId])) {
				$qty = $qty[$itemId];
			} else {
				$qty = 1;
			}
		}
		$qty = $this->_processLocalizedQty($qty);
		if ($qty) {
			$item->setQty($qty);
		}
		
		/* @var $session Mage_Wishlist_Model_Session */
		$session    = Mage::getSingleton('wishlist/session');
		$cart       = Mage::getSingleton('checkout/cart');
		
		$redirectUrl = Mage::getUrl('*/*');
		
		try {
			$options = Mage::getModel('wishlist/item_option')->getCollection()
			->addItemFilter(array($itemId));
			$item->setOptions($options->getOptionsByItem($itemId));
			
			$buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
					$this->getRequest()->getParams(),
					array('current_config' => $item->getBuyRequest())
					);
			
			$isadd = false;
			
			$item->mergeBuyRequest($buyRequest);
			if ($item->addToCart($cart, true)) {
				$cart->save()->getQuote()->collectTotals();
				$isadd = true;
			}else{//realted to counterpoint
			    $productId = $item->getProductId();
			    if($productId){
			        $product = Mage::getModel('catalog/product')
			             ->setStoreId(Mage::app()->getStore()->getId())
			             ->load($item->getProductId());
			        $params = array();
			        $params['qty'] = $item->getQty();
			        $cart->addProduct($product, $params);
			        $cart->save()->getQuote()->collectTotals();
			        $isadd = true;
			        $item->delete();
			    }
			}
			
			$wishlist->save();
			Mage::helper('wishlist')->calculate();
			
			if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
				$redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
			}
			Mage::helper('wishlist')->calculate();
			
			$productName = Mage::helper('core')->escapeHtml($item->getName());
			
			if($isadd){
				$message = $this->__('%s was added to your shopping cart.', $productName);
				
				$result['success'] = 1;
				$result['message'] = $message;
				
				$this->loadLayout('myaccount_wishlist_layout');
				$html = $this->getLayout()->getBlock('customer.wishlist_myaccount')->toHtml();
				$result['html']  = $html;
				
				$content = $this->getLayout()
				->createBlock('checkout/cart_sidebar')
				->setTemplate('checkout/cart/sidebar.phtml')
				->toHtml();
				$result['top_cart'] = $content;
				
				$result['top_qty'] = Mage::helper('checkout/cart')->getItemsQty();
				
				//$this->loadLayout('myaccount_checkout_cart_layout');
				$cart_html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
				
				$result['cart_html'] = $cart_html;
			}else{
				$result['success'] = 0;
				$message = $this->__('%s was not added to your shopping cart.', $productName);
				$result['message'] = $message;
			}
			
		} catch (Mage_Core_Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Cannot add item to shopping cart');
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('Cannot add item to shopping cart');
			$result['error'] = $e->getMessage();
		}
		
		Mage::helper('wishlist')->calculate();
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
	public function removeAjaxAction()
	{
		if (!$this->_validateFormKey()) {
			return $this->_redirect('*/*');
		}
		$id = (int) $this->getRequest()->getParam('item');
		$item = Mage::getModel('wishlist/item')->load($id);
		if (!$item->getId()) {
			return $this->norouteAction();
		}
		$wishlist = $this->_getWishlist($item->getWishlistId());
		if (!$wishlist) {
			return $this->norouteAction();
		}
		try {
			$item->delete();
			$wishlist->save();
			
			$result['success'] = 1;
			$result['message'] = $this->__("Wishlist Item Removed Successfully");
			
			$this->loadLayout('myaccount_wishlist_layout');
			$html = $this->getLayout()->getBlock('customer.wishlist_myaccount')->toHtml();
			$result['html']  = $html;
			
		} catch (Mage_Core_Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage());
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			$result['success'] = 0;
			$result['message'] = $this->__('An error occurred while deleting the item from wishlist.');
			$result['error'] = $e->getMessage();
		}
		
		Mage::helper('wishlist')->calculate();
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
	/**
	 * Add cart item to wishlist and remove from cart
	 */
	public function fromcartAjaxAction()
	{
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
			return ;//$this->norouteAction();
		}
		$itemId = (int) $this->getRequest()->getParam('item');
		
		/* @var Mage_Checkout_Model_Cart $cart */
		$cart = Mage::getSingleton('checkout/cart');
		$session = Mage::getSingleton('checkout/session');
		
		try {
			$item = $cart->getQuote()->getItemById($itemId);
				if (!$item) {
					$result['success'] = 0;
					$result['message'] = Mage::helper('wishlist')->__("Requested cart item doesn't exist");
				}else{
				
					$productId  = $item->getProductId();
					$buyRequest = $item->getBuyRequest();
					
					$wishlist->addNewItem($productId, $buyRequest);
					
					$productIds[] = $productId;
					$cart->getQuote()->removeItem($itemId);
					$cart->save();
					Mage::helper('wishlist')->calculate();
					$productName = Mage::helper('core')->escapeHtml($item->getProduct()->getName());
					$wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());
					
					$wishlist->save();
					
					$result['success'] = 1;
					$result['message'] = Mage::helper('wishlist')->__("%s has been moved to wishlist %s", $productName, $wishlistName);
					
					$this->loadLayout('myaccount_wishlist_layout');
					$html = $this->getLayout()->getBlock('customer.wishlist_myaccount')->toHtml();
					$result['html']  = $html;
					
					$content = $this->getLayout()
					->createBlock('checkout/cart_sidebar')
					->setTemplate('checkout/cart/sidebar.phtml')
					->toHtml();
					$result['top_cart'] = $content;
					
					$result['top_qty'] = Mage::helper('checkout/cart')->getItemsQty();
					
					
					//$this->loadLayout('myaccount_checkout_cart_layout');
					$cart_html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
					
					$result['cart_html'] = $cart_html;
					
			}
			
		} catch (Mage_Core_Exception $e) {
			$result['success'] = 0;
			$result['message'] = $e->getMessage();
		} catch (Exception $e) {
			$result['success'] = 0;
			$result['message'] = Mage::helper('wishlist')->__('Cannot move item to wishlist');
		}
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		
	}
	
	
	public function sendAjaxAction()
	{
		if (!$this->_validateFormKey()) {
			return $this->_redirect('*/*/');
		}
		
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
			return ;//$this->norouteAction();
		}
		
		$emails  = explode(',', $this->getRequest()->getPost('emails'));
		$message = nl2br(htmlspecialchars((string) $this->getRequest()->getPost('message')));
		Mage::log($emails,Zend_log::DEBUG,'abc',true);
		$error   = false;
		if (empty($emails)) {
			$error = $this->__('Email address can\'t be empty.');
		}
		else {
			foreach ($emails as $index => $email) {
				$email = trim($email);
				if (!Zend_Validate::is($email, 'EmailAddress')) {
					$error = $this->__('Please input a valid email address.');
					break;
				}
				$emails[$index] = $email;
			}
		}
		if ($error) {
			$result['success'] = 0;
			$result['message'] = $error;
		}else{
		
			$translate = Mage::getSingleton('core/translate');
			/* @var $translate Mage_Core_Model_Translate */
			$translate->setTranslateInline(false);
			
			try {
				$customer = Mage::getSingleton('customer/session')->getCustomer();
				
				/*if share rss added rss feed to email template*/
				if ($this->getRequest()->getParam('rss_url')) {
					$rss_url = $this->getLayout()
					->createBlock('wishlist/share_email_rss')
					->setWishlistId($wishlist->getId())
					->toHtml();
					$message .= $rss_url;
				}
				$wishlistBlock = $this->getLayout()->createBlock('wishlist/share_email_items')->toHtml();
				
				$emails = array_unique($emails);
				/* @var $emailModel Mage_Core_Model_Email_Template */
				$emailModel = Mage::getModel('core/email_template');
				
				$sharingCode = $wishlist->getSharingCode();
				foreach ($emails as $email) {
					$emailModel->sendTransactional(
							Mage::getStoreConfig('wishlist/email/email_template'),
							Mage::getStoreConfig('wishlist/email/email_identity'),
							$email,
							null,
							array(
									'customer'       => $customer,
									'salable'        => $wishlist->isSalable() ? 'yes' : '',
									'items'          => $wishlistBlock,
									'addAllLink'     => Mage::getUrl('*/shared/allcart', array('code' => $sharingCode)),
									'viewOnSiteLink' => Mage::getUrl('*/shared/index', array('code' => $sharingCode)),
									'message'        => $message
							)
							);
				}
				
				$wishlist->setShared(1);
				$wishlist->save();
				
				$translate->setTranslateInline(true);
				
				Mage::dispatchEvent('wishlist_share', array('wishlist' => $wishlist));
				$result['success'] = 1;
				$result['message'] = $this->__('Your Wishlist has been shared.');
				
				//Mage::getSingleton('customer/session')->addSuccess(
				//		$this->__('Your Wishlist has been shared.')
				//		);
				//$this->_redirect('*/*', array('wishlist_id' => $wishlist->getId()));
			}
			catch (Exception $e) {
				$translate->setTranslateInline(true);
				
				$result['success'] = 0;
				$result['message'] = $e->getMessage();
				
				//Mage::getSingleton('wishlist/session')->addError($e->getMessage());
				//Mage::getSingleton('wishlist/session')->setSharingForm($this->getRequest()->getPost());
				//$this->_redirect('*/*/share');
			}
		}
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
}
