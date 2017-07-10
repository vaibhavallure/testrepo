<?php

class Ebizmarts_BakerlooRestful_Model_Api_Orders extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_order';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order';

    /** @var Ebizmarts_BakerlooRestful_Model_OrderManagement  */
    private $_manager;

    /** @var Ebizmarts_BakerlooRestful_Model_OrderDtoBuilder  */
    private $_dtoBuilder;

    public $defaultDir = "DESC";

    protected $_model = "sales/order";
    protected $_filterUseOR = true;
    protected $_pickUpSearch = false;

    public function __construct($params)
    {
        parent::__construct($params);

        if (isset($params['manager'])) {
            $this->_manager = $params['manager'];
        } else {
            $this->_manager = Mage::getModel('bakerloo_restful/orderManagement');
        }

        if (isset($params['dtobuilder'])) {
            $this->_dtoBuilder = $params['dtobuilder'];
        } else {
            $this->_dtoBuilder = Mage::getModel('bakerloo_restful/orderDtoBuilder');
        }
    }

    public function checkDeletePermissions()
    {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/delete'));
    }

    public function checkPostPermissions()
    {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/create'));
    }

    public function _createDataObject($id = null, $data = null)
    {
        $result = array();

        /* @var $order Mage_Sales_Model_Order */
        $order = $this->getModel('sales/order')->load($id);

        if ($order->getId()) {
            /** @var Ebizmarts_BakerlooRestful_Model_Order $posOrder */
            $posOrder = $this->getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');

            $result = $this->_dtoBuilder->getDataObject($order, $posOrder);
        }

        return $this->returnDataObject($result);
    }

    /**
     * Create order in Magento.
     *
     */
    public function post()
    {
        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        return $this->_manager->create($this->getRequest(), $this->getStoreId());
    }

    /**
     * Cancel order
     */
    public function delete()
    {
        parent::delete();

        $orderId = $this->_getIdentifier();

        $order = $this->getModel($this->_model)->load($orderId);

        if ($order->getId()) {
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
            } else {
                Mage::throwException("Order can not be canceled.");
            }
        } else {
            Mage::throwException("Order does not exist.");
        }

        return array(
            'order_id'     => (int)$order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel()
        );
    }

    /**
     * @return Object|Varien_Data_Collection
     */
    protected function _getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = $this->getResourceModel('sales/order_collection');
        }

        return $this->_collection;
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param boolean $useOR
     */
    public function applyFilters($filters, $useOR = false)
    {
        if ($this->_pickUpSearch) {
            parent::applyFilters($filters, $this->_filterUseOR);
        }
        else if (count($filters) == 1) {
            $filter = $this->explodeFilter($filters[0]);

            if ("increment_id" == $filter[0] && $filter[1] == 'eq') {

                $orderByIncrementId = $this->getModel($this->_model)->loadByIncrementId($filter[2]);
                if ($orderByIncrementId->getId()) {
                    $this->_getCollection()->getSelect()->where('main_table.increment_id = ?', $filter[2]);
                } else {
                    $this->_filterByDeviceOrderId($filter[2]);
                }

            } else {
                if ("order_guid" == $filter[0] && $filter[1] == 'eq') {
                    $this->_getCollection()->getSelect()->joinLeft(
                        array('pos' => $this->_posTableName()),
                        'main_table.entity_id = pos.order_id',
                        array()
                    )->where('pos.order_guid = ?', $filter[2]);
                } else {
                    parent::applyFilters($filters, true);
                }
            }
        } else {
            $magicSearch = false;

            foreach ($filters as $_filterString) {
                $fl = $this->explodeFilter($_filterString);

                //Try search by device_order_id
                if ($fl[0] == 'increment_id') {
                    $collection = $this->getModel('bakerloo_restful/order')->getCollection();
                    $collection->addFieldToFilter('device_order_id', array($fl[1] => $fl[2]));

                    if (((int)$collection->getSize() > 0)) {
                        $magicSearch = true;
                        $this->_filterByDeviceOrderId($fl[2]);
                    }
                }
            }

            if (!$magicSearch) {

                // State filter should be applied with AND, while all other filters use OR.
                $stateFilterPosition = $this->getFilterByName('state', null, true);
                $stateFilter = $this->getFilterByName('state');
                if (!is_null($stateFilterPosition)) {
                    unset($filters[$stateFilterPosition]);
                }

                parent::applyFilters($filters, $this->_filterUseOR);

                if (!is_null($stateFilterPosition)) {
                    $stateFilter = $this->explodeFilter($stateFilter);
                    if (array_key_exists(2, $stateFilter) and !empty($stateFilter[2])){
                        $this->_getCollection()->addFieldToFilter($stateFilter[0], array($stateFilter[1] => $stateFilter[2]));
                    }
                }
            }
        }
    }

    protected function _posTableName()
    {
        return $this->getModel('core/resource', true)->getTableName('bakerloo_restful/order');
    }

    protected function _filterByDeviceOrderId($orderId)
    {
        $this->_collection->getSelect()->joinLeft(
            array('pos' => $this->_posTableName()),
            'main_table.entity_id = pos.order_id',
            array()
        )->where('pos.device_order_id LIKE ?', $orderId);
    }

    /**
     * Save order in local table POS > Orders.
     *
     * @param  int   $id      [description]
     * @param  Mage_Sales_Model_Order   $order   [description]
     * @param  stdClass $data    [description]
     * @param  string   $rawData [description]
     * @return Ebizmarts_BakerlooRestful_Model_Order            [description]
     */
    public function saveOrder($order, $data, $id = null, $rawData = null)
    {
//        Mage::log('Saving bakerloo order.');

        /** @var Ebizmarts_BakerlooRestful_Model_Order $_bakerlooOrder */
        $_bakerlooOrder = $this->getModel('bakerloo_restful/order');
        $headerId = (int)$this->_getRequestHeader('B-Order-Id');
        if ($headerId) {
            $id = $headerId;
        }

        if (!is_null($id)) {
            $_bakerlooOrder->load($id);
        } else {
            $this->validatePostData($data);

            //Store request headers in local table first time
            //so if it fails we can retry with all original data
            $requestHeaders = array();
            foreach ($this->getHelper('bakerloo_restful')->allPossibleHeaders() as $_rqh) {
                $value = (string)$this->_getRequestHeader($_rqh);
                if (!empty($value)) {
                    $requestHeaders[$_rqh] = $value;
                }
            }
            $_bakerlooOrder->setJsonRequestHeaders(json_encode($requestHeaders));
        }
        //Save order in custom table
        $_bakerlooOrder
            ->setOrderIncrementId($order->getIncrementId())
            ->setOrderId($order->getId())
            ->setAdminUser($data['user'])
            ->setLoginUser($this->getUsername())
            ->setLoginUserAuth($this->getUsernameAuth())
            ->setSalesperson((isset($data['salesperson']) ? $data['salesperson'] : null))
            ->setRemoteIp($this->getHelper('core/http')->getRemoteAddr())
            ->setDeviceId($this->getDeviceId())
            ->setUserAgent($this->getUserAgent())
            ->setRequestUrl($this->getHelper('core/url')->getCurrentUrl()); //@TODO: Check this.

        if (!is_null($rawData)) {
//            Mage::log('Order has raw data. ');

            $_rawData = json_decode($rawData, true);

            if (isset($_rawData['payment']['customer_signature'])) {
                $_bakerlooOrder->setCustomerSignature($_rawData['payment']['customer_signature']);
                unset($_rawData['payment']['customer_signature']);
            }

            if (isset($_rawData['timezone']) and !$_bakerlooOrder->getId()) {
                $_rawData['local_delivery_date'] = $this->getHelper('bakerloo_restful')
                    ->convertDateFromUTCtoTimezone($_rawData['delivery_date'], $_rawData['timezone']);
            }

            $_bakerlooOrder->setJsonPayload(json_encode($_rawData));
//            Mage::log('Json payload saved. ');
        }
        //Device Order ID
        if (isset($data['internal_id'])) {
            $_bakerlooOrder->setDeviceOrderId($data['internal_id']);
        }
        if (isset($data['order_guid'])) {
            $_bakerlooOrder->setOrderGuid($data['order_guid']);
        }
        if (isset($data['auth_user'])) {
            $_bakerlooOrder->setAdminUserAuth($data['auth_user']);
        }
        if (isset($data['customer']['is_default_customer'])) {
            $usesDefault = !is_null($data['customer']['is_default_customer']) ? $data['customer']['is_default_customer'] : 0;
            $_bakerlooOrder->setUsesDefaultCustomer($usesDefault);
        }
        if ($this->getLatitude()) {
            $_bakerlooOrder->setLatitude($this->getLatitude());
        }
        if ($this->getLongitude()) {
            $_bakerlooOrder->setLongitude($this->getLongitude());
        }

        //Store additional data.
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
            $_bakerlooOrder->setData($_attribute, $order->getData($_attribute));
        }

        if ($order->getPayment()) {
            $_bakerlooOrder->setPaymentMethod($order->getPayment()->getMethod());
        }

        $_bakerlooOrder->save();

        if ($order->getId()) {
            $_bakerlooOrder->setRealCreatedAtToParent();
        }
//        Mage::log('Order saved. ');
        return $_bakerlooOrder;
    }

    private function validatePostData($data)
    {
        $helper = $this->getHelper('bakerloo_restful');

        // Verify mandatory fields are present
        $fields = array(
            'order_id', 'id', 'order_guid', 'internal_id'
        );

        foreach ($fields as $_field) {
            if (!array_key_exists($_field, $data)) {
                Mage::throwException($helper->__("Invalid order data."));
            }
        }

        // Check for duplicates by order_guid
        if (is_null($data['order_guid'])) {
            Mage::throwException($helper->__("Invalid order data."));
        }

        if (is_null($data['internal_id'])) {
            Mage::throwException($helper->__("Invalid order data."));
        }

        $duplicate = $this->getModel('bakerloo_restful/order')->load($data['order_guid'], 'order_guid');
        if ($duplicate->getId()) {
            Mage::throwException("Duplicate POST for `{$data['order_guid']}`.");
        }

    }

    /**
     * Given an order ID, send order email.
     *
     * @return array Email sending result
     */
    public function sendEmail()
    {

        //get data
        $orderId = (int)$this->_getQueryParameter('orderId');
        $customEmail = (string)$this->_getQueryParameter('email');
        $storeEmail = (string)Mage::app()->getStore()->getConfig('trans_email/ident_general/email');

        //Load order and check if exists.
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);

        if (!$order->getId()) {
            Mage::throwException("Order does not exist.");
        }

        Mage::app()->setCurrentStore($order->getStoreId());

        //send email if custom email is valid and different from store email
        $email = filter_var($customEmail, FILTER_VALIDATE_EMAIL) ? $customEmail : $order->getCustomerEmail();

        if ($storeEmail != $email) {
            $emailSent = $this->insertEmail($order, $email);
        } else {
            $emailSent = false;
        }

        //return a jSon object with order data and email status
        $result = array(
            'order_id'     => (int)$order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel(),
            'email_sent'   => $emailSent
        );
        return $result;
    }

    /**
     * @param $order
     */
    public function insertEmail(Mage_Sales_Model_Order $order, $customEmail = null)
    {

        $inserted = false;
        $helper = $this->getHelper('bakerloo_restful');

        if ($this->getRequest()->isPost()) {
            $data = $this->getJsonPayload();
        } else {
            $data = new stdClass();
        }

        $salesHelper = $this->getHelper('bakerloo_restful/sales');
        //Add customer from email if email is valid and customer is new
        $customer = $salesHelper->customerExists($customEmail, Mage::app()->getStore()->getWebsiteId());
        $createConfig = (int)$helper->config('checkout/create_customer');
        $customerInOrderIsGuestOrDefault = $salesHelper->customerInOrderIsGuestOrDefault($order);

        if ($customer === false) {
            if ($createConfig) {
                $this->addCustomer($customEmail, $order, false);
            }
        } elseif ($customerInOrderIsGuestOrDefault && $customer->getId()) {
            $this->setCustomerToOrder($customer, $order);
            $bakerlooOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');
            $bakerlooOrder->setUsesDefaultCustomer(0)->save();
        }

        //Register flag for workaround in Magento version 1.8 or lower.
        if (!Mage::registry('pos_send_email_to')) {
            Mage::register('pos_send_email_to', $order->getCustomerEmail());
        }

        $emailType = (string)$helper->config('pos_receipt/receipts', $this->getStoreId());
        $subscribeToNewsletter = (bool)$this->_getQueryParameter('subscribe_to_newsletter');

        //Add incidence to bakerloo_email/unsent_emails table
        $unsentQueue = $this->insertUnsentEmail($order, $emailType, $customEmail);
        if ($unsentQueue->getId()) {
            $inserted = true;

            //Add incidence to bakerloo_email/log table
            $queue = $this->logEmail($order, $emailType, $subscribeToNewsletter, null, $customEmail);

            //Save attachment if set
            if (isset($data->attachments) and is_array($data->attachments) and !empty($data->attachments)) {
                $receiptData = current($data->attachments);

                //Store image name in database.
                $unsentQueue->setAttachment($receiptData->name)->save();
                $queue->setAttachment($receiptData->name)->save();

                //Store receipt on disk.
                $receiptsStorage = $this->getHelper('bakerloo_restful/cli')->getPathToDb($order->getStoreId(), 'receipts', false);
                $contents = base64_decode($receiptData->content);
                $saved = false;

                if ($contents !== false) {
                    $saved = file_put_contents($receiptsStorage . DS . $receiptData->name, $contents);
                }

                if ($saved === false) {
                     $queue->setEmailResult(false)
                        ->setErrorMessage($helper->__("Receipt for order {$order->getId()} not saved. "))
                        ->save();
                }
            }
        }

        //Subscribe email to newsletter if indicated
        if ($subscribeToNewsletter) {
            $this->subscribeToNewsletter($customEmail);
        }

        $order->save();

        return $inserted;
    }

    public function logEmail($order, $emailType, $newsletterSubscription = null, $error = null, $emailTo = null)
    {
        $emailTo = is_null($emailTo) ? $order->getCustomerEmail() : $emailTo;

        $row = Mage::getModel('bakerloo_email/queue')
            ->setId(null)
            ->setOrderId($order->getId())
            ->setCustomerId($order->getCustomerId())
            ->setToEmail($emailTo)
            ->setEmailType($emailType)
            ->setSubscribeToNewsletter((int)$newsletterSubscription)
            ->setEmailResult(false)
            ->save();

        if (isset($error)) {
            $row->setEmailResult(false)
                ->setErrorMessage($error)
                ->save();
        }

        return $row;
    }

    public function insertUnsentEmail($order, $emailType, $customEmail = null)
    {

        $emailTo = is_null($customEmail) ? $order->getCustomerEmail() : $customEmail;

        $rows = Mage::getModel('bakerloo_email/unsent')
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq', $order->getId()))
            ->addFieldToFilter('to_email', array('eq', $emailTo));

        if ($rows->count() == 0) {
            $row = Mage::getModel('bakerloo_email/unsent')
                ->setId(null)
                ->setOrderId($order->getId())
                ->setCustomerId($order->getCustomerId())
                ->setToEmail($emailTo)
                ->setEmailType($emailType)
                ->save();
        } else {
            $row = $rows->getFirstItem();
            $row->setCustomerId($order->getCustomerId())
                ->setEmailType($emailType)
                ->save();
        }

        return $row;
    }

    public function subscribeToNewsletter($email)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);

        $subscriberCollection = Mage::getModel('newsletter/subscriber')->getCollection()
            ->addFieldToFilter('subscriber_email', array('eq' => $email));
        $duplicateSubscriber = current($subscriberCollection->getItems());

        if ($duplicateSubscriber !== false && !$duplicateSubscriber->getId()) {
            if ($customer->getId()) {
                $customer->setIsSubscribed(1);
                $customer->save();

                Mage::getModel('newsletter/subscriber')->subscribe($email);
                $subscribedCustomer = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                $subscribedCustomer->setCustomerId($customer->getId());
                $subscribedCustomer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $subscribedCustomer->save();
            }
        } else {
            Mage::getModel('newsletter/subscriber')->subscribe($email);
        }
    }

    /**
     * @param $email
     * @return mixed
     */
    public function customerExists($email)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        return $this->getHelper('bakerloo_restful/sales')->customerExists($email, $websiteId);
    }

    /**
     * @param $order
     * @param $newEmail
     * @return mixed
     */
    public function swapOrderEmail($order, $newEmail)
    {
        $validCustomEmail = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
        if ($validCustomEmail) {
            $order->setCustomerEmail($newEmail)->save();
        }
    }

    /**
     * @param $email
     * @param $order
     * @param $changedCustomer
     *
     * @return bool
     *
     * Adds a customer to Magento customers from supplied email
     */
    public function addCustomer($email, Mage_Sales_Model_Order $order, $changedCustomer = false)
    {
        $name = substr($email, 0, strpos($email, '@'));

        $customerData = array();
        $customerData['customer'] = array(
            'group_id'  => Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, Mage::app()->getStore()->getId()),
            'email'     => $email,
            'firstname' => $name,
            'lastname'  => $name
        );

        $websiteId = Mage::app()->getStore()->getWebsiteId();

        $newCustomer = $this->getHelper('bakerloo_restful')->createCustomer($websiteId, $customerData);
        //@TODO: Add addresses if not equal to store.

        $customerInOrderIsGuestOrDefault = $this->getHelper('bakerloo_restful/sales')->customerInOrderIsGuestOrDefault($order);

        //Associate customer to order.
        if ($newCustomer->getId() and $customerInOrderIsGuestOrDefault) {
            $this->setCustomerToOrder($newCustomer, $order);

            $this->getModel('bakerloo_restful/order')
                ->load($order->getId(), 'order_id')
                ->setUsesDefaultCustomer(0)
                ->save();

            $changedCustomer = true;
            unset($currentEmail);

            //Register flag for workaround in Magento version 1.8 or lower.
            Mage::register('pos_send_email_to', $newCustomer);
        }

        return $changedCustomer;
    }

    /**
     * Search orders by POS order number.
     *
     * @return array|Varien_Object
     */
    public function searchByPosOrderId()
    {

        $id = (int)$this->_getQueryParameter('id');

        $collection = Mage::getModel('bakerloo_restful/order')->getCollection();
        $collection->addFieldToFilter('id', $id);

        $order = new Varien_Object;
        if ($collection->getSize()) {
            $_order = $this->_createDataObject($collection->getFirstItem()->getOrderId());

            if (is_array($_order) and isset($_order['entity_id'])) {
                $order = $_order;
            }
        }

        return $order;
    }


    /**
     * @return int
     */
    public function processUnsentEmails()
    {
        //check email sending enabled
        $enabled = Mage::getStoreConfig('bakerloorestful/order_emails/enabled', Mage::app()->getStore());
        $sentEmails = 0;

        if ($enabled) {
            $unsentQueue = $this->getModel('bakerloo_email/unsent')->getCollection();

            foreach ($unsentQueue as $unsentEmail) {
                $emailType = $unsentEmail->getEmailType();

                $orderId = (int)$unsentEmail->getOrderId();

                /* @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->load($orderId);
                if (!$order->getId()) {
                    continue;
                }

                //swap order email if different from unsent email address
                $orderEmailAddress = $order->getCustomerEmail();
                $unsentEmailAddress = $unsentEmail->getToEmail();
                if (strcmp($unsentEmailAddress, $orderEmailAddress) != 0) {
                    $this->swapOrderEmail($order, $unsentEmailAddress);
                }

                $receiptsStorage = $this->getHelper('bakerloo_restful/cli')->getPathToDb($order->getStoreId(), 'receipts', false);
                $fullPath = $receiptsStorage . DS . $unsentEmail->getAttachment();
                $contents = file_get_contents($fullPath);
                $attachment = new stdClass();

                if ($contents !== false) {
                    $attachment->name = $unsentEmail->getAttachment();
                    $attachment->content = base64_encode($contents);
                    $attachment->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath);
                }

                $emailSent = false;
                try {
                    if ($emailType == 'magento') {
                        $order->sendNewOrderEmail();
                        $emailSent = (bool)$order->getEmailSent();
                    } elseif ($emailType == 'receipt') {
                        $receipt = $this->getHelper('bakerloo_restful/email')->sendReceipt($order, $attachment);
                        $emailSent = (bool)$receipt->getEmailSent();
                    } else {
                        $order->sendNewOrderEmail();
                        $receipt = $this->getHelper('bakerloo_restful/email')->sendReceipt($order, $attachment);
                        $emailSent = (bool)($order->getEmailSent() or $receipt->getEmailSent());
                    }

                    if ($emailSent) {
                        $this->updateEmailStatus($order, true);
                        $unsentEmail->delete();
                        $sentEmails++;

                    } else {
                        $this->updateEmailStatus($order, false);
                    }
                } catch (Exception $e) {
                    Mage::logException($e);

                    //Add row to email log reflecting failed attempt
                    $this->logEmail($order, $emailType, null, $e->getMessage());
                }

                //reset old order email
                $this->swapOrderEmail($order, $orderEmailAddress);
            }
        }

        return $sentEmails;
    }

    public function updateEmailStatus($order, $status)
    {

        //Add comment to order.
        if ($status) {
            $order->addStatusHistoryComment($this->getHelper('bakerloo_restful')->__("Order email sent to email address: \"%s\"", $order->getCustomerEmail()), false)
                ->setIsVisibleOnFront(false)
                ->setIsCustomerNotified(false)
                ->save();
        }

        //Set send in corresponding queue record
        $queuedEmails = Mage::getModel('bakerloo_email/queue')->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $order->getId()));

        foreach ($queuedEmails as $queuedEmail) {
            $queuedEmail->setEmailResult($status);

            if ($status) {
                $queuedEmail->setErrorMessage('');
                $queuedEmail->setDeleteAttachment(1);
            }

            $queuedEmail->save();
        }
    }

    /**
     * PUT - Update original order JSON.
     */
    public function updateOrigOrder()
    {

        $id = (int)$this->_getQueryParameter('id');

        $posOrder = Mage::getModel('bakerloo_restful/order')->load($id);

        if (!$posOrder->getId()) {
            Mage::throwException('Order does not exist.');
        }

        $jsonObj = $this->getJsonPayload();

        $posOrder->setJsonPayload(json_encode($jsonObj));

        $posOrder->save();

        return $this->_createDataObject($posOrder->getOrderId());
    }

    /**
     * Return ready to pickup orders.
     *
     * @return array
     */
    public function readyToPickup()
    {
        $this->_pickUpSearch = true;
        
        //get page
        $page = $this->_getQueryParameter('page');
        if (!$page) {
            $page = 1;
        }

        //Retrieve orders not completed and placed with our shipping method.
        $myFilters = array(
            'shipping_method,eq,bakerloo_store_pickup_bakerloo_store_pickup',
            'state,neq,complete',
            'state,neq,closed',
            'total_paid,notnull,',
        );

        $filters = $this->_getQueryParameter('filters');

        if (is_null($filters)) {
            $filters = $myFilters;
        } else {
            $filters = array_merge($filters, $myFilters);
        }

        $this->_filterUseOR = false;

        return $this->_getAllItems($page, $filters);
    }

    public function setCustomerToOrder($customer, $order)
    {
        $order->setData('customer_id', $customer->getId());
        $order->setData('customer_is_guest', 0);
        $order->setData('customer_email', $customer->getEmail());
        $order->setData('customer_firstname', $customer->getFirstname());
        $order->setData('customer_lastname', $customer->getLastname());
        $order->setData('customer_group_id', $customer->getGroupId());
    }

    /**
     * @param $order
     * @return array
     */
    protected function getInvoiceAndShipmentConfig($order)
    {
        $invoiceConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("invoice");
        $shipmentConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("ship");

        if ($order->getPayment()->getMethod() == 'free') {
            $invoiceConfig = (int)Mage::getStoreConfig('payment/bakerloo_free/invoice', $this->getStore());
            $shipmentConfig = (int)Mage::getStoreConfig('payment/bakerloo_free/ship', $this->getStore());
        } elseif ($order->getPayment()->getMethod() == 'bakerloo_layaway') {
            $invoiceConfig = 0;
            $shipmentConfig = 0;
        }

        return array($invoiceConfig, $shipmentConfig);
    }
}
