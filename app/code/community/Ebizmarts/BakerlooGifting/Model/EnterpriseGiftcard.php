<?php

class Ebizmarts_BakerlooGifting_Model_EnterpriseGiftcard extends Ebizmarts_BakerlooGifting_Model_Abstract
{

    protected $_model      = 'enterprise_giftcardaccount/giftcardaccount';
    protected $_moduleName = 'Enterprise_GiftCardAccount';

    protected $_expirationField = 'date_expires';
    protected $_balanceField    = 'balance';

    public function isValid()
    {
        return $this->_giftcard->isValid(true, true, true, false);
    }

    public function addToCart(Mage_Sales_Model_Quote $quote)
    {
        $this->_giftcard->addToCart(false, $quote);
    }

    public function addBalance($amount, $data = null)
    {
        $currentAmount = $this->_giftcard->getBalance();
        $username      = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());
        $user          = Mage::getModel('admin/user')->loadByUsername($username);
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

    public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote)
    {
        $quoteGiftCards = array();

        $_quoteGift = $quote->getGiftCards();
        if (!empty($_quoteGift)) {
            $quoteGiftCards = unserialize($_quoteGift);
            $quoteGiftCards = $this->_formatGiftCardResponse($quoteGiftCards);
        }

        return $quoteGiftCards;
    }

    protected function _formatGiftCardResponse(array $quoteGiftCards)
    {

        $nrOfGiftcards = count($quoteGiftCards);

        for ($i=0; $i < $nrOfGiftcards; $i++) {
            if (isset($quoteGiftCards[$i]['i'])) {
                $quoteGiftCards[$i]['id'] = (int)$quoteGiftCards[$i]['i'];
                unset($quoteGiftCards[$i]['i']);
            }
            if (isset($quoteGiftCards[$i]['c'])) {
                $quoteGiftCards[$i]['code'] = $quoteGiftCards[$i]['c'];
                unset($quoteGiftCards[$i]['c']);
            }
            if (isset($quoteGiftCards[$i]['ba'])) {
                $quoteGiftCards[$i]['base_amount'] = $quoteGiftCards[$i]['ba'];
                unset($quoteGiftCards[$i]['ba']);
            }
            if (isset($quoteGiftCards[$i]['a'])) {
                $quoteGiftCards[$i]['amount'] = $quoteGiftCards[$i]['a'];
                unset($quoteGiftCards[$i]['a']);
            }
        }

        return $quoteGiftCards;
    }

    public function getOptions(Mage_Catalog_Model_Product $product)
    {
        $allowOpenAmount = ((int)$product->getAllowOpenAmount() === 1 ? true : false);

        $giftCardType = (int)$product->getGiftcardType();
        $giftCardTypeLabel = $this->_getGiftCardTypeLabel($giftCardType);

        $options = array(
            'type'              => $giftCardType,
            'type_label'        => Mage::helper('bakerloo_restful')->__($giftCardTypeLabel),
            'amounts'           => $product->getGiftcardAmounts(),
            'allow_open_amount' => $allowOpenAmount,
        );

        if ($allowOpenAmount) {
            $options['open_amount_min'] = (is_null($product->getOpenAmountMin()) ? 0.0000 : (int)$product->getOpenAmountMin());
            $options['open_amount_max'] = (is_null($product->getOpenAmountMax()) ? 0.0000 : (int)$product->getOpenAmountMax());
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
                        or ($amount == $_gcAmount['website_value'])
                    ) {
                        $customAmount = false;
                    }
                }
            }

            $options['custom_giftcard_amount']   = ($customAmount ? $giftCardData['amount'] : '');
            $options['giftcard_amount']          = ($customAmount ? '' : $giftCardData['amount']);
            $options['giftcard_sender_name']     = $giftCardData['sender_name'];
            $options['giftcard_sender_email']    = $giftCardData['sender_email'];
            $options['giftcard_recipient_name']  = $giftCardData['recipient_name'];
            $options['giftcard_recipient_email'] = $giftCardData['recipient_email'];
            $options['giftcard_message']         = $giftCardData['comments'];
        }

        return $options;
    }

    public function getBuyRequestOptions(Varien_Object $buyRequest)
    {
        if (!isset($buyRequest['giftcard_amount']) || ($buyRequest['giftcard_amount'] === 'custom')) {
            $amount = $buyRequest['custom_giftcard_amount'];
            $customAmount = true;
        } elseif (isset($buyRequest['giftcard_amount'])) {
            $amount = $buyRequest['giftcard_amount'];
            $customAmount = false;
        } else {
            $amount = '';
            $customAmount = false;
        }

        $options = array(
            'amount'          => (float)$amount,
            'custom_amount'   => $customAmount,
            'sender_name'     => $buyRequest['giftcard_sender_name'],
            'sender_email'    => isset($buyRequest['giftcard_sender_email']) ? $buyRequest['giftcard_sender_email'] : '',
            'recipient_name'  => isset($buyRequest['giftcard_recipient_name']) ? $buyRequest['giftcard_recipient_name'] : '',
            'recipient_email' => isset($buyRequest['giftcard_recipient_email']) ? $buyRequest['giftcard_recipient_email'] : '',
            'comments'        => isset($buyRequest['giftcard_message']) ? $buyRequest['giftcard_message'] : ''
        );

        return $options;
    }

    public function getItemData(Mage_Sales_Model_Order_Item $item, $gift)
    {
        $selection = $item->getBuyRequest();

        $result = array(
            'gift_code'       => array($gift->getCode()),
            'date_created'    => $gift->getDateCreated(),
            'date_expires'    => $gift->getDateExpires(),
            'balance'         => $gift->getBalance(),
            'sender_name'     => $selection->getGiftcardSenderName(),
            'sender_email'    => $selection->getGiftcardSenderEmail(),
            'recipient_name'  => $selection->getGiftcardRecipientName(),
            'recipient_email' => $selection->getGiftcardRecipientEmail(),
            'message'         => $selection->getGiftcardMessage()
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
}
