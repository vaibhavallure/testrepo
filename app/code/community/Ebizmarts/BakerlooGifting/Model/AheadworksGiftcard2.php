<?php

class Ebizmarts_BakerlooGifting_Model_AheadworksGiftcard2 extends Ebizmarts_BakerlooGifting_Model_Abstract
{

    protected $_model      = 'aw_giftcard2/giftcard';
    protected $_moduleName = 'AW_Giftcard2';

    protected $_expirationField = 'expire_at';
    protected $_balanceField    = 'initial_balance';

    public function init()
    {
        parent::init();

        $this->_giftcard->setDateCreated($this->_giftcard->getCreatedAt());
        $this->_giftcard->setDateExpires($this->_giftcard->getExpireAt());
        $this->_giftcard->setAvailability(AW_Giftcard2_Model_Source_Giftcard_Availability::ACTIVE_VALUE);
        $this->_giftcard->setType(AW_Giftcard2_Model_Source_Entity_Attribute_Giftcard_Type::VIRTUAL_VALUE);
    }

    public function isValid()
    {
        return $this->_giftcard->isValidForRedeem($this->getStoreId());
    }

    public function addToCart(Mage_Sales_Model_Quote $quote)
    {
        Mage::helper('aw_giftcard2/giftcard')->addToQuote($this->_giftcard, $quote->getId());
    }

    public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote)
    {
        $quoteGiftCards = array();

        $_quoteGift = Mage::helper('aw_giftcard2/giftcard')->getQuoteGiftCards($quote->getId());
        if (!empty($_quoteGift)) {
            $quoteGiftCards = $this->_formatGiftCardResponse($_quoteGift);
        }

        return $quoteGiftCards;
    }

    public function create($storeId, $amount, $expirationDate = null)
    {
        parent::create($storeId, $amount, $expirationDate);

        $customer = $this->getCustomer($storeId);
        $email    = $this->getCustomerEmail();

        if ($customer) {
            $this->_giftcard->setData('sender_name', $customer->getName());
            $this->_giftcard->setData('recipient_name', $customer->getName());
        }

        if ($email) {
            $this->_giftcard->setData('sender_email', $email);
            $this->_giftcard->setData('recipient_email', $email);
        } elseif ($customer) {
            $this->_giftcard->setData('sender_email', $customer->getEmail());
            $this->_giftcard->setData('recipient_email', $customer->getEmail());
        }

        $this->_giftcard->setIsNew(false)->save();
        return $this->_giftcard->getCode();
    }

    public function addBalance($amount, $data = null)
    {
        $creditmemoId = isset($data['creditnote_id']) ? $data['creditnote_id'] : null;
        if ($creditmemoId) {
            $gcMemo = Mage::getModel('aw_giftcard2/giftcard_creditmemo')->load($creditmemoId, 'creditmemo_id');
            if ($gcMemo->getId()) {
                Mage::throwException(Mage::helper('bakerloo_gifting')->__("This credit memo {$creditmemoId} has already been refunded to gift card {$this->_giftcard->getCode()}"));
            } else {
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
                if ($creditmemo->getId()) {
                    $this->_giftcard->setData('creditmemo', $creditmemo);
                }
            }
        }

        $currentAmount = $this->_giftcard->getBalance();
        $username      = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());
        $user          = $this->getAdminUser()->loadByUsername($username);
        Mage::getSingleton('admin/session')->setUser($user);

        $this->_giftcard
            ->setData('availability', 1)
            ->setData('balance', $currentAmount + $amount);

        $this->_giftcard->save();

        return $this->_giftcard->getCode();
    }

    protected function _formatGiftCardResponse(array $quoteGiftCards)
    {

        $return = array();

        foreach ($quoteGiftCards as $giftcard) {
            $return[] = array(
              'id'          => (int)$giftcard->getGiftcardId(),
              'code'        => $giftcard->getCode(),
              'base_amount' => $giftcard->getBaseGiftcardAmount(),
              'amount'      => $giftcard->getBalance(),
            );
        }

        return $return;
    }

    public function getOptions(Mage_Catalog_Model_Product $product)
    {
        $allowOpenAmount   = ((int)$product->getData(AW_Giftcard2_Model_Product_Type_Giftcard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT) === 1 ? true : false);
        $giftCardType      = (int)$product->getData(AW_Giftcard2_Model_Product_Type_Giftcard::ATTRIBUTE_CODE_TYPE);

        $amounts = array();
        foreach ($product->getData(AW_Giftcard2_Model_Product_Type_Giftcard::ATTRIBUTE_CODE_AMOUNTS) as $_amountOption) {
            $amounts[] = array(
                'website_d' => $_amountOption['website_id'],
                'value'     => $_amountOption['price']
            );
        }

        $options = array(
            'type'              => $giftCardType,
            'type_label'        => $this->getGiftCardTypeLabel($giftCardType),
            'amounts'           => $amounts,
            'allow_open_amount' => $allowOpenAmount,
        );

        $minAmount = (int)$product->getData(AW_Giftcard2_Model_Product_Type_Giftcard::ATTRIBUTE_CODE_OPEN_AMOUNT_MIN);
        $maxAmount = (int)$product->getData(AW_Giftcard2_Model_Product_Type_Giftcard::ATTRIBUTE_CODE_OPEN_AMOUNT_MAX);

        if ($allowOpenAmount) {
            $options['open_amount_min'] = (is_null($minAmount) ? 0.0000 : $minAmount);
            $options['open_amount_max'] = (is_null($maxAmount) ? 0.0000 : $maxAmount);
        }

        return $options;
    }

    public function getBuyInfoOptions($data)
    {
        $options = array();
        if (isset($data['gift_card_options'])) {
            $giftCardData = $data['gift_card_options'];
            $amount       = $giftCardData['amount'];
            $amounts      = $giftCardData['amounts'];

            $customAmount = true;

            if (!empty($amounts)) {
                foreach ($amounts as $_gcAmount) {
                    if (($amount == $_gcAmount['value'])
                        or ($amount == $_gcAmount['website_value'])) {
                        $customAmount = false;
                    }
                }
            }

            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT]   = ($customAmount ? $giftCardData['amount'] : '');
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_AMOUNT]          = ($customAmount ? 'custom' : $giftCardData['amount']);
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_NAME]     = $giftCardData['sender_name'];
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL]    = $giftCardData['sender_email'];
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME]  = $giftCardData['recipient_name'];
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL] = $giftCardData['recipient_email'];
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_MESSAGE]         = $giftCardData['comments'];

            //get gift card product default template
            $product = $this->getCatalogProduct()->load($data['product_id']);
            if ($product->getId()) {
                $templateOptions = $product->getTypeInstance()->getTemplateOptions($product);
                if (!empty($templateOptions)) {
                    $options[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE] = $templateOptions[0]['template'];
                }
            }

            if (!is_null($giftCardData['gift_code'])) {
                foreach ($giftCardData['gift_code'] as $code) {
                    if (Mage::getResourceModel('aw_giftcard2/giftcard')->codeExists($code)) {
                        Mage::throwException('Gift code already sold. Please select a new one.');
                    }
                }

                $options['selected_gift_codes'] = $giftCardData['gift_code'];
            }
        }

        return $options;
    }

    public function getBuyRequestOptions(Varien_Object $buyRequest)
    {
        if ($buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_AMOUNT] === 'custom') {
            $amount = $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT];
            $customAmount = true;
        } else {
            $amount = $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_AMOUNT];
            $customAmount = false;
        }

        $senderEmail = isset($buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL])
            ? $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL]
            : '';

        $recipientEmail = isset($buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL])
            ? $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL]
            : '';

        $message = isset($buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_MESSAGE])
            ? $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_MESSAGE]
            : '';

        $options = array(
            'amount'          => (float)$amount,
            'custom_amount'   => $customAmount,
            'sender_name'     => $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_NAME],
            'sender_email'    => $senderEmail,
            'recipient_name'  => $buyRequest[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME],
            'recipient_email' => $recipientEmail,
            'comments'        => $message
        );

        return $options;
    }

    public function getItemData(Mage_Sales_Model_Order_Item $item)
    {
        $selection = $item->getProductOptionByCode(AW_Giftcard2_Model_Product_Type_Giftcard::CUSTOM_OPTIONS_CODE);

        $senderEmail = isset($selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL])
            ? $selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL]
            : '';

        $recipientEmail = isset($selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL])
            ? $selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL]
            : '';

        $createdCodes = $selection[AW_Giftcard2_Model_Product_Type_Giftcard::ORDER_ITEM_CODE_CREATED_CODES];
        $result = array(
            'gift_code'       => $createdCodes,
            'date_created'    => '',
            'date_expires'    => '',
            'balance'         => $selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_AMOUNT],
            'sender_name'     => $selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_SENDER_NAME],
            'sender_email'    => $senderEmail,
            'recipient_name'  => $selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME],
            'recipient_email' => $recipientEmail,
            'message'         => $selection[AW_Giftcard2_Model_Product_Type_Giftcard::BUY_REQUEST_ATTR_CODE_MESSAGE]
        );

        if (!empty($createdCodes)) {
            $model = Mage::getModel($this->_model)->loadByCode($createdCodes[0]);

            if ($model->getId()) {
                $result['date_created'] = $model->getCreatedAt();
                $result['date_expires'] = $model->getExpireAt();
                $result['balance']      = $model->getBalance();
            }
        }

        return $result;
    }

    public function getGiftCardTypeLabel($type)
    {
        $config = Mage::getModel('aw_giftcard2/source_entity_attribute_giftcard_type');
        return $config->getOptionText($type);
    }

    public function getCustomer($storeId)
    {
        if ($this->getCustomerId()) {
            return $this->getModelCustomer()->load($this->getCustomerId());
        }

        if ($this->getCustomerEmail()) {
            $websiteId = $this->websiteIdByStoreId($storeId);
            return $this->getModelCustomer()->setWebsiteId($websiteId)->loadByEmail($this->getCustomerEmail());
        }
    }

    public function getCatalogProduct()
    {
        return Mage::getModel('catalog/product');
    }

    public function getAdminUser()
    {
        return Mage::getModel('admin/user');
    }

    public function getModelCustomer()
    {
        return Mage::getModel('customer/customer');
    }
}
