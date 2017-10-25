<?php

ini_set('xdebug.var_display_max_depth', 8);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);


require_once('../../app/Mage.php');
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

class POS_Order
{
	const ORDER_REGISTRY_KEY      = 'current_bakerloo_order';
	const XML_PERMISSIONS_LOGIN   = 'bakerloo_api/login';
	const XML_PERMISSIONS_CREATE  = 'bakerloo_api/orders/create';
	const EVENT_ORDER_HAS_RETURNS = 'pos_order_has_returns';
	const EVENT_ORDER_LAYAWAY     = 'pos_order_has_layaway_payment';
	
	const ORDER_ID      = 'order_id';
	const ORDER_NUMBER  = 'order_number';
	const ORDER_STATE   = 'order_state';
	const ORDER_STATUS  = 'order_status';
	const ORDER_DATA    = 'order_data';
	const ORDER_ERROR   = 'error_message';
	
	const RESULT_NOT_SAVED = 'notsaved';
	
	/** @var Ebizmarts_BakerlooRestful_Model_Order  */
	private $_bakerlooOrder;
	
	/** @var Ebizmarts_BakerlooRestful_Model_OrderDtoBuilder */
	private $_dtoBuilder;
	
	/** @var Mage_Core_Model_Resource_Transaction  */
	private $_transaction;
	
	/** @var Ebizmarts_BakerlooRestful_Helper_Data  */
	private $_helper;
	
	/** @var Ebizmarts_BakerlooRestful_Helper_Acl */
	private $_aclHelper;
	
	/** @var Ebizmarts_BakerlooRestful_Helper_Sales */
	private $_salesHelper;
	
	/** @var Ebizmarts_BakerlooRestful_Helper_Http */
	private $_httpHelper;
	
	/** @var Ebizmarts_BakerlooRestful_Model_CustomPrice  */
	private $_customPrice;
	
	/** @var Ebizmarts_BakerlooRestful_Helper_Email  */
	private $_emailHelper;
	
	public function __construct($args = array())
	{
		if (isset($args['bakerlooorder'])) {
			$this->_bakerlooOrder = $args['bakerlooorder'];
		} else {
			$this->_bakerlooOrder = Mage::getModel('bakerloo_restful/order');
		}
		
		if (isset($args['dtobuilder'])) {
			$this->_dtoBuilder = $args['dtobuilder'];
		} else {
			$this->_dtoBuilder = Mage::getModel('bakerloo_restful/orderDtoBuilder');
		}
		
		if (isset($args['transactionresource'])) {
			$this->_transaction = $args['transactionresource'];
		} else {
			$this->_transaction = Mage::getModel('core/resource_transaction');
		}
		
		if (isset($args['bakerloohelper'])) {
			$this->_helper = $args['bakerloohelper'];
		} else {
			$this->_helper = Mage::helper('bakerloo_restful');
		}
		
		if (isset($args['aclhelper'])) {
			$this->_aclHelper = $args['aclhelper'];
		} else {
			$this->_aclHelper = Mage::helper('bakerloo_restful/acl');
		}
		
		if (isset($args['saleshelper'])) {
			$this->_salesHelper = $args['saleshelper'];
		} else {
			$this->_salesHelper = Mage::helper('bakerloo_restful/sales');
		}
		
		if (isset($args['httphelper'])) {
			$this->_httpHelper = $args['httphelper'];
		} else {
			$this->_httpHelper = Mage::helper('bakerloo_restful/http');
		}
		
		if (isset($args['emailhelper'])) {
			$this->_emailHelper = $args['emailhelper'];
		} else {
			$this->_emailHelper = Mage::helper('bakerloo_restful/email');
		}
		
		if (isset($args['customprice'])) {
			$this->_customPrice = $args['customprice'];
		} else {
			$this->_customPrice = Mage::getModel('bakerloo_restful/customPrice');
		}
	}
	
	/**
	 * @param Mage_Core_Controller_Request_Http $request
	 * @param $storeId
	 * @return array
	 */
	public function create(Mage_Core_Controller_Request_Http $request, $storeId)
	{
		// Check create permissions
		$this->checkPlacePermissions($this->getUsernameFromRequest($request));
		
		// Set current store
		$this->setCurrentStoreId($storeId);
		
		// Create bakerloo order
		$this->initBakerlooOrder($this->_httpHelper->getJsonPayload($request, true), $request->getRawBody(), $this->getAllRequestHeaders($request));
		
		return $this->submit();
	}
	
	/**
	 * @param $posOrderId
	 * @return array
	 */
	public function place($posOrderId)
	{
		$this->_bakerlooOrder->load($posOrderId);
		
		if (!$this->_bakerlooOrder->getId()) {
			Mage::throwException($this->_helper->__('The order does not exist.'));
		}
		
		// Check try again permissions
		$this->checkPlacePermissions($this->getUsernameFromOrder($this->_bakerlooOrder));
		
		// Set current store
		$storeId = $this->setCurrentStoreId(null, $this->_bakerlooOrder);
		
		return $this->submit();
	}
	
	/**
	 * @return array
	 */
	protected function submit()
	{
		$returnData = array();
		
		if (!$this->_bakerlooOrder->getId() or !$this->_bakerlooOrder->getStoreId()) {
			$returnData[self::ORDER_ID]     = (int)$this->_bakerlooOrder->getId();
			$returnData[self::ORDER_NUMBER] = (int)$this->_bakerlooOrder->getId();
			$returnData[self::ORDER_STATE]  = self::RESULT_NOT_SAVED;
			$returnData[self::ORDER_STATUS] = self::RESULT_NOT_SAVED;
			$returnData[self::ORDER_ERROR]  = $this->_helper->__("Order could not be submitted.");
			
			return $returnData;
		}
		
		$this->registerOrder($this->_bakerlooOrder);
		$payload = $this->_bakerlooOrder->getJsonPayload();
		
		if (is_string($payload)) {
			$payload = json_decode($payload, true);
		}
		
		$order = new Mage_Sales_Model_Order();
		
		try {
			// Build quote
			$quote = $this->_salesHelper->buildQuote($this->_bakerlooOrder->getStoreId(), $payload);
			
			// Submit quote
			$service = $this->initQuoteService($quote);
			$service->submitAll();
			
			// Retrieve order
			$order = $service->getOrder();
			
			// Dispatch observer events
			$this->dispatchCheckoutEvents($order, $quote);
			$this->checkAndNotifyReturns($order, $payload);
			$this->checkAndNotifyLayaway($order, $payload, $this->_bakerlooOrder);
			$this->checkAndNotifyPriceOverride($order, $payload);
			
			// Order may come through with a request to cancel
			$this->checkAndCancelOrder($order, $payload);
			
			// Process order invoice, shipment, and notifications
			$service   = $this->initOrderService($order);
			$invoiced  = $this->checkAndInvoice($service, $order);
			$shipped   = $this->checkAndShip($service, $order, $invoiced);
			$commented = $this->checkAndComment($order, $payload);
			
			$this->_transaction->save();
			
			// Update bakerloo order
			$this->updateBakerlooOrder($order, $payload, $this->_bakerlooOrder->getId());
			$this->_bakerlooOrder->setFailMessage('')->save();
			
			// Prepare return data
			$returnData[self::ORDER_ID]     = (int)$order->getId();
			$returnData[self::ORDER_NUMBER] = $order->getIncrementId();
			$returnData[self::ORDER_STATE]  = $order->getState();
			$returnData[self::ORDER_STATUS] = $order->getStatusLabel();
			$returnData[self::ORDER_DATA]   = $this->_dtoBuilder->getDataObject($order, $this->_bakerlooOrder);
			
			// Inactivate quote
			$quote->setIsActive(false)->save();
			
			die('DONE');
		} catch (Exception $e) {
			Mage::logException($e);
			
			//set quote as not active if the order fails.
			if (isset($quote)) {
				$quote->setIsActive(false)->save();
			}
			
			//die("ERROR::".$e->getMessage());
			$this->_bakerlooOrder->setFailMessage($e->getMessage())->save();
			
			// Prepare return data
			$returnData[self::ORDER_ID]     = (int)$this->_bakerlooOrder->getId();
			$returnData[self::ORDER_NUMBER] = (int)$this->_bakerlooOrder->getId();
			$returnData[self::ORDER_STATE]  = self::RESULT_NOT_SAVED;
			$returnData[self::ORDER_STATUS] = self::RESULT_NOT_SAVED;
			$returnData[self::ORDER_ERROR]  = $this->_helper->__($e->getMessage());
			
			$this->_salesHelper->notifyAdmin(
				array(
					'severity'    => Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
					'date_added'  => Mage::getModel('core/date')->date(),
					'title'       => $this->_helper->__("POS order number #%s failed.", $this->_bakerlooOrder->getId()),
					'description' => $this->_helper->__($e->getMessage()),
					'url'         => null
				)
				);
			
			$failedId=Mage::registry('failed_ebiz_order');
			if(empty($failedId) ||$failedId!=$this->_bakerlooOrder->getId())
			    $this->_salesHelper->processFailedOrder($this->_bakerlooOrder->getId());
			
			$this->updateBakerlooOrder($order, $payload, $this->_bakerlooOrder->getId());
		}
		
		return $returnData;
	}
	
	/**
	 * @param $username
	 */
	public function checkPlacePermissions($username)
	{
		//$this->_aclHelper->checkPermission($username, array(self::XML_PERMISSIONS_LOGIN, self::XML_PERMISSIONS_CREATE));
	}
	
	/**
	 * @param array $data
	 * @param null $rawData
	 * @param array $requestHeaders
	 */
	protected function initBakerlooOrder($data = array(), $rawData = null, $requestHeaders = array())
	{
		$this->_bakerlooOrder->unsetData();
		$this->validatePostData($data);
		
		$this->_bakerlooOrder->setRemoteIp($this->_helper->getRemoteAddr());
		$this->_bakerlooOrder->setRequestUrl($this->_helper->getRequestUrl());
		
		$this->setDataFromHeaders($requestHeaders, true);
		$this->setDataFromBody($data);
		$this->setDataFromRawBody($rawData);
		
		$this->_bakerlooOrder->save();
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 * @param null $posOrderId
	 * @param array $requestHeaders
	 */
	protected function updateBakerlooOrder(Mage_Sales_Model_Order $order, $data = array(), $posOrderId = null, $requestHeaders = array())
	{
		$this->_bakerlooOrder->load($posOrderId);
		$this->setDataFromHeaders($requestHeaders, false);
		$this->setDataFromBody($data);
		$this->setDataFromOrder($order);
		
		$this->_bakerlooOrder->save();
	}
	
	/**
	 * @param array $data
	 */
	protected function validatePostData($data = array())
	{
		// Verify mandatory fields are present
		$fields = array(
			'order_id', 'id', 'order_guid', 'internal_id'
		);
		
		foreach ($fields as $_field) {
			if (!array_key_exists($_field, $data)) {
				Mage::throwException($this->_helper->__("Invalid order data."));
			}
		}
		
		// Check for duplicates by order_guid
		if (is_null($data['order_guid'])) {
			Mage::throwException($this->_helper->__("Invalid order data."));
		}
		
		if (is_null($data['internal_id'])) {
			Mage::throwException($this->_helper->__("Invalid order data."));
		}
		
		$duplicate = $this->_bakerlooOrder->load($data['order_guid'], 'order_guid');
		if ($duplicate->getId()) {
			Mage::throwException("Duplicate POST for `{$data['order_guid']}`.");
		}
		
		$this->_bakerlooOrder->unsetData();
	}
	
	/**
	 * @param array $requestHeaders
	 */
	protected function setDataFromHeaders($requestHeaders = array(), $isNew = false)
	{
		if (isset($requestHeaders[$this->_helper->getUsernameHeader()])) {
			$this->_bakerlooOrder->setLoginUser($requestHeaders[$this->_helper->getUsernameHeader()]);
		}
		
		if (isset($requestHeaders[$this->_helper->getUsernameAuthHeader()])) {
			$this->_bakerlooOrder->setLoginUserAuth($requestHeaders[$this->_helper->getUsernameAuthHeader()]);
		}
		
		if (isset($requestHeaders[$this->_helper->getDeviceIdHeader()])) {
			$this->_bakerlooOrder->setDeviceId($requestHeaders[$this->_helper->getDeviceIdHeader()]);
		}
		
		if (isset($requestHeaders[$this->_helper->getUserAgentHeader()])) {
			$this->_bakerlooOrder->setUserAgent($requestHeaders[$this->_helper->getUserAgentHeader()]);
		}
		
		if (isset($requestHeaders[$this->_helper->getLatitudeHeader()])) {
			$this->_bakerlooOrder->setLatitude($requestHeaders[$this->_helper->getLatitudeHeader()]);
		}
		if (isset($requestHeaders[$this->_helper->getLongitudeHeader()])) {
			$this->_bakerlooOrder->setLongitude($requestHeaders[$this->_helper->getLongitudeHeader()]);
		}
		
		if ($isNew) {
			//Store request headers
			$orderHeaders = array();
			foreach ($this->_helper->allPossibleHeaders() as $_rqh) {
				if (isset($requestHeaders[$_rqh])) {
					$orderHeaders[$_rqh] = $requestHeaders[$_rqh];
				}
			}
			
			$this->_bakerlooOrder->setJsonRequestHeaders(json_encode($orderHeaders));
			
			if (isset($requestHeaders[$this->_helper->getStoreIdHeader()])) {
				$this->_bakerlooOrder->setStoreId($requestHeaders[$this->_helper->getStoreIdHeader()]);
			}
		}
	}
	
	/**
	 * @param null $rawData
	 */
	protected function setDataFromRawBody($rawData = null)
	{
		$rawData = json_decode($rawData, true);
		
		if ($rawData) {
			if (isset($rawData['payment']['customer_signature'])) {
				$this->_bakerlooOrder->setCustomerSignature($rawData['payment']['customer_signature']);
				unset($rawData['payment']['customer_signature']);
			}
			
			if (isset($rawData['timezone']) and !$this->_bakerlooOrder->getId()) {
				$_rawData['local_delivery_date'] = $this->_helper->convertDateFromUTCtoTimezone($rawData['delivery_date'], $rawData['timezone']);
			}
			
			$this->_bakerlooOrder->setJsonPayload(json_encode($rawData));
		}
	}
	
	/**
	 * @param array $data
	 */
	protected function setDataFromBody($data = array())
	{
		$this->_bakerlooOrder->setAdminUser($data['user']);
		
		if (isset($data['salesperson'])) {
			$this->_bakerlooOrder->setSalesperson($data['salesperson']);
		}
		
		if (isset($data['internal_id'])) {
			$this->_bakerlooOrder->setDeviceOrderId($data['internal_id']);
		}
		if (isset($data['order_guid'])) {
			$this->_bakerlooOrder->setOrderGuid($data['order_guid']);
		}
		if (isset($data['auth_user'])) {
			$this->_bakerlooOrder->setAdminUserAuth($data['auth_user']);
		}
		if (isset($data['customer']['is_default_customer'])) {
			$usesDefault = !is_null($data['customer']['is_default_customer']) ? $data['customer']['is_default_customer'] : 0;
			$this->_bakerlooOrder->setUsesDefaultCustomer($usesDefault);
		}
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 */
	protected function setDataFromOrder(Mage_Sales_Model_Order $order)
	{
		$additional = array(
			'store_id',
			'grand_total',
			'subtotal',
			'base_subtotal',
			'base_grand_total',
			'base_shipping_amount',
			'base_tax_amount',
			'base_to_global_rate',
			'base_to_order_rate',
			'base_currency_code',
			'tax_amount',
			'store_to_base_rate',
			'store_to_order_rate',
			'global_currency_code',
			'order_currency_code',
			'store_currency_code',
		);
		foreach ($additional as $_attribute) {
			$this->_bakerlooOrder->setData($_attribute, $order->getData($_attribute));
		}
		
		if ($order->getPayment()) {
			$this->_bakerlooOrder->setPaymentMethod($order->getPayment()->getMethod());
		}
		
		$this->_bakerlooOrder->setOrderIncrementId($order->getIncrementId());
		$this->_bakerlooOrder->setOrderId($order->getId());
	}
	
	/**
	 * Retrieve username saved in Bakerloo order header.
	 *
	 * @param Ebizmarts_BakerlooRestful_Model_Order $order
	 * @return string
	 */
	protected function getUsernameFromOrder(Ebizmarts_BakerlooRestful_Model_Order $order)
	{
		$username = '';
		$headers = $order->getHttpHeadersAsArray();
		$userH = $this->_helper->getUsernameHeader();
		
		if (isset($headers[$userH])) {
			$username = $headers[$userH];
		}
		
		return $username;
	}
	
	/**
	 * @param Mage_Core_Controller_Request_Http $request
	 * @return string
	 */
	protected function getUsernameFromRequest(Mage_Core_Controller_Request_Http $request)
	{
		return (string)$request->getHeader($this->_helper->getUsernameHeader());
	}
	
	/**
	 * @param Mage_Core_Controller_Request_Http $request
	 * @return array
	 */
	protected function getAllRequestHeaders(Mage_Core_Controller_Request_Http $request)
	{
		$headers = array();
		foreach ($this->_helper->allPossibleHeaders() as $_rqh) {
			$headers[$_rqh] = $request->getHeader($_rqh);
		}
		
		return $headers;
	}
	
	/**
	 * @param int|null $storeId
	 * @param Ebizmarts_BakerlooRestful_Model_Order|null $order
	 * @return int|null
	 */
	protected function setCurrentStoreId($storeId = null, Ebizmarts_BakerlooRestful_Model_Order $order = null)
	{
		if (is_null($storeId)) {
			$headers = $order->getHttpHeadersAsArray();
			$storeH  = $this->_helper->getStoreIdHeader();
			
			if (isset($headers[$storeH])) {
				$storeId = $headers[$storeH];
			}
		}
		
		Mage::app()->setCurrentStore($storeId);
		
		if (!is_null($order) and !$order->getStoreId()) {
			$order->setStoreId($storeId);
		}
		
		return $storeId;
	}
	
	/**
	 * @param Mage_Sales_Model_Quote $quote
	 * @return Mage_Sales_Model_Service_Quote
	 */
	protected function initQuoteService(Mage_Sales_Model_Quote $quote)
	{
		return Mage::getModel('sales/service_quote', $quote);
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return Mage_Sales_Model_Service_Order
	 */
	protected function initOrderService(Mage_Sales_Model_Order $order)
	{
		return Mage::getModel('sales/service_order', $order);
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param Mage_Sales_Model_Quote $quote
	 */
	protected function dispatchCheckoutEvents(Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote)
	{
		Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order' => $order, 'quote' => $quote));
		Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 */
	protected function checkAndNotifyReturns(Mage_Sales_Model_Order $order, $data = array())
	{
		if ($this->shouldNotifyReturns($data)) {
			$returnDetails = $this->getReturnDetails($data['returns']);
			Mage::dispatchEvent(self::EVENT_ORDER_HAS_RETURNS, array('order_id' => $order->getIncrementId(), 'returned_items' => $returnDetails));
		}
	}
	
	/**
	 * @param $returnedProducts
	 * @return array
	 */
	protected function getReturnDetails($returnedProducts)
	{
		$returnedProductDetails = array();
		
		foreach ($returnedProducts as $prod) {
			if (isset($prod['bundle_option']) && !empty($prod['bundle_option'])) {
				$bundleQty = $prod['qty'];
				
				$bundledProducts = $prod['bundle_option'];
				foreach ($bundledProducts as $bundledProd) {
					foreach ($bundledProd['selections'] as $selectedProd) {
						if ($selectedProd['selected'] == true) {
							$productDetails = array(
								'product_id' => $selectedProd['product_id'],
								'product_qty' => $selectedProd['qty'] * $bundleQty
							);
							$returnedProductDetails[] = $productDetails;
						}
					}
				}
			} elseif (isset($prod['type']) && $prod['type'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
				$productDetails = array(
					'product_id' => $prod['child_id'],
					'product_qty' => $prod['qty']
				);
				$returnedProductDetails[] = $productDetails;
			} else {
				$productDetails = array(
					'product_id' => $prod['product_id'],
					'product_qty' => $prod['qty']
				);
				$returnedProductDetails[] = $productDetails;
			}
		}
		
		return $returnedProductDetails;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 * @param Ebizmarts_BakerlooRestful_Model_Order $posOrder
	 */
	protected function checkAndNotifyLayaway(Mage_Sales_Model_Order $order, $data = array(), Ebizmarts_BakerlooRestful_Model_Order $posOrder)
	{
		if ($data['payment']['method'] == Ebizmarts_BakerlooPayment_Model_Layaway::CODE) {
			Mage::dispatchEvent(self::EVENT_ORDER_LAYAWAY, array('order' => $order, 'payload' => $data, 'posorder' => $posOrder));
		}
	}
	
	protected function checkAndNotifyPriceOverride(Mage_Sales_Model_Order $order, $data = array())
	{
		if (isset($data['discount']) and $data['discount'] > 0) {
			
			$totalBefore = (float)$data['total_amount'];
			$discount    = (float)$data['discount'];
			$totalAfter  = $totalBefore - $discount;
			
			//save discount to custom price table
			$this->_customPrice->unsetData();
			$this->_customPrice
			->setId(null)
			->setOrderId($order->getId())
			->setOrderIncrementId($order->getIncrementId())
			->setAdminUser($data['user'])
			->setStoreId($order->getStoreId())
			->setTotalDiscount($discount)
			->setGrandTotalBeforeDiscount($totalBefore)
			->setGrandTotalAfterDiscount($totalAfter)
			->save();
			
			//check email config and send
			$notifyFlag = $this->_helper->config('custom_discount_email/enabled', $order->getStoreId());
			$notifyPercent = (int)$this->_helper->config('custom_discount_email/minimum_percent', $order->getStoreId());
			$orderPercent = ($totalBefore != 0) ? $discount/$totalBefore * 100 : 0;
			
			if ($notifyFlag and ($orderPercent >= $notifyPercent)) {
				$this->_emailHelper->sendPriceOverride($order, $discount);
			}
		}
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 */
	protected function checkAndCancelOrder(Mage_Sales_Model_Order $order, $data = array())
	{
		if (isset($data['order_state']) && ((int)$data['order_state'] === 4)) {
			$order->cancel()->save();
		}
	}
	
	/**
	 * @param Mage_Sales_Model_Service_Order $service
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	protected function checkAndInvoice(Mage_Sales_Model_Service_Order $service, Mage_Sales_Model_Order $order)
	{
		$invoiced = false;
		
		if ($order->getId() and $this->shouldInvoice($order)) {
			$invoice = $service->prepareInvoice();
			
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			$invoice->setTransactionId(time());
			$invoice->register();
			
			//Do no send invoice email
			$invoice->setEmailSent(false);
			$invoice->getOrder()->setCustomerNoteNotify(false);
			
			$this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
			
			$invoiced = true;
			
		} elseif ($order->getInvoiceCollection() and ($order->getInvoiceCollection()->count())) {
			$invoiced = true;
		}
		
		return $invoiced;
	}
	
	/**
	 * @param Mage_Sales_Model_Service_Order $service
	 * @param Mage_Sales_Model_Order $order
	 * @param bool $hasInvoice
	 * @return bool
	 */
	protected function checkAndShip(Mage_Sales_Model_Service_Order $service, Mage_Sales_Model_Order $order, $hasInvoice = false)
	{
		$shipped = false;
		
		if ($order->getId() and $this->shouldShip($order, $hasInvoice)) {
			$shipment = $service->prepareShipment($this->getShipmentQty($order));
			$shipment->register();
			
			if ($shipment) {
				$shipment->setEmailSent(false); // @TODO: verify this
				$this->_transaction->addObject($shipment)->addObject($shipment->getOrder());
				
				$shipped = true;
			}
		}
		
		return $shipped;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 * @return bool
	 */
	protected function checkAndComment(Mage_Sales_Model_Order $order, $data = array())
	{
		$commented = false;
		
		if ($order->getId() and isset($data['comments'])) {
			$order->addStatusHistoryComment($data['comments']);
			$order->setCustomerNote($data['comments']);
			$order->setIsVisibleOnFront(true);
			$order->setCustomerNoteNotify(true);
			
			$this->_transaction->addObject($order);
			
			$commented = true;
		}
		
		return $commented;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return array
	 */
	protected function getShipmentQty(Mage_Sales_Model_Order $order)
	{
		$itemsShipmentQty = array();
		
		foreach ($order->getItemsCollection() as $item) {
			/** @var Mage_Sales_Model_Order_Item $item */
			if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && !$item->isShipSeparately()) {
				$itemsShipmentQty[$item->getId()] = $item->getQtyOrdered();
			} elseif (!is_null($item->getParentItem()) && $item->getParentItem()->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $item->isShipSeparately()) {
				$itemsShipmentQty[$item->getId()] = $item->getQtyOrdered();
			}
		}
		
		return $itemsShipmentQty;
	}
	
	/**
	 * @param array $data
	 * @return bool
	 */
	protected function shouldNotifyReturns($data = array())
	{
		$notify = false;
		
		if (isset($data['returns']) && !empty($data['returns'])) {
			$notify = true;
		}
		
		return $notify;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	protected function shouldInvoice(Mage_Sales_Model_Order $order)
	{
		$invoice = 0;
		
		if ($order->canInvoice()) {
			$invoice = (int)$order->getPayment()->getMethodInstance()->getConfigData("invoice");
			
			if ($order->getPayment()->getMethod() == 'free') {
				$invoice = (int)$this->getStoreConfig('payment/bakerloo_free/invoice', $order->getStoreId());
			} elseif ($order->getPayment()->getMethod() == 'bakerloo_layaway') {
				$invoice = 0;
			}
		}
		
		return (bool)$invoice;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param bool $hasInvoice
	 * @return bool
	 */
	protected function shouldShip(Mage_Sales_Model_Order $order, $hasInvoice = false)
	{
		$shouldShip = false;
		
		if ($hasInvoice and !$order->getIsVirtual()) {
			
			$shippingMethod = explode('_', $order->getShippingMethod(), 3);
			
			if ($order->getPayment()->getMethod() == 'free') {
				$shipmentConfigFromPayment = (int)$this->getStoreConfig('payment/bakerloo_free/ship', $order->getStoreId());
			} elseif ($order->getPayment()->getMethod() == 'bakerloo_layaway') {
				$shipmentConfigFromPayment = 0;
			} else {
				$shipmentConfigFromPayment = (int)$order->getPayment()->getMethodInstance()->getConfigData("ship");
			}
			
			if ($shipmentConfigFromPayment === 1) {
				$shouldShip = true;
			} elseif ($shipmentConfigFromPayment === 2 and isset($shippingMethod[2])) {
				$shouldShip = (int)$this->getStoreConfig('carriers/' . $shippingMethod[2] . '/ship', $order->getStoreId());
			}
		}
		
		return (bool)$shouldShip;
	}
	
	/**
	 * @param string $path
	 * @param string|int $storeId
	 * @return string
	 */
	protected function getStoreConfig($path, $storeId)
	{
		return Mage::getStoreConfig($path, $storeId);
	}
	
	protected function registerOrder($order) {
		if (Mage::registry(self::ORDER_REGISTRY_KEY)) {
			Mage::unregister(self::ORDER_REGISTRY_KEY);
		}
		
		Mage::register(self::ORDER_REGISTRY_KEY, $order);
	}
}


$orderProcessor = new POS_Order();

$action = $_REQUEST['action'];

$posOrderId = (int) $_REQUEST['id'];


if (empty($action) || empty($posOrderId)) {
	die('Invalid Data');
}

if (empty($posOrderId)) {
	die('Invalid Data');
}

switch ($action) {
	case 'process':
		$orderProcessor->place($posOrderId);
		break;
}

