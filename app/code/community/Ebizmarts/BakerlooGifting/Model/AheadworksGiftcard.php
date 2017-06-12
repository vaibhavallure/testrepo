<?php

class Ebizmarts_BakerlooGifting_Model_AheadworksGiftcard extends Ebizmarts_BakerlooGifting_Model_Abstract
{

    protected $_model      = 'aw_giftcard/giftcard';
    protected $_moduleName = 'AW_Giftcard';

    protected $_expirationField = 'expire_at';
    protected $_balanceField    = 'balance';

    public function init()
    {
        parent::init();

        $this->_giftcard->setDateCreated($this->_giftcard->getCreatedAt());
        $this->_giftcard->setDateExpires($this->_giftcard->getExpireAt());
    }

    public function isValid()
    {
        return $this->_giftcard->isValidForRedeem($this->getStoreId());
    }

    public function addToCart(Mage_Sales_Model_Quote $quote)
    {
        Mage::helper('aw_giftcard/totals')->addCardToQuote($this->_giftcard, $quote);
    }

    public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote)
    {
        $quoteGiftCards = array();

        $_quoteGift = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($quote->getId());
        if (!empty($_quoteGift)) {
            $quoteGiftCards = $this->_formatGiftCardResponse($_quoteGift);
        }

        return $quoteGiftCards;
    }

    public function addBalance($amount, $data = null)
    {
        $currentAmount = $this->_giftcard->getBalance();

        $username = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());
        $user     = $this->getAdminUser()->loadByUsername($username);
        Mage::getSingleton('admin/session')->setUser($user);

        $this->_giftcard
            ->setData('status', 1)
            ->setData('balance', $currentAmount + $amount);

        if (isset($data['creditnote_id'])) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($data['creditnote_id']);
            $this->_giftcard->setData('creditmemo', $creditmemo);
        }

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
              'base_amount' => $giftcard->getCardBalance(),
              'amount'      => $giftcard->getCardBalance(),
            );
        }

        return $return;
    }

    public function getOptions(Mage_Catalog_Model_Product $product)
    {
        $allowOpenAmount = ((int)$product->getAwGcAllowOpenAmount() === 1 ? true : false);

        $giftCardType = (int)$product->getGiftcardType();
        $giftCardTypeLabel = $this->_getGiftCardTypeLabel($giftCardType);

        $amounts = $product->getPriceModel()->getAmountOptions($product);

        $options = array(
            'type'              => $giftCardType,
            'type_label'        => Mage::helper('bakerloo_restful')->__($giftCardTypeLabel),
            'amounts'           => $amounts,
            'allow_open_amount' => $allowOpenAmount,
        );

        $minAmount = (int)$product->getAwGcOpenAmountMin();
        $maxAmount = (int)$product->getAwGcOpenAmountMax();

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

            $options['aw_gc_custom_amount']   = ($customAmount ? $giftCardData['amount'] : '');
            $options['aw_gc_amount']          = ($customAmount ? '' : $giftCardData['amount']);
            $options['aw_gc_sender_name']     = $giftCardData['sender_name'];
            $options['aw_gc_sender_email']    = $giftCardData['sender_email'];
            $options['aw_gc_recipient_name']  = $giftCardData['recipient_name'];
            $options['aw_gc_recipient_email'] = $giftCardData['recipient_email'];
            $options['aw_gc_message']         = $giftCardData['comments'];
        }

        return $options;
    }

    public function getBuyRequestOptions(Varien_Object $buyRequest)
    {
        if ($buyRequest['aw_gc_amount'] === 'custom') {
            $amount = $buyRequest['aw_gc_custom_amount'];
            $customAmount = true;
        } else {
            $amount = $buyRequest['aw_gc_amount'];
            $customAmount = false;
        }
        
        $options = array(
            'amount'          => (float)$amount,
            'custom_amount'   => $customAmount,
            'sender_name'     => $buyRequest['aw_gc_sender_name'],
            'sender_email'    => isset($buyRequest['aw_gc_sender_email']) ? $buyRequest['aw_gc_sender_email'] : '',
            'recipient_name'  => isset($buyRequest['aw_gc_recipient_name']) ? $buyRequest['aw_gc_recipient_name'] : '',
            'recipient_email' => isset($buyRequest['aw_gc_recipient_email']) ? $buyRequest['aw_gc_recipient_email'] : '',
            'comments'        => isset($buyRequest['aw_gc_message']) ? $buyRequest['aw_gc_message'] : ''
        );

        return $options;
    }

    public function getItemData(Mage_Sales_Model_Order_Item $item, $gift)
    {
        $selection = $item->getBuyRequest();

        $result = array(
            'gift_code'       => array($gift->getCode()),
            'date_created'    => $gift->getCreatedAt(),
            'date_expires'    => $gift->getExpireAt(),
            'balance'         => $gift->getBalance(),
            'sender_name'     => $selection->getAwGcSenderName(),
            'sender_email'    => $selection->getAwGcSenderEmail(),
            'recipient_name'  => $selection->getAwGcRecipientName(),
            'recipient_email' => $selection->getAwGcRecipientEmail(),
            'message'         => $selection->getAwGcMessage()
        );

        return $result;
    }

    private function _getGiftCardTypeLabel($type)
    {
        $label = '';

        switch ($type) {
            case 0:
                $label = 'Virtual';
                break;
            case 1:
                $label = 'Physical';
                break;
            case 2:
                $label = 'Combined';
                break;
            default:
                break;
        }

        return $label;
    }

    public function getAdminUser()
    {
        return Mage::getModel('admin/user');
    }
}
