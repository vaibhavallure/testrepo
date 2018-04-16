<?php

class Teamwork_CEGiftcards_Block_Sales_Order_Item_Renderer extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    protected function _prepareCustomOption($code)
    {
        if ($option = $this->getOrderItem()->getProductOptionByCode($code)) {
            return $this->escapeHtml($option);
        }
        return false;
    }

    protected function _getNameEmailString($name, $email)
    {
        return "$name &lt;{$email}&gt;";
    }

    protected function _getGiftcardOptions()
    {
        $result = array();
        if ($value = $this->_prepareCustomOption('giftcard_sender_name')) {
            if ($email = $this->_prepareCustomOption('giftcard_sender_email')) {
                $value = $this->_getNameEmailString($value, $email);
            }
            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Sender'),
                'value'=>$value,
            );
        }
        if ($value = $this->_prepareCustomOption('giftcard_recipient_name')) {
            if ($email = $this->_prepareCustomOption('giftcard_recipient_email')) {
                $value = $this->_getNameEmailString($value, $email);
            }
            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Recipient'),
                'value'=>$value,
            );
        }
        if ($value = $this->_prepareCustomOption('giftcard_message')) {
            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Message'),
                'value'=>$value,
            );
        }
        return $result;
    }

    public function getItemOptions()
    {
        return array_merge($this->_getGiftcardOptions(), parent::getItemOptions());
    }
}
