<?php
class Allure_SmartAnalytics_Model_Observer
{
	private $_cid;
	private $_utmz;
	private $_cn;
	private $_cs;
	private $_cm;
	private $_cc;
	private $_ck;
	private $_gclid;
	private $_domainHost;
	private $_debug;
	private $_helper;

	public function __construct()
	{
		$this->_helper = Mage::helper('allure_smartanalytics');
		if(isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] != '')):
			$this->_domainHost = $_SERVER['SERVER_NAME'];
		else:
			$replace = array('http://','https://');
			$this->_domainHost = rtrim(str_replace($replace, '', Mage::getStoreConfig('web/unsecure/base_url')), '/');
		endif;
		$this->_debug = $this->_helper->getDebugging();

		if (isset($_COOKIE['_ga'])){
			$this->_cid = $this->gaParseCookie($_COOKIE['_ga']);
		}
		if (isset($_COOKIE["__utma"]) and isset($_COOKIE["__utmz"])) {
			$this->_utmz = $_COOKIE["__utmz"];
		}
		if (isset($_COOKIE["utmz"])) {
			$this->_utmz = $this->_cid.".1.1.".str_replace('"','',$_COOKIE["utmz"]);
			$this->gaParseTSCookie($this->_utmz);
		}
	}

	/**
     * Order Cancellation Event which sends -negative transaction to google for removing the original transaction
     *
     * @return void
    */
	public function orderCancelAfter(Varien_Event_Observer $observer)
	{
		$order=$observer->getEvent()->getOrder();
		$order = Mage::getModel("sales/order")->load($order->getId());
		$storeId = $order->getStoreId();
		if ($order->getSentDataToGoogle()==1 && $this->_helper->isSendOrderCancellationToGA($storeId) && $this->isAdmin()){
			$this->buildData($order, $storeId, null, null, true);
		}
	}

	/**
     * Store category data against quote item table
     *
     * @return void
    */
	public function salesQuoteItemSetGoogleCategory($observer)
	{
		$quoteItem = $observer->getQuoteItem();
		$product = $observer->getProduct();
	    $category = $quoteItem->getGoogleCategory();

		if (!isset($category)){
			$cookie = Mage::getSingleton('core/cookie');
			$category = str_replace('"','',$cookie->get("googlecategory"));

			if (!isset($category) || strlen($category)==0){
				$category = $this->_helper->getProductCategoryName($product);
			}
			$quoteItem->setGoogleCategory($category);
		}
	}

	/**
     * Store data in cookie for every add to basket product so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function addProductCookie(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
		$quoteItem = $event->getQuoteItem();

		$cookie = Mage::getSingleton('core/cookie');
	    $category = str_replace('"','',$cookie->get("googlecategory"));

		if (!isset($category)){
			$category = $this->_helper->getProductCategoryName($product);
		}

		$productToBasket = array(
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'category' => $category,
            'brand' => $this->_helper->getBrand($product),
            'variant' => $this->_helper->getVariantProperty($product),
            'price' => $product->getFinalPrice(),
            'qty' => $quoteItem->getQty()
        );

		Mage::getModel('core/session')->setProductToBasket(json_encode($productToBasket));

    }

	/**
     * Store data in cookie for every remove from basket product so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function removeProductCookie(Varien_Event_Observer $observer)
    {
		$quoteItem = $observer->getQuoteItem();
        $product = $quoteItem->getProduct();
		$category = $quoteItem->getGoogleCategory();

		if (!isset($category)){
			$category = $this->_helper->getProductCategoryName($product);
		}

		$productOutBasket = array(
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'category' => $category,
            'brand' => $this->_helper->getBrand($product),
            'variant' => $this->_helper->getVariantProperty($product),
            'price' => $product->getFinalPrice(),
            'qty' => $observer->getQuoteItem()->getQty()
        );

		Mage::getModel('core/session')->setProductOutBasket(json_encode($productOutBasket));
    }

	/**
     * Save Google Cookie Data on Order Confirmation
     *
     * @return bool
     */

	public function saveGoogleCookie($observer)
	{
		$order=$observer->getEvent()->getOrder();
		$order->setGoogleCookie($this->_cid)
			->setGoogleTsCookie($this->_utmz)
			->save();

		return true;
	}

	/**
     * Store data in cookie for refunded order so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function refundOrderInventory(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Creditmemo $creditmemo
         * @var $item Mage_Sales_Model_Order_Creditmemo_Item
         */

        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $orderId = $order->getIncrementId();
		$storeId = $order->getStoreId();
        $products = array();
		$fullRefund = false;

		if (count($order->getAllItems())==count($creditmemo->getAllItems())){
			$fullRefund = true;
		}

        //if ($order->canCreditmemo())
        //{
		foreach ($creditmemo->getAllItems() as $item)
		{
			if($item->getBasePrice()<=0) continue;
			$products[] = array('id' => $item->getSku(), 'qty' => $item->getQty());
		}
        //}

        $response = array(
            'orderId'   => $orderId,
			'storeId'   => $storeId,
            'products'  => $products,
			'fullrefund'=> $fullRefund,
        );

		Mage::getModel('core/session')->setRefundOrder(json_encode($response));
    }

	/**
     * send checkout step data to GA
     *
     * @return void
    */

	public function sendCheckoutStepDataToGA()
	{
		$this->sendBillingStepDataToGA();
		$this->sendShippingStepDataToGA();
		$this->sendShippingMethodStepDataToGA();
		$this->sendPaymentMethodStepDataToGA();
		$this->sendSaveOrderStepDataToGA();
	}

	/**
     * send checkout step data to GA if AwesomeCheckout is enabled
     *
     * @return void
    */

	public function sendCheckoutStepDataForAwesomeCheckout()
	{
		if (Mage::getConfig()->getModuleConfig('AnattaDesign_AwesomeCheckout')->is('active', 'true')){
			$this->sendCheckoutStepDataToGA();
		}
	}

	/**
     * Send billing step data to GA
     *
     * @return void
    */

	public function sendBillingStepDataToGA()
	{
		if (!$this->_helper->isEnabled()) return;

		$cid = $this->_cid;
		$postData = Mage::app()->getRequest()->getPost('billing', array());
		$country_id = "";
		if(isset($postData) && array_key_exists('country_id',$postData)){
			if (isset($postData['country_id'])){
				$country_id = $postData['country_id'];
			}
			if ($this->_helper->stepExists(1) && strlen($country_id)>0){
				//billing checkout step and option data
				$data = $this->addPageView($this->_helper->getAccountId(),$cid,'/checkout/onepage/savebilling','Save Billing',$this->_helper->getStepNumber(1),'Save Billing');

				//adding traffic source data
				$data = $this->addTrafficSourceData($data);

				$cart = Mage::getModel('checkout/cart')->getQuote();

				//adding product data
				//$data = $this->addProductData($cart,$data);

				//sending billing checkout step and option data
				$this->sendDataToGoogle($data);

				//sending save billing event
				$data = $this->sendEvent($this->_helper->getAccountId(),$cid,'Save Billing');

				if (strlen($this->_helper->isLinkAccountsEnabled())>0){
					//billing checkout step and option data
					$data = $this->addPageView($this->_helper->getLinkedAccountId(),$cid,'/checkout/onepage/savebilling','Save Billing',$this->_helper->getStepNumber(1),'Save Billing');

					$cart = Mage::getModel('checkout/cart')->getQuote();

					//adding product data
					//$data = $this->addProductData($cart,$data);

					//sending billing checkout step and option data
					$this->sendDataToGoogle($data);

					//sending save billing event
					$data = $this->sendEvent($this->_helper->getLinkedAccountId(),$cid,'Save Billing');
				}
			}
		}
	}

	/**
     * Send shipping step data to GA
     *
     * @return void
    */
	public function sendShippingStepDataToGA()
	{
		if (!$this->_helper->isEnabled()) return;

		$cid = $this->_cid;
		$postData = Mage::app()->getRequest()->getPost('shipping', array());
		$country_id = "";
		if(isset($postData) && array_key_exists('country_id',$postData)){
			if (isset($postData['country_id'])){
				$country_id = $postData['country_id'];
			}
			if ($this->_helper->stepExists(2) && strlen($country_id)>0){
				//shipping checkout step and option data
				$data = $this->addPageView($this->_helper->getAccountId(),$cid,'/checkout/onepage/saveshipping','Save Shipping',$this->_helper->getStepNumber(2),'Save Shipping');

				//adding traffic source data
				$data = $this->addTrafficSourceData($data);

				$cart = Mage::getModel('checkout/cart')->getQuote();

				//adding product data
				//$data = $this->addProductData($cart,$data);

				//sending shipping checkout step and option data
				$this->sendDataToGoogle($data);

				//sending save shipping event
				$data = $this->sendEvent($this->_helper->getAccountId(),$cid,'Save Shipping');

				if (strlen($this->_helper->isLinkAccountsEnabled())>0){
					//shipping checkout step and option data
					$data = $this->addPageView($this->_helper->getLinkedAccountId(),$cid,'/checkout/onepage/saveshipping','Save Shipping',$this->_helper->getStepNumber(2),'Save Shipping');

					$cart = Mage::getModel('checkout/cart')->getQuote();

					//adding product data
					//$data = $this->addProductData($cart,$data);

					//sending shipping checkout step and option data
					$this->sendDataToGoogle($data);

					//sending save shipping event
					$data = $this->sendEvent($this->_helper->getLinkedAccountId(),$cid,'Save Shipping');
				}
			}
		}
	}

	/**
     * Send shipping method step data to GA
     *
     * @return void
    */
	public function sendShippingMethodStepDataToGA()
	{
		if (!$this->_helper->isEnabled()) return;

		$postData = Mage::app()->getRequest()->getPost('shipping_method', array());
		$cid = $this->_cid;
		if ($this->_helper->stepExists(3) && !empty($postData)){
			//shipping method checkout step and option data
			$data = $this->addPageView($this->_helper->getAccountId(),$cid,'/checkout/onepage/saveshippingmethod','Save Shipping Method',$this->_helper->getStepNumber(3),$postData);

			//adding traffic source data
			$data = $this->addTrafficSourceData($data);

			$cart = Mage::getModel('checkout/cart')->getQuote();

			//adding product data
			//$data = $this->addProductData($cart,$data);

			//sending shipping method checkout step and option data
			$this->sendDataToGoogle($data);

			//sending save shipping method event
			$data = $this->sendEvent($this->_helper->getAccountId(),$cid,$postData);

			if (strlen($this->_helper->isLinkAccountsEnabled())>0){
				//shipping method checkout step and option data
				$data = $this->addPageView($this->_helper->getLinkedAccountId(),$cid,'/checkout/onepage/saveshippingmethod','Save Shipping Method',$this->_helper->getStepNumber(3),$postData);

				$cart = Mage::getModel('checkout/cart')->getQuote();

				//adding product data
				//$data = $this->addProductData($cart,$data);

				//sending shipping method checkout step and option data
				$this->sendDataToGoogle($data);

				//sending save shipping method event
				$data = $this->sendEvent($this->_helper->getLinkedAccountId(),$cid,$postData);
			}
		}
	}

	/**
     * Send payment method step data to GA
     *
     * @return void
    */
	public function sendPaymentMethodStepDataToGA()
	{
		if (!$this->_helper->isEnabled()) return;

		$paymentMethod = "";

		$postData = Mage::app()->getRequest()->getPost('payment', array());

		if (isset($postData['method'])){
			$payment_method  = $postData['method'];
		}
		else{
			$payment_method = Mage::app()->getRequest()->getPost('payment_method', array());
		}

		if (!is_array($payment_method)){
			if (strlen($payment_method)==0){
				$payment_method = Mage::app()->getRequest()->getPost('method', array());
				if (is_array($payment_method)){
					if (isset($payment_method[0])){
						$paymentMethod = $payment_method[0];
					}
				}
				else{
					$paymentMethod = $payment_method;
				}
			}
			else{
				$paymentMethod = $payment_method;
			}
		}
		else{
			if (isset($payment_method[0])){
				$paymentMethod = $payment_method[0];
			}
		}

		$cid = $this->_cid;
		if ($this->_helper->stepExists(4) && strlen($paymentMethod)){

			//payment method checkout step and option data
			$data = $this->addPageView($this->_helper->getAccountId(),$cid,'/checkout/onepage/savepaymentmethod','Save Payment Method',$this->_helper->getStepNumber(4),$payment_method);

			//adding traffic source data
			$data = $this->addTrafficSourceData($data);

			$cart = Mage::getModel('checkout/cart')->getQuote();

			//adding product data
			//$data = $this->addProductData($cart,$data);

			//sending payment method checkout step and option data
			$this->sendDataToGoogle($data);

			//sending save payment method event
			$data = $this->sendEvent($this->_helper->getAccountId(),$cid,$payment_method);

			if (strlen($this->_helper->isLinkAccountsEnabled())>0){
				//payment method checkout step and option data
				$data = $this->addPageView($this->_helper->getLinkedAccountId(),$cid,'/checkout/onepage/savepaymentmethod','Save Payment Method',$this->_helper->getStepNumber(4),$payment_method);

				$cart = Mage::getModel('checkout/cart')->getQuote();

				//adding product data
				//$data = $this->addProductData($cart,$data);

				//sending payment method checkout step and option data
				$this->sendDataToGoogle($data);

				//sending save payment method event
				$data = $this->sendEvent($this->_helper->getLinkedAccountId(),$cid,$payment_method);
			}
		}
	}

	/**
     * Send order review step data to GA
     *
     * @return void
    */
	public function sendSaveOrderStepDataToGA()
	{
		if (!$this->_helper->isEnabled()) return;

		$cid = $this->_cid;
		if ($this->_helper->stepExists(5)){
			//order review data
			$data = $this->addPageView($this->_helper->getAccountId(),$cid,'/checkout/onepage/orderreview','Order Review',$this->_helper->getStepNumber(5),'');

			//adding traffic source data
			$data = $this->addTrafficSourceData($data);

			$cart = Mage::getModel('checkout/cart')->getQuote();

			//adding product data
			//$data = $this->addProductData($cart,$data);

			//sending order review checkout step and option data
			$this->sendDataToGoogle($data);

			//sending order review event
			$data = $this->sendEvent($this->_helper->getAccountId(),$cid,'Order Review');

			if (strlen($this->_helper->isLinkAccountsEnabled())>0){
				//order review data
				$data = $this->addPageView($this->_helper->getLinkedAccountId(),$cid,'/checkout/onepage/orderreview','Order Review',$this->_helper->getStepNumber(5),'');

				$cart = Mage::getModel('checkout/cart')->getQuote();

				//adding product data
				//$data = $this->addProductData($cart,$data);

				//sending order review checkout step and option data
				$this->sendDataToGoogle($data);

				//sending order review event
				$data = $this->sendEvent($this->_helper->getLinkedAccountId(),$cid,'Order Review');
			}
		}
	}

	/**
     * Send order data to GA only on order creation
     *
     * @return void
    */
	public function sendOrderDataToGoogle(Varien_Event_Observer $observer)
	{
		if (!$this->_helper->isEnabled()) return;

		$objOrder = $observer->getEvent()->getOrder();

		$order = Mage::getModel("sales/order")->load($objOrder->getId());

		$storeId = $order->getStoreId();
		if (strlen($order->getStatus())==0 || !$order || $this->_helper->sendTransactionDataOnInvoice($storeId)) return;
		if (
			((!$this->_helper->sendTransactionDataOffline($storeId) || $this->isAdmin()) &&
			(!$this->_helper->sendPhoneOrderTransaction($storeId) || !$this->isAdmin()))
			|| (!$this->_helper->isEnhancedEcommerceEnabled($storeId))
			){
				$objOrder->setSentDataToGoogle(1);
				$objOrder->getResource()->saveAttribute($objOrder, "sent_data_to_google");
				return;
		}

		if (($order->getStatus() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)|| ($order->getStatus() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)) {
			$objOrder->setSentDataToGoogle(2);
			$objOrder->getResource()->saveAttribute($objOrder, "sent_data_to_google");
			return;
		}

		if(!($order->getSentDataToGoogle()==1 || $order->getSentDataToGoogle()==2)){
			$this->buildData($order, $storeId, null, $objOrder);
		}
	}
	/**
     * Send order data to GA only on invoice creation
     *
     * @return void
    */
	public function setOrderDataForGA(Varien_Event_Observer $observer)
    {
		if (!$this->_helper->isEnabled()) return;

        /**
         * @var Mage_Sales_Model_Order_Invoice $invoice
         * @var $item Mage_Sales_Model_Order_Invoice_Item
         */

		$invoice = $observer->getEvent()->getInvoice();

        $order = $invoice->getOrder();
		$storeId = $order->getStoreId();

		if (!$this->_helper->sendTransactionDataOnInvoice($storeId) ||
		!$this->_helper->isEnhancedEcommerceEnabled($storeId) ||
		(strlen($order->getStatus())==0)
		) return;

		$invoiceId = $invoice->getOrigData('entity_id');

		if(is_null($invoiceId ) && !($order->getSentDataToGoogle()==1)){
			$this->buildData($order, $storeId, $invoice);
		}
    }

	/**
     * Main function to build and send data to GA
     *
     * @return void
    */
	public function buildData($order, $storeId, $invoice=null, $objOrder=null, $cancel=false)
	{
		$orderId = $order->getIncrementId();
		$products = array();
		$cid = $order->getGoogleCookie();
		if (!isset($cid)) $cid = $this->gen_uuid();
		$this->gaParseTSCookie($order->getGoogleTsCookie(), $storeId);
		$domainHost = $this->_domainHost;

		if ($this->_helper->sendBaseData($storeId)):
			$orderCurrency 		= $order->getBaseCurrencyCode();
			$orderGrandTotal 	= $order->getBaseGrandTotal();
			$orderShippingTotal	= $order->getBaseShippingAmount();
			$orderTax			= $order->getBaseTaxAmount();
		else:
			$orderCurrency 		= $order->getOrderCurrencyCode();
			$orderGrandTotal 	= $order->getGrandTotal();
			$orderShippingTotal	= $order->getShippingAmount();
			$orderTax			= $order->getTaxAmount();
		endif;

		if ($cancel==true){
			$orderGrandTotal 	= -$orderGrandTotal;
			$orderShippingTotal	= -$orderShippingTotal;
			$orderTax			= -$orderTax;
		}

		//$this->_userAgent = $order->getUserAgent();

		/* Sending Transactional Data to GA*/
		$data = $this->addTransactionalPageView($this->_helper->getAccountId($storeId), $cid, '/checkout/onepage/success', 'Order Confirmation', $orderId, $orderCurrency, $order->getAffiliation(), $orderGrandTotal, $orderShippingTotal, $orderTax, $order->getCouponCode(), 'purchase');

		//adding traffic source data
		if ($cancel==false){
			$data = $this->addTrafficSourceData($data);
		}

		//$data = array_merge($data,$this->parseGoogleCookie($_COOKIE['__utmz']));

		if (is_null($invoice)){
			$data = $this->addProductData($order, $data, true, $storeId, $cancel);
		}
		else{
			$data = $this->addProductData($invoice, $data, false, null, $cancel);
		}

		//echo '<pre>';
		//print_r($data);
		//exit;
		$this->sendDataToGoogle($data);
		$order->setSentDataToGoogle(1)
			->save();

		if (!(is_null($objOrder))){
			$objOrder->setSentDataToGoogle(1);
			$objOrder->getResource()->saveAttribute($objOrder, "sent_data_to_google");
		}

		if (strlen($this->_helper->isLinkAccountsEnabled($storeId))>0){

			/* Sending Transactional Data to GA*/
			$data = $this->addTransactionalPageView($this->_helper->getLinkedAccountId($storeId), $cid, '/checkout/onepage/success', 'Order Confirmation', $orderId, $orderCurrency, $order->getAffiliation(), $orderGrandTotal, $orderShippingTotal, $orderTax, $order->getCouponCode(), 'purchase');

			//$data = array_merge($data,$this->parseGoogleCookie($_COOKIE['__utmz']));

			//adding traffic source data
			$data = $this->addTrafficSourceData($data);

			if (is_null($invoice)){
				$data = $this->addProductData($order, $data, true, $storeId);
			}
			else{
				$data = $this->addProductData($invoice, $data);
			}

			/*echo '<pre>';
			print_r($data);
			exit;*/
			$this->sendDataToGoogle($data);
		}
	}

    public function beforeCollectionLoad(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) return;

		$collection = $observer->getCollection();

        if (!isset($collection))
        {
            return;
        }

        if ($collection instanceof Mage_Catalog_Model_Resource_Product_Collection)
        {
            if ($attribute = $this->_helper->getBrandDropdown())
            {
                $collection->addAttributeToSelect($attribute);
            }
        }
    }

	/**
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function sendTransactionToGoogle(Varien_Event_Observer $observer)
	{
		$block = Mage::app()->getLayout()->getBlock('sales_order_edit');
		if (!$block) {
			return $this;
		}
		$order = Mage::registry('current_order');

		$storeId = $order->getStoreId();
		if ($this->_helper->allowSendingTransactionOffline($storeId) && $this->_helper->isEnabled($storeId)){
			$url   = Mage::helper("adminhtml")->getUrl(
				"adminhtml/smartanalytics_universalanalytics/senddata",
				array('order_id' => $order->getId())
			);
			$block->addButton(
				'button_id',
				array(
					'label'   => Mage::helper('allure_smartanalytics')->__('Send Transaction To Google'),
					'onclick' => "confirmSetLocation('".Mage::helper('allure_smartanalytics')->__('Are you sure you want to send this transaction to GA?')."', '". $url."')",
					'class'   => 'go'
				)
			);
		}
		return $this;
	}

	/**
     * Generating unique id for GA if __ga cookie doesn't exist
     *
     * @return string
    */
	protected function gen_uuid()
	{
		// Generates a UUID. A UUID is required for the measurement protocol.
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		// 16 bits for "time_mid"
		mt_rand( 0, 0xffff ),
		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand( 0, 0x0fff ) | 0x4000,
		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand( 0, 0x3fff ) | 0x8000,
		// 48 bits for "node"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	/**
     * Retrieving cid for GA from __ga cookie
     *
     * @return string
    */
	protected function gaParseCookie($google_cookie) {
		if (isset($google_cookie)) {
			list($version,$domainDepth, $cid1, $cid2) = preg_split('[\.]', $google_cookie,4);
			$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1.'.'.$cid2);
			$cid = $contents['cid'];
		}
		else $cid = $this->gen_uuid();
		return $cid;
	}

	/**
     * Retrieving traffic source data for GA from __utmz cookie
     *
     * @return string
    */
	protected function gaParseTSCookie($google_cookie, $storeId=null) {
		// Parse __utmz cookie
		if (isset($google_cookie)){
			list($domain_hash,$timestamp, $session_number, $campaign_numer, $campaign_data) = preg_split('[\.]', $google_cookie,5);

			// Parse the campaign data
			$campaign_data = parse_str(strtr($campaign_data, "|", "&"));

			if (isset($utmcsr)) $this->_cs = $utmcsr;
			if (isset($utmccn)) $this->_cn = $utmccn;
			if (isset($utmcmd)) $this->_cm = $utmcmd;
			if (isset($utmctr)) $this->_ck = $utmctr;
			if (isset($utmcct)) $this->_cc = $utmcct;
			if (isset($utmgclid)) $this->_gclid = $utmgclid;

			// You should tag you campaigns manually to have a full view
			// of your adwords campaigns data.
			// The same happens with Urchin, tag manually to have your campaign data parsed properly.

			if (isset($utmgclid)&&strlen($utmgclid)>0) {
				$this->_cs = "google";
				$this->_cm = "cpc";
			}

			// Parse the __utma Cookie
			/*list($domain_hash,$random_id,$time_initial_visit,$time_beginning_previous_visit,$time_beginning_current_visit,$session_counter) = split('[\.]', $_COOKIE["__utma"]);

			$this->first_visit = date("d M Y - H:i",$time_initial_visit);
			$this->previous_visit = date("d M Y - H:i",$time_beginning_previous_visit);
			$this->current_visit_started = date("d M Y - H:i",$time_beginning_current_visit);
			$this->times_visited = $session_counter;

			// Parse the __utmb Cookie

			list($domain_hash,$pages_viewed,$garbage,$time_beginning_current_session) = split('[\.]', $_COOKIE["__utmb"]);
			$this->pages_viewed = $pages_viewed;*/
		}

		if ($this->isAdmin() && $this->_helper->sendPhoneOrderTransaction($storeId)){
			$this->_cs = $this->_helper->getSourceText();
			$this->_cm = $this->_helper->getMediumText();
		}
	}

	protected function sendDataToGoogle($data)
	{
		if ($data){
			$url = 'https://ssl.google-analytics.com/collect'; // This is the URL to which we'll be sending the post request.
			$content = http_build_query($data); // The body of the post must include exactly 1 URI encoded payload and must be no longer than 8192 bytes. See http_build_query.
			$content = utf8_encode($content); // The payload must be UTF-8 encoded.

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
			curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
			curl_setopt($ch,CURLOPT_POST, TRUE);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			curl_close($ch);

			if ($this->_debug) Mage::log($data, 7, "GA.log");
		}
	}

	/**
     * Adding product data of the current cart to send to GA as part of measurement protocol call
     *
     * @return array
    */
	protected function addProductData($cart,$data,$order=false,$storeId=null,$cancel=false)
	{
		$intCtr = 1;
		$products = array();
		$qty = 0;

		foreach ($cart->getAllItems() as $item){
			if($item->getBasePrice()<=0) continue;
			if ($order==true) {
				$qty = $item->getQtyOrdered();
			}
			else{
				$qty = $item->getQty();
			}
			if ($cancel==true) $qty = -$qty;
			$products = array(
						'pr'.$intCtr.'nm'	=> Mage::helper('core')->jsQuoteEscape($item->getName(), '"'), // Item name. Required.
						'pr'.$intCtr.'pr' 	=> $this->_helper->sendBaseData($storeId)==true ? $item->getBasePrice() : $item->getPrice(), // Item price.
						'pr'.$intCtr.'qt' 	=> $qty, // Item quantity.
						'pr'.$intCtr.'ca' 	=> Mage::helper('core')->jsQuoteEscape($this->_helper->getQuoteCategoryName($item)), // Item category.
						'pr'.$intCtr.'br' 	=> Mage::helper('core')->jsQuoteEscape($this->_helper->getQuoteBrand($item)), // Item brand.
						'pr'.$intCtr.'id' 	=> $item->getSku(), // Item code / SKU.
						'pr'.$intCtr.'ps'  	=> $intCtr // Item Position.
					);

			$data = array_merge($data,$products);

			$intCtr++;
		}

		return $data;
	}

	/**
     * Adding page view data to send to GA as part of measurement protocol call
     *
     * @return array
    */
	protected function addPageView($accountId, $cid, $dp, $dt, $cos, $col)
	{
		$domainHost = $this->_domainHost;

		$data = array(
						'v' 	=> 1, // The version of the measurement protocol
						'tid' 	=> $accountId, // Google Analytics account ID (UA-98765432-1)
						'cid' 	=> $cid, // The UUID
						't'     => 'pageview', // Hit type
						'dh'     => $domainHost, // Document Hostname
						'dp'     => $dp, // Page
						'dt'     => $dt, // Page Title
						'pa'	=> 'checkout', //Action
						'cos'	=> $cos, // Checkout Step
						'col'	=> $col, // Checkout Step Option
					);
		return $data;
	}

	/**
     * Adding transactional page view data to send to GA as part of measurement protocol call
     *
     * @return array
    */
	protected function addTransactionalPageView($accountId, $cid, $dp, $dt, $orderId, $orderCurrency, $orderAffiliation, $orderGrandTotal, $orderShippingTotal, $orderTax, $orderCouponCode, $pa)
	{
		$domainHost = $this->_domainHost;

		$data = array(
					'v' 	=> 1, // The version of the measurement protocol
					'tid' 	=> $accountId, // Google Analytics account ID (UA-98765432-1)
					'cid' 	=> $cid, // The UUID
					't'     => 'pageview', // Hit Type
					'dh'     => $domainHost, // Domain Hostname
					'dp'     => $dp, // Page
					'dt'     => $dt,// Page Title
					'ti'	=> $orderId,       // transaction ID. Required.
					'cu'	=> $orderCurrency,  // Transaction currency code.
					'ta'	=> $orderAffiliation,  // Transaction affiliation.
					'tr'	=> $orderGrandTotal,        // Transaction revenue.
					'ts'	=> $orderShippingTotal,        // Transaction shipping.
					'tt'	=> $orderTax,       // Transaction tax.
					'tcc'	=> $orderCouponCode, // Transaction coupon code
					'pa'	=> $pa // Product Action
				);
		return $data;
	}

	/**
     * Adding traffic source data to send to GA as part of measurement protocol call
     *
     * @return array
    */
	protected function addTrafficSourceData($data)
	{

		$tsdata = array(
						'cn'	=> $this->_cn, //Campaign Name
						'cs'	=> $this->_cs, //Campaign Source
						'cm'	=> $this->_cm, //Campaign Medium
						'ck'	=> $this->_ck, //Campaign Keyword
						'cc'	=> $this->_cc, //Content
						'gclid' => $this->_gclid //gclid
					);

		$data = array_merge($data,$tsdata);

		return $data;
	}

	/**
     * Sending event data to GA for each step
     *
     * @return void
    */
	protected function sendEvent($accountId, $cid, $el)
	{
		if (strlen($el)){
			$data = array(
						'v' 	=> 1, // The version of the measurement protocol
						'tid' 	=> $accountId, // Google Analytics account ID (UA-98765432-1)
						'cid' 	=> $cid, // The UUID
						't'     => 'event', // Hit Type
						'ec'    => 'UX', // Event Category
						'ea'    => 'click', // Event Action
						'el'    => $el, // Event Label
						'ni'	=> 1, // Non-Interaction Hit
					);

			$this->sendDataToGoogle($data);
		}
	}

	public function isAdmin()
    {
		Mage::getSingleton('core/session', array('name'=>'adminhtml'));
		if(Mage::getSingleton('admin/session')->isLoggedIn()){
			if(Mage::app()->getStore()->isAdmin()){
				return true;
			}

			if(Mage::getDesign()->getArea() == 'adminhtml'){
				return true;
			}
		}
        return false;
    }
}
