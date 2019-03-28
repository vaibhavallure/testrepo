<?php

class Ebizmarts_BakerlooRestful_Helper_Email extends Mage_Core_Helper_Abstract
{

    const XML_PATH_RECEIPT_EMAIL_IDENTITY  = 'bakerloorestful/pos_receipt/identity';
    const XML_PATH_RECEIPT_EMAIL_TEMPLATE  = 'bakerloorestful/pos_receipt/template';

    const XML_PATH_COUPON_EMAIL_IDENTITY  = 'bakerloorestful/pos_coupon/identity';
    const XML_PATH_COUPON_EMAIL_TEMPLATE  = 'bakerloorestful/pos_coupon/template';

    const XML_PATH_WELCOME_EMAIL_IDENTITY               = 'bakerloorestful/new_customer_account/identity';
    const XML_PATH_WELCOME_EMAIL_TEMPLATE               = 'bakerloorestful/new_customer_account/template';
    const XML_PATH_WELCOME_CONFIRMATION_EMAIL_TEMPLATE  = 'bakerloorestful/new_customer_account/confirmation_template';

    const PRICE_OVERRIDE_EMAIL_TEMPLATE = 'bakerloorestful_pos_customprice_template';
    const XML_PATH_PRODUCT_SHARE_TEMPLATE = 'share_product_email/template';

    private $_emailSent = false;

    /** @var Ebizmarts_BakerlooRestful_Helper_Data  */
    private $_helper;

    /** @var Mage_Core_Model_Email_Template  */
    private $_template;

    /** @var Mage_Core_Model_Email_Info */
    private $_emailInfo;

    public function __construct(
        Ebizmarts_BakerlooRestful_Helper_Data $helper = null,
        Mage_Core_Model_Email_Template $template = null,
        Mage_Core_Model_Email_Info $info = null
    )
    {
        if (is_null($helper)) {
            $this->_helper = Mage::helper('bakerloo_restful');
        } else {
            $this->_helper = $helper;
        }

        if (is_null($template)) {
            $this->_template = Mage::getModel('core/email_template');
        } else {
            $this->_template = $template;
        }

        if (is_null($info)) {
            $this->_emailInfo = Mage::getModel('core/email_info');
        } else {
            $this->_emailInfo = $info;
        }
    }

    public function setEmailSent($bool)
    {
        $this->_emailSent = $bool;
    }

    public function getEmailSent()
    {
        return $this->_emailSent;
    }

    /**
     * Send coupon code to a given email address.
     *
     * @param $email
     * @param $coupon
     * @return Varien_Object
     */
    public function sendCoupon($email, $coupon, $storeId)
    {

        $result = new Varien_Object;

        $this->_emailInfo->unsetData();
        $this->_emailInfo->addTo($email, '');

        $this->_template->getMail()->createAttachment(base64_decode($coupon->content), $coupon->type, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $coupon->name);
        $this->_template
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_COUPON_EMAIL_TEMPLATE, $storeId),
                Mage::getStoreConfig(self::XML_PATH_COUPON_EMAIL_IDENTITY, $storeId),
                $this->_emailInfo->getToEmails(),
                $this->_emailInfo->getToNames(),
                array(),
                $storeId
            );

        $result->setEmailSent(true);

        return $result;
    }

    /**
     * Send receipt to customer.
     *
     * @param $order
     * @param $receipt
     * @return Ebizmarts_BakerlooRestful_Helper_Email
     */
    public function sendReceipt($order, $receipt)
    {
        $storeId = $order->getStoreId();

        $this->_emailInfo->unsetData();
        $this->_emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerName());

        if ($this->isValidReceipt($receipt)) {
            $this->_template->getMail()
                ->createAttachment(base64_decode($receipt->content), $receipt->type, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $receipt->name);
        }

        $this->_template
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_RECEIPT_EMAIL_TEMPLATE, $storeId),
                Mage::getStoreConfig(self::XML_PATH_RECEIPT_EMAIL_IDENTITY, $storeId),
                $this->_emailInfo->getToEmails(),
                $this->_emailInfo->getToNames(),
                array('order' => $order),
                $storeId
            );

        $this->setEmailSent(true);

        return $this;
    }

    /**
     * @param stdClass $receipt
     * @return bool
     */
    private function isValidReceipt($receipt)
    {
        $valid = false;

        if (!is_null($receipt)) {
            if (isset($receipt->name) and !empty($receipt->name) and isset($receipt->content) and !empty($receipt->content) and isset($receipt->type)) {
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Send new account email to customer if enabled in config.
     *
     * @param $customer
     * @param null $storeId
     * @return $this
     */
    public function sendWelcome($customer, $storeId = null)
    {

        $shouldSendEmail = (int)$this->_helper->config('new_customer_account/send_welcome_email');
        if ($shouldSendEmail !== 1 or !$customer->getId()) {
            return;
        }

        $template = $customer->isConfirmationRequired() ? self::XML_PATH_WELCOME_CONFIRMATION_EMAIL_TEMPLATE : self::XML_PATH_WELCOME_EMAIL_TEMPLATE;

        $this->_emailInfo->unsetData();
        $this->_emailInfo->addTo($customer->getEmail(), $customer->getName());

        if ($customer->getId()) {
            $newResetPasswordLinkToken =  $this->getCustomerHelper()->generateResetPasswordLinkToken();
            $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
        }

        $this->_template
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                Mage::getStoreConfig($template, $storeId),
                Mage::getStoreConfig(self::XML_PATH_WELCOME_EMAIL_IDENTITY, $storeId),
                $this->_emailInfo->getToEmails(),
                $this->_emailInfo->getToNames(),
                array('customer' => $customer),
                $storeId
            );

        $this->setEmailSent(true);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param float $discount
     */
    public function sendPriceOverride(Mage_Sales_Model_Order $order, $discount = 0.00)
    {
        /** @var Mage_Core_Model_Email_Template $emailTemplate */
        $this->_template->loadDefault(self::PRICE_OVERRIDE_EMAIL_TEMPLATE);

        $adminName  = Mage::getStoreConfig('trans_email/ident_general/name', $order->getStoreId());
        $adminEmail = Mage::getStoreConfig('trans_email/ident_general/email', $order->getStoreId());

        $discount = sprintf('%s %d', $order->getBaseCurrencyCode(), $discount);
        $emailTemplateVars = array(
            'order_id' => $order->getIncrementId(),
            'discount' => $discount
        );

        $this->_template->setSenderName($adminName);
        $this->_template->setSenderEmail($adminEmail);
        $this->_template->send($adminEmail, null, $emailTemplateVars);
    }

    public function sendProduct($data, $product, $storeId)
    {
        if ($product->getId()) {
            /** @var Mage_Catalog_Helper_Image $imageHelper */
            $imageHelper = Mage::helper('catalog/image')->init($product, 'small_image');
            $template = $this->_helper->config(self::XML_PATH_PRODUCT_SHARE_TEMPLATE, $storeId);
            $sender = array(
                'email' => $data->getSenderEmail(),
                'name'  => $data->getSenderName()
            );

            $templateVars = array(
                'product_url'     => $product->getUrlInStore(),
                'product_name'    => $product->getName(),
                'product_image'   => $imageHelper->resize(75),
                'sender_name'     => $data->getSenderName(),
                'sender_email'    => $data->getSenderEmail(),
                'message'         => $data->getMessage(),
                'product'         => $product
            );

            $templateVars = new Varien_Object($templateVars);
            Mage::dispatchEvent('pos_send_product_email', array('email_data' => $data, 'template_vars' => $templateVars, 'product' => $product));

            $recipients = $data->getRecipients();
            foreach ($recipients as $recipient) {
                try {
                    $templateVars['recipient_name'] = $recipient['recipient_name'];
                    $templateVars['recipient_email'] = $recipient['recipient_email'];

                    $this->_template->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
                    $this->_template->sendTransactional(
                            $template,
                            $sender,
                            $recipient['recipient_email'],
                            $recipient['recipient_name'],
                            $templateVars->getData(),
                            $storeId
                        );
                    $this->setEmailSent((bool)$this->_template->getSentSuccess());
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this;
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function getCustomerHelper()
    {
        return Mage::helper('customer');
    }
}
