<?php
require_once 'Mage/Sales/controllers/OrderController.php';
class Ecp_Sales_OrderController extends Mage_Sales_OrderController
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {       
        $action = $this->getRequest()->getActionName(); 
        if($action == 'print') return $this;
        parent::preDispatch();      
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }        
        
    }
    protected function _canViewOrder($order)
    {
        $action = $this->getRequest()->getActionName(); 
        if($action == 'print'){
            return true;
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }
    /**
     * Print Order Action
     */
    public function printAction()
    {       
        if (!$this->_loadValidOrder()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }
    
    
    /**
     * Action for reorder add item to cart
     */
    public function reorderItemAjaxAction()
    {
    	$item_id = $this->getRequest()->getParam('item_id');
    	$cart = Mage::getSingleton('checkout/cart');
    	$cartTruncated = false;
    	/* @var $cart Mage_Checkout_Model_Cart */
    	
    	try {
    		$result = array();
    		$message = "";
    		$isAdd = false;
    		if(!empty($item_id)){
    			$item = Mage::getModel("sales/order_item")->load($item_id);
    			if($item->getId()){
    				$productId = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
    				if($productId){
    					$productObj = Mage::getModel('catalog/product')->load($productId);
    					//$cart->addOrderItem($item);
    					$params = array();
    					$params['qty'] = 1;
    					$cart->addProduct($productObj, $params);
    					$cart->save()->getQuote()->collectTotals();
    					$isAdd = true;
    					$message =  $productObj->getName().' add into your shopping cart.';
    				}else{
    					$message = 'Cannot add the item to shopping cart.Item not available';
    				}
    			}else{
    				$message = 'Cannot add the item to shopping cart.';
    			}
    		}else{
    			$message = 'Cannot add the item to shopping cart.';
    		}
    		
    		if($isAdd){
    			$result['success'] = 1;
    			$result['message'] = $message;
    			
    			$content = $this->getLayout()
    				->createBlock('checkout/cart_sidebar')
    				->setTemplate('checkout/cart/sidebar.phtml')
    				->toHtml();
    			$result['top_cart'] = $content;
    			
    			$this->loadLayout('myaccount_checkout_cart_layout');
    			$html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
    			
    			$result['cart_html'] = $html;
    			
    			$result['top_qty'] = Mage::helper('checkout/cart')->getSummaryCount();
    		}else{
    			$result['success'] = 0;
    			$result['message'] = $message;
    		}
    		
    	} catch (Mage_Core_Exception $e){
    		$result['success'] = 0;
    		$result['message'] = $e->getMessage();
    	} catch (Exception $e) {
    		$result['success'] = 0;
    		$result['message'] = $e->getMessage();
    	}
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    }
    
    
    public function reviewAction()
    {
    	$this->_viewAction();
    }
    
}
