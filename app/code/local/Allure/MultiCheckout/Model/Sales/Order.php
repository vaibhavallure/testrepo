<?php

class Allure_MultiCheckout_Model_Sales_Order extends Mage_Sales_Model_Order // Webtex_Giftcards_Model_Sales_Order
{

    const XML_MULTIORDER_EMAIL_TEMPLATE = 'sales_email/allure_multicheckout_sales_email/template';

    const XML_MULTIORDER_EMAIL_GUEST_TEMPLATE = 'sales_email/allure_multicheckout_sales_email/guest_template';

    /*
     * Send email to customer that contains In stock and
     * Out of stock products information.
     * i.e. two orders in this email.
     */
    public function queueNewOrderSplitEmail ($outOfStockOrderId, $forceMode = false)
    {
        $storeId = $this->getStore()->getId();
        
        // Mage::log($this->getId(),Zend_log::DEBUG,'abc',true);
        Mage::log($outOfStockOrderId,Zend_log::DEBUG,'abc',true);
        
        $outOfStockOrder = Mage::getModel('sales/order')->load($outOfStockOrderId);
        
        if (! Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }
        
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        
        // Start store emulation process
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        
        try {
            // Retrieve specified view block from appropriate design package
            // (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
            $paymentBlockHtml_second = $paymentBlockHtml;
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }
        
        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        
        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_MULTIORDER_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_MULTIORDER_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }
        
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);
        
        // Email copies are sent as separated emails if their copy method is
        // 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }
        
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
                array(
                        'order' => $this,
                        'secondOrder' => $outOfStockOrder,
                        'billing' => $this->getBillingAddress(),
                        'payment_html' => $paymentBlockHtml,
                        'paymentBlockHtml_second' => $paymentBlockHtml_second,
                        'highlighted' => $this->isHighlighted(),
                        'signature' => $this->isSignatureRequire()
                ));
        
        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(! $forceMode);
        
        $mailer->setQueue($emailQueue)->send();
        
        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');
        
        // out of stock order email sent status update.
        $outOfStockOrder->setEmailSent(true)->save();
        return $this;
    }
    
    
    /*
     * Send email to customer that contains In stock and
     * Out of stock products information.
     * i.e. two orders in this email.
     */
    public function queueMultiAddressNewOrderEmail ($orderArray,$forceMode = false)
    {
        if(!count($orderArray)){
            return $this;    
        }
        
        $storeId = $this->getStore()->getId();
        
        $orderNumberArray = array();
        $orderCollection = new Varien_Data_Collection();
        foreach ($orderArray as $order){
            $orderNumberArray[] = "#".$order->getIncrementId();
            $orderCollection->addItem($order);
        }
        $orderNumber = implode(" ", $orderNumberArray);
        
        if (! Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }
        
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        
        // Start store emulation process
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        
        try {
            // Retrieve specified view block from appropriate design package
            // (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
            ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }
        
        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        
        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_MULTIORDER_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_MULTIORDER_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }
        
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);
        
        // Email copies are sent as separated emails if their copy method is
        // 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }
        
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
                'order' => $this,
                'order_number' => $orderNumber,
                'multi_order' => $orderCollection,
                'billing' => $this->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'highlighted' => $this->isHighlighted(),
                'signature' => $this->isSignatureRequire()
            ));
        
        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
        ->setEntityType(self::ENTITY)
        ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
        ->setIsForceCheck(! $forceMode);
        
        $mailer->setQueue($emailQueue)->send();
        
        $this->setEmailSent(true);
        foreach ($orderArray as $order){
            $order->setEmailSent(true);
            $this->_getResource()->saveAttribute($order, 'email_sent');
        }
        
        return $this;
    }
    
}
