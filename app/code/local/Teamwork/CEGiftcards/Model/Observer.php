<?php

class Teamwork_CEGiftcards_Model_Observer //extends Mage_Core_Model_Abstract
{

    const MAX_ATTEMPTS_GENERATE = 3;

    public function quotesubmitbefore($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $gcs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                   ->getCollection()
               ->addQuoteFilter($quote);
        if ($gcs->count()) {
            $svs = Mage::getSingleton('teamwork_cegiftcards/svs');
            $wrongGCs = array();
            try {
                foreach($gcs as $gc) {
                    if ($gc->getData('amount')) {
                        $gcData = $svs->getGiftcardData($gc->getData('gc_code'), $gc->getData('gc_pin'));
                        if (!$gcData['active'] || $gcData['giftcard_balance'] != $gc->getData('balance')){
                            $wrongGCs[] = $gc->getData('gc_code');
                        }
                    }
                }
                if (!$wrongGCs) {
                    foreach($gcs as $gc) {
                        if ($gc->getData('amount')/* && !$gc->getData('paid')*/) {
                            $amount = Teamwork_CEGiftcards_Model_Svs::negative($gc->getData('amount'));
                            $this->_gcTransaction($gc, $amount, true, array('paid' => true));
                        }
                    }
                }
            } catch (Teamwork_CEGiftcards_Model_Exception $e) {
                if ($e->isVisibleOnFrontend()) {
                    $msg = $e->getMessage();
                } else {
                    $msg = Mage::helper('teamwork_cegiftcards')->__('Internal error occured. Please try later.');
                }
                Mage::throwException($msg);
            }
            if ($wrongGCs) {
                Mage::throwException(Mage::helper('teamwork_cegiftcards')->__('Balance of the following gift card(s) have been changed: %s. Please try again.', implode(" , ", $wrongGCs)));
            }

        }
    }

    public function quotesubmitfailure($observer)
    {
        $quote = $observer->getEvent()->getQuote();

        // refund giftcards used to pay for order
        $gcs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                   ->getCollection()
               ->addQuoteFilter($quote);
        if ($gcs->count()) {
            foreach($gcs as $gc) {
                if ($gc->getData('paid')) {
                    $this->_gcTransaction($gc, $gc->getData('amount'), true, array('paid' => false), true);
                }
            }
        }

        Mage::register('teamwork_order_creation_failed', $quote->getId());
    }

    public function quotesubmitsuccess($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        $gcs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                   ->getCollection()
               ->addQuoteFilter($quote);
        if ($gcs->count()) {
            foreach($gcs as $gc) {
                $gc->setData('order_id', $order->getId());
                $gc->save();
            }
        }
        $this->checkAndCreateInvoice($observer);
    }

    public function forceAutorizeCapture($observer)
    {
        if( Mage::getStoreConfig(Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture::CONFIG_PATH_VIRTUAL_CAPTURE) == Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture::FORCE_PAYMENT )
        {
            $payment = $observer->getEvent()->getPayment();
            $invoiceHelper = Mage::helper('teamwork_cegiftcards/invoice');
            
            $storeId = Mage::app()->getStore()->getStoreId();
            if( empty($storeId) )
            {
                $storeId = $payment->getOrder()->getStoreId();
            }
            
            $channelId = $invoiceHelper->getChannelIdByStoreId( $storeId );
            if(
                $channelId &&
                $invoiceHelper->allowAuthorizeOnlyPayment($payment->getMethod(), $channelId) &&
                $invoiceHelper->checkVirtualProductInOrder( $payment->getOrder() )
            )
            {
                $methodInstance = $payment->getMethodInstance();
                $payment->setMethodInstance( Mage::getModel('teamwork_cegiftcards/order_payment', $methodInstance) );
            }
        }
    }

    public function checkAndCreateInvoice($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if( Mage::getStoreConfig(Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture::CONFIG_PATH_VIRTUAL_CAPTURE) == Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture::CREATE_INVOICE )
        {
            $invoiceHelper = Mage::helper('teamwork_cegiftcards/invoice');
            
            $storeId = Mage::app()->getStore()->getStoreId();
            if( empty($storeId) )
            {
                $storeId = $order->getStoreId();
            }

            $channelId = $invoiceHelper->getChannelIdByStoreId( $storeId );

            $payment = $observer->getEvent()->getPayment();
            if( empty($payment) )
            {
                $payment = $observer->getEvent()->getQuote()->getPayment();
            }

            if(
                $channelId &&
                $invoiceHelper->allowAuthorizeOnlyPayment($payment->getMethod(), $channelId) && !$order->hasInvoices() &&
                $order->canInvoice() &&
                $invoiceHelper->checkVirtualProductInOrder($order)
            )
            {
                $invoiceHelper->createInvoice($order);
            }
        }
    }

    public function invoicesaveafter($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->hasData('__teamwork_cegiftcards_invoice_links')) {
            $usedGCs = $invoice->getData('__teamwork_cegiftcards_invoice_links');
            foreach($usedGCs as $link) {
                $link->setData('invoice_id', $invoice->getId());
                $link->save();
            }
        }
    }

    public function creditmemosaveafter($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        // We refund all order giftcards when creating credit memo if:
        if (// there're some giftcards to refund
            $creditmemo->hasData('__teamwork_cegiftcards_creditmemo_links')
            // and top-level flag "refund giftcards at all" is set
            && Mage::getStoreConfigFlag(Teamwork_CEGiftcards_Helper_Data::XML_PATH_REFUND_GIFTCARDS)
            // and Transfer module didn't set special flag that we shouldn't refund giftcard (it should set this flag when handling 'Modified' order status from CHQ)
            && !$creditmemo->getSkipRefundGiftcards()
            ) {

            $usedGCs = $creditmemo->getData('__teamwork_cegiftcards_creditmemo_links');
            foreach($usedGCs as $link) {
                $link->setData('creditmemo_id', $creditmemo->getId());
                $link->save();
                $gc = Mage::getModel('teamwork_cegiftcards/giftcard_link')->load($link->getData('gc_link_id'));
                $this->_gcTransaction($gc, $link->getData('amount_used'), true, array(), true);
            }
        }
    }

    protected function _gcTransaction($gcLink, $amount, $saveTransactionId = true, $updateData = array(), $force = false)
    {
        try {
            $svs = Mage::getSingleton('teamwork_cegiftcards/svs');
            $transactionId = $svs->sale($gcLink->getData('gc_code'), $gcLink->getData('gc_pin'), $amount, $force);
            $doSave = $saveTransactionId;
            if ($updateData) {
                $gcLink->addData($updateData);
                $doSave = true;
            }
            if ($saveTransactionId) {
                $gcLink->setData('transactions', array($transactionId => $gcLink->getData('amount')));
            }
            if ($doSave) {
                $gcLink->save();
            }
        } catch (Teamwork_CEGiftcards_Model_Exception $e) {
            if ($e->isVisibleOnFrontend()) {
                $msg = $e->getMessage();
            } else {
                $msg = Mage::helper('teamwork_cegiftcards')->__('Internal error occured.');
            }
            Mage::throwException($msg);
        }
    }

    public function salesOrderLoadAfter(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfigFlag(Teamwork_CEGiftcards_Helper_Data::XML_PATH_REFUND_GIFTCARDS)) {

            $order = $observer->getEvent()->getOrder();

            if ($order->canUnhold() || $order->isPaymentReview()) {
                return $this;
            }

            if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED ) {
                return $this;
            }

            $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
            $mayReturn = 0;
            foreach($appliedGCs as $appliedGC) {
                $invoiced = 0;
                $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
                foreach ($invoiceLinkCollection as $record){
                    $invoiced += $record->getData('amount_used');
                }
                if ($invoiced) {
                    $returned = 0;
                    $creditmemoriedLinkCollection = Mage::getModel('teamwork_cegiftcards/order_creditmemo_link')->getCollection()->addGCLinkFilter($appliedGC);
                    foreach ($creditmemoriedLinkCollection as $record){
                        $returned += $record->getData('amount_used');
                    }
                    $mayReturn += $invoiced - $returned;
                }
            }
            if ($mayReturn >= 0.0001) {
                $order->setForcedCanCreditmemo(true);
            }
        }

        return $this;
    }


    public function appendGiftcardAdditionalData(Varien_Event_Observer $observer)
    {
        //sales_convert_quote_item_to_order_item

        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $keys = array(
            'giftcard_sender_name',
            'giftcard_sender_email',
            'giftcard_recipient_name',
            'giftcard_recipient_email',
            'giftcard_message',
            'giftcard_amount',
        );
        $productOptions = $orderItem->getProductOptions();
        foreach ($keys as $key) {
            if ($option = $quoteItem->getProduct()->getCustomOption($key)) {
                $productOptions[$key] = $option->getValue();
            }
        }

        $product = $quoteItem->getProduct();
        $productOptions['giftcard_type'] = $product->getGiftcardType();

        $orderItem->setProductOptions($productOptions);

        return $this;
    }


    public function generateGiftCards(Varien_Event_Observer $observer)
    {
	    // When order creation failed, prevent GC generation
        if ($quoteFailedId = Mage::registry('teamwork_order_creation_failed')) {
            Mage::helper('teamwork_cegiftcards/log')->addMessage("Order creation from quote {$quoteFailedId} failed => giftcards will NOT be generated");
            Mage::unregister('teamwork_order_creation_failed');
            return;
        }

        $order = $observer->getEvent()->getOrder();
        $loadedInvoices = array();
        foreach ($order->getAllItems() as $item) {
            $doProcess = false;
            if ($item->getProductType() == Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                $options = $item->getProductOptions();
                if (array_key_exists('giftcard_type', $options)
                    && $options['giftcard_type'] != Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL) {
                        $doProcess = true;
                }
            }
            if ($doProcess) {
                /*generate codes*/
                $options = $item->getProductOptions();
                $qty = $item->getQtyOrdered();
                if (isset($options['giftcard_created_codes'])) {
                    $qty -= count($options['giftcard_created_codes']);
                }

                $amount = $item->getBasePrice();

                if ($qty > 0) {
                    $product = $item->getProduct();
                    $lifetime = 0;
                    if ($option = $product->getData('giftcard_lifetime')) {
                        $lifetime = $option;
                    }


                    $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

                    $data = new Varien_Object();
                    $data->setWebsiteId($websiteId)
                        ->setAmount($amount)
                        ->setLifetime($lifetime);

                    $codes = (isset($options['giftcard_created_codes']) ?
                                $options['giftcard_created_codes'] : array());

                    $pinIsEnabled = Mage::helper('teamwork_cegiftcards')->pinIsEnabled();
                    $pinGenIsEnabled = Mage::helper('teamwork_cegiftcards')->pinGenerationIsEnabled();
                    for ($i = 0; $i < $qty; $i++) {
                        try {
                            $res = $this->_generateGC($data);
                            if ($pinIsEnabled && $pinGenIsEnabled) {
                                $codes[$res['code']] = $res['code'] . " (PIN: " . $res['pin'] . ")";
                            } else {
                                $codes[$res['code']] = $res['code'];
                            }
                        } catch (Teamwork_CEGiftcards_Model_Exception $e) {
                            $hasFailedCodes = true;
                            $message = Mage::helper('teamwork_cegiftcards')->__('Some of Gift Card were not generated properly.');
                            Mage::getSingleton('adminhtml/session')->addError($message);
                            Mage::helper('teamwork_cegiftcards/log')->addMessage("Error occured while gc code generation: order id: {$order->getId()}; order increment id: {$order->getIncrementId()}; order item id: {$item->getId()} (product id:{$product->getId()})");
                            Mage::helper('teamwork_cegiftcards/log')->addException($e);
                            //throw $e;
                        }
                    }
                    $options['giftcard_created_codes'] = $codes;


                    $barcodes = (isset($options['giftcard_barcodes']) ? $options['giftcard_barcodes'] : array());
                    $generatedBarcodes = ($data->getData("barcodes") ? $data->getData("barcodes") : array());

                    $options['giftcard_barcodes'] = array_merge($barcodes, $generatedBarcodes);


                    $item->setProductOptions($options);
                    $item->save();


                }
                //check invoiced gcs and send if needed
                if ($item->getProductOptionByCode('giftcard_type') != Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL) {

                    $sentCodes = (isset($options['giftcard_sent_codes'])
                                        ? $options['giftcard_sent_codes']
                                        : array());
                    $sentNum = count($sentCodes);

                    $generatedCodes = (isset($options['giftcard_created_codes']) ?
                                        $options['giftcard_created_codes'] : array());


                    if ($sentNum < count($generatedCodes)) {

                        $paidQty = 0;
                        $invoiceItemCollection = Mage::getResourceModel('sales/order_invoice_item_collection')
                                                    ->addFieldToFilter('order_item_id', $item->getId());

                        foreach ($invoiceItemCollection as $invoiceItem) {
                            $invoiceId = $invoiceItem->getParentId();
                            if(isset($loadedInvoices[$invoiceId])) {
                                $invoice = $loadedInvoices[$invoiceId];
                            } else {
                                $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
                                $loadedInvoices[$invoiceId] = $invoice;
                            }
                            // check, if this order item has been paid
                            if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID) {
                                $paidQty += $invoiceItem->getQty();
                            }
                        }
                        $needSendNum = $paidQty - $sentNum;
                        if ($needSendNum > 0) {

                            $counter = 0;

                            $generatedBarcodes = (isset($options['giftcard_barcodes']) ? $options['giftcard_barcodes'] : array());
                            $willSendGCInfo = array();

                            foreach(array_diff(array_keys($generatedCodes), $sentCodes) as $gcCode) {
                                $willSendGCInfo[$gcCode] = array('label' => $generatedCodes[$gcCode]);
                                if (array_key_exists($gcCode, $generatedBarcodes)) {
                                    $willSendGCInfo[$gcCode]['barcode_link'] = Mage::getBaseUrl('media') . str_replace(DS, '/', $generatedBarcodes[$gcCode]);
                                } else {
                                    $willSendGCInfo[$gcCode]['barcode_link'] = "";
                                }
                                $counter++;
                                if ($counter >= $needSendNum) break;
                            }

                            $sender = $item->getProductOptionByCode('giftcard_sender_name');
                            $senderName = $item->getProductOptionByCode('giftcard_sender_name');
                            if ($senderEmail = $item->getProductOptionByCode('giftcard_sender_email')) {
                                $sender = "$sender <$senderEmail>";
                            }

                            $className = Mage::getConfig()->getBlockClassName('core/template');
                            $codeListBlock = new $className;
                            if (Mage::app()->getStore()->isAdmin()) {
                                $codeListBlock->setArea('adminhtml');
                            }
                            $codeListBlock->setTemplate('teamwork_cegiftcards/email/generated.phtml');
                            //$codeListBlock->setCodesWBarcodes($willSendBarcodes);
                            $codeListBlock->setCodes(array_keys($willSendGCInfo));
                            $codeListBlock->setGcInfo($willSendGCInfo);
                            $codeListBlock->unsetData('cache_lifetime');

                            $balance = Mage::app()->getLocale()->currency(
                                                Mage::app()->getStore($order->getStoreId())
                                                    ->getBaseCurrencyCode())->toCurrency($amount);

                            $templateData = array(
                                'name'                   => $item->getProductOptionByCode('giftcard_recipient_name'),
                                'email'                  => $item->getProductOptionByCode('giftcard_recipient_email'),
                                'sender_name_with_email' => $sender,
                                'sender_name'            => $senderName,
                                'gift_message'           => $item->getProductOptionByCode('giftcard_message'),
                                'giftcards'              => $codeListBlock->toHtml(),
                                'balance'                => $balance,
                                'is_multiple_codes'      => 1 < count($willSendGCInfo),
                                'store'                  => $order->getStore(),
                                'store_name'             => $order->getStore()->getName(),
                            );

                            $email = Mage::getModel('core/email_template')
                                        ->setDesignConfig(array('store' => $item->getOrder()->getStoreId()));

                            $product = $item->getProduct();
                            if ($product->getData('giftcard_email_template_uc')) {
                                $emailTemplate = Mage::getStoreConfig(
                                                    Teamwork_CEGiftcards_Helper_Data::XML_PATH_EMAIL_TEMPLATE,
                                                    $item->getOrder()->getStoreId());
                            } else {
                                $emailTemplate = $product->getData('giftcard_email_template');
                            }
                            $email->addBcc($this->_getCopyTo($item))->sendTransactional(
                                $emailTemplate,
                                Mage::getStoreConfig(
                                Teamwork_CEGiftcards_Helper_Data::XML_PATH_EMAIL_IDENTITY,
                                $item->getOrder()->getStoreId()),
                                array($item->getProductOptionByCode('giftcard_recipient_email')),
                                array($item->getProductOptionByCode('giftcard_recipient_name')),
                                $templateData
                            );

                            if ($email->getSentSuccess()) {
                                $options['email_sent'] = 1;
                            }

                            $sentCodes = array_merge($sentCodes, array_keys($willSendGCInfo));
                            $options['giftcard_sent_codes'] = $sentCodes;
                        }

                    }


                }

                $item->setProductOptions($options);
                $item->save();

            }
        }
    }

    protected function _generateGC($data)
    {
        $code = $this->_generateCode($data);
        if (Mage::helper('teamwork_cegiftcards')->pinIsEnabled()
            && Mage::helper('teamwork_cegiftcards')->pinGenerationIsEnabled()) {
            $pin = $this->_generatePin($data);
        } else {
            $pin = false;
        }
        $svs = Mage::getSingleton('teamwork_cegiftcards/svs');
        $svs->setWebsiteId($data->getWebsiteId());
        try {
            $counter = 0;
            while($counter < self::MAX_ATTEMPTS_GENERATE) {
                try{
                    $lifetime = null;
                    if ($data->getLifetime()) {
                        $lifetime = strtotime("now +{$data->getLifetime()}days");
                    }
                    $gcData = $svs->create($code, $pin, $data->getAmount(), $lifetime);
                    //check/save barcode
                    if (is_array($gcData) && array_key_exists("giftcard", $gcData)
                        && is_array($gcData["giftcard"]) && array_key_exists("barcode_url", $gcData["giftcard"])) {
                            if ($filePath = Mage::helper('teamwork_cegiftcards')->saveBarCodeImage($code, $gcData["giftcard"]["barcode_url"])) {
                                $barcodes = $data->getData("barcodes");
                                if (empty($barcodes)) {
                                    $barcodes = array();
                                }
                                $barcodes[$code] = $filePath;
                                $data->setData("barcodes", $barcodes);
                            }
                    }
                    break;
                } catch (Teamwork_CEGiftcards_Model_Exception $e) {
                    $exCode = $e->getCode();
                    if ($e->getCode() !== Teamwork_CEGiftcards_Model_Svs::SVS_ERROR_CODE_GIFTCARD_ALREADY_EXISTS) {
                        throw $e;
                    }
                }
                $code = $this->_generateCode($data);
                $counter++;
            }
            if ($counter >= self::MAX_ATTEMPTS_GENERATE) {
                $e = new Teamwork_CEGiftcards_Model_Exception_Svs_Response("Error occurred while giftcard generating: there were {$counter} attempts but the all positions are occuped. Please try later.");
                $e->isVisibleOnFrontend(false);
                throw $e;
            }
        } catch (Teamwork_CEGiftcards_Model_Exception $e) {
            if (!$e->isVisibleOnFrontend()) throw Mage::exception("Mage_Core", "Some error occurred while generating giftcard. Please try later.");
            throw $e;
        }

        $result = array(
            'code' => $code,
        );
        if (Mage::helper('teamwork_cegiftcards')->pinIsEnabled()) {
            $result['pin'] = $pin;
        }
        return $result;

    }

    protected function _generateCode($data)
    {
        $website = Mage::app()->getWebsite($data->getWebsiteId());

        $format  = $website->getConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_CODE_FORMAT);

        $length  = max(1, (int) $website->getConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_CODE_LENGTH));
        $split   = max(0, (int) $website->getConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_CODE_SPLIT));
        $suffix  = trim($website->getConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_CODE_SUFFIX));
        $prefix  = trim($website->getConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_CODE_PREFIX));

        $splitChar = (string) Mage::app()->getConfig()->getNode(Teamwork_CEGiftcards_Helper_Data::XML_PATH_SEPARATOR);
        $charset = str_split((string) Mage::app()->getConfig()->getNode(sprintf(Teamwork_CEGiftcards_Helper_Data::XML_CHARSET_NODE, $format)));

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $char = $charset[array_rand($charset)];
            if ($split > 0 && ($i%$split) == 0 && $i != 0) {
                $char = "{$splitChar}{$char}";
            }
            $code .= $char;
        }

        $code = "{$prefix}{$code}{$suffix}";
        return $code;
    }

    protected function _generatePin($data)
    {
        $website = Mage::app()->getWebsite($data->getWebsiteId());

        $length  = max(1, (int) $website->getConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_PIN_LENGHT));

        $charset = str_split((string) Mage::app()->getConfig()->getNode(Teamwork_CEGiftcards_Helper_Data::XML_CFG_PATH_PIN_CHARACTERS));

        $pin = '';
        for ($i=0; $i<$length; $i++) {
            $pin .= $charset[array_rand($charset)];
        }

        return $pin;
    }

    public function addPaypalGiftCardItem(Varien_Event_Observer $observer)
    {
        $paypalCart = $observer->getEvent()->getPaypalCart();

        $object = $paypalCart->getSalesEntity();

        $quoteId = null;
        $storeId = null;

        /*check whether GC is in use and get amount*/
        if ($object instanceof Mage_Sales_Model_Quote)
        {
            $quoteId = $object->getId();
            $storeId = $object->getStoreId();
        }
        else if ($object instanceof Mage_Sales_Model_Order)
        {
            $quoteId = $object->getQuoteId();
            $storeId = $object->getStoreId();
        }

        if ($quoteId) {
            $gcs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                   ->getCollection()
               ->addQuoteFilter($quoteId);
            $amount = 0;
            foreach($gcs as $gc) {
                $amount += $gc->getData('base_amount');
            }
            if ($amount > 0.0001) {
                $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, $amount,
                    Mage::helper('teamwork_cegiftcards')->__('Gift Card(s) (%s)', Mage::app()->getStore($storeId)->convertPrice($amount, true, false))
                );
            }
        }
    }


    public function processOrderCreationData(Varien_Event_Observer $observer)
    {
        $model = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote = $model->getQuote();
        if (isset($request['giftcard_add'])) {
            $code = $request['giftcard_add'];
            $helper = Mage::helper('teamwork_cegiftcards');
            try {
			    $gcPin = false; // stub
                $helper->sessionMsgsOut(Mage::getSingleton('adminhtml/session_quote'), $helper->applyGC2Quote($quote, $code, $gcPin));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addException(
                    $e, Mage::helper('teamwork_cegiftcards')->__('Cannot apply Gift Card')
                );
            }
        }

        if (isset($request['giftcard_remove'])) {
            $code = $request['giftcard_remove'];
            $helper = Mage::helper('teamwork_cegiftcards');
            try {
                $helper->sessionMsgsOut(Mage::getSingleton('adminhtml/session_quote'), $helper->removeGCFromQuote($quote, $code));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addException(
                    $e, Mage::helper('teamwork_cegiftcards')->__('Cannot remove Gift Card')
                );
            }
        }
        return $this;
    }

    /*adminhtml_catalog_product_edit_prepare_form event handler*/
    public function adminHtmlAddAmountRenderer(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $amount = $form->getElement('giftcard_amount');
        if ($amount) {
            $amount->setRenderer(
                Mage::app()->getLayout()->createBlock('teamwork_cegiftcards/adminhtml_catalog_product_edit_tab_price_amount')
            );
        }
    }

    public function mergeQuotes(Varien_Event_Observer $observer)
    {
        $customerQuote = $observer->getEvent()->getQuote();
        $guestQuote = $observer->getEvent()->getSource();
        if ($customerQuote instanceof Mage_Sales_Model_Quote
            && $guestQuote instanceof Mage_Sales_Model_Quote
            && $customerQuote->getId()
            && $guestQuote->getId()) {
                //get customer GCs list
                $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                                ->getCollection()
                              ->addQuoteFilter($customerQuote);
                $customersGCs = array();
                foreach($appliedGCs as $appliedGC) {
                    $customersGCs[] = $appliedGC->getData('gc_code');
                }
                //get guest GCs list
                $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                                ->getCollection()
                              ->addQuoteFilter($guestQuote);
                $guestGCs = array();
                foreach($appliedGCs as $appliedGC) {
                    $guestGCs[] = $appliedGC->getData('gc_code');
                }

                $helper = Mage::helper('teamwork_cegiftcards');
                $session = Mage::getSingleton('checkout/session');
                //remove the all GCs from customer's quote
                if (count($customersGCs)) {
                    foreach($customersGCs as $customersGC) {
                        $result = $helper->removeGCFromQuote($customerQuote, $customersGC);
                        //session error msgs
                        $helper->sessionMsgsOut($session, array('error_msgs' => $result['error_msgs']));
                    }
                }
                //add guest's + customer's GCs
                $customersGCs = array_merge($customersGCs, $guestGCs);
                if (count($customersGCs)) {
                    $customersGCs = array_unique($customersGCs);
                    foreach($customersGCs as $customersGC) {
                        $gcPin = false; // stub
                        $result = $helper->applyGC2Quote($customerQuote, $customersGC, $gcPin);
                        //session error msgs
                        $helper->sessionMsgsOut($session, array('error_msgs' => $result['error_msgs']));
                    }
                }
        }
    }

    public function transferProductLoaded($observer)
    {
        $params = $observer->getEvent()->getParams();
        if ($params->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            && $params->getData('product')->getTypeId() == Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                $params->setData('type_id', null);
        }
    }

    /**
     * Get array of emails from email_copy_to parameter
     */
    protected function _getCopyTo($giftcardOrdered)
    {
        $storeId = $giftcardOrdered->getOrder()->getStoreId();
        $result  = array();

        $bccEmails = Mage::getStoreConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_EMAIL_COPY_TO, $storeId);
        if (!empty($bccEmails)) {
            $result = array_map('trim', explode(',', $bccEmails));
        }

        if(Mage::getStoreConfigFlag(Teamwork_CEGiftcards_Helper_Data::XML_PATH_EMAIL_COPY_TO_GC_SENDER, $storeId)) {
            $result[] = $giftcardOrdered->getProductOptionByCode('giftcard_sender_email');
        }

        return $result;
    }

    public function refundGiftcardsAfterCancelOrder($observer)
    {
        if (Mage::getStoreConfigFlag(Teamwork_CEGiftcards_Helper_Data::XML_PATH_REFUND_GIFTCARDS)) {
            $order = $observer->getEvent()->getOrder();
            $originalState = $order->getOrigData('state');
            $newState = $order->getData('state');

            // if order status was changed to 'canceled', refund all GC's
            if (($newState != $originalState) && ($newState == Mage_Sales_Model_Order::STATE_CANCELED)) {
                $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
                foreach ($appliedGCs as $giftcard) {
                    if ($giftcard->getPaid()) {
                        $this->_gcTransaction($giftcard, $giftcard->getData('amount'), true, array(), true);
                    }
                }
            }
        }
    }
}
