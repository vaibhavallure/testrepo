<?php

class Teamwork_CEGiftcards_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{

    protected function _prepareCustomOption(Mage_Catalog_Model_Product_Configuration_Item_Interface $item, $code)
    {
        $option = $item->getOptionByCode($code);
        if ($option) {
            $value = $option->getValue();
            if ($value) {
                return $this->escapeHtml($value);
            }
        }
        return false;
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function _getGiftcardOptions($item)
    {

        $result = array();
        $value = $this->_prepareCustomOption($item, 'giftcard_sender_name');
        if ($value) {
            $email = $this->_prepareCustomOption($item, 'giftcard_sender_email');
            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label' => $this->__('Gift Card Sender'),
                'value' => $value
            );
        }

        $value = $this->_prepareCustomOption($item, 'giftcard_recipient_name');
        if ($value) {
            $email = $this->_prepareCustomOption($item, 'giftcard_recipient_email');
            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label' => $this->__('Gift Card Recipient'),
                'value' => $value
            );
        }

        $value = $this->_prepareCustomOption($item, 'giftcard_message');
        if ($value) {
            $result[] = array(
                'label' => $this->__('Gift Card Message'),
                'value' => $value
            );
        }

        return $result;
    }

    /**
     * Return gift card and custom options array
     *
     * @return array
     */
    public function getOptionList()
    {
        $item = $this->getItem();
        return array_merge(
            $this->_getGiftcardOptions($item),
            Mage::helper('catalog/product_configuration')->getCustomOptions($item)
        );
    }
}
