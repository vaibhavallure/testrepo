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
			
			/* $item->mergeBuyRequest($buyRequest);
			if ($item->addToCart($cart, true)) {
				$cart->save()->getQuote()->collectTotals();
				$isadd = true;
			} */
			
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
			
			$wishlist->save();
			Mage::helper('wishlist')->calculate();
			
			if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
				$redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
			}
			Mage::helper('wishlist')->calculate();
			
			$productName = Mage::helper('core')->escapeHtml($product->getName());
			
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
				
				$this->loadLayout('myaccount_checkout_cart_layout');
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
	
}
