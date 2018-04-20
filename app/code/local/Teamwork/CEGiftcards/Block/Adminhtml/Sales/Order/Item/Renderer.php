<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Item_Renderer
    extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    protected function _prepareCustomOption($code)
    {
        if ($option = $this->getItem()->getProductOptionByCode($code)) {
            return $this->escapeHtml($option);
        }
        return false;
    }

    protected function _getGiftcardOptions()
    {
        $result = array();
        if ($type = $this->getItem()->getProductOptionByCode('giftcard_type')) {
            switch ($type) {
                case Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_VIRTUAL:
                    $type = Mage::helper('teamwork_cegiftcards')->__('Virtual');
                    break;
                case Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL:
                    $type = Mage::helper('teamwork_cegiftcards')->__('Physical');
                    break;
                case Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_COMBINED:
                    $type = Mage::helper('teamwork_cegiftcards')->__('Combined');
                    break;
            }

            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Type'),
                'value'=>$type,
            );
        }


        if ($value = $this->_prepareCustomOption('giftcard_sender_name')) {
            if ($email = $this->_prepareCustomOption('giftcard_sender_email')) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Sender'),
                'value'=>$value,
                'custom_view'=>true,
            );
        }
        if ($value = $this->_prepareCustomOption('giftcard_recipient_name')) {
            if ($email = $this->_prepareCustomOption('giftcard_recipient_email')) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Recipient'),
                'value'=>$value,
                'custom_view'=>true,
            );
        }
        if ($value = $this->_prepareCustomOption('giftcard_message')) {
            $result[] = array(
                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Message'),
                'value'=>$value,
            );
        }

//        if ($value = $this->_prepareCustomOption('giftcard_lifetime')) {
//            $result[] = array(
//                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Lifetime'),
//                'value'=>sprintf('%s days', $value),
//            );
//        }
//
//        $yes = Mage::helper('teamwork_cegiftcards')->__('Yes');
//        $no = Mage::helper('teamwork_cegiftcards')->__('No');
//        if ($value = $this->_prepareCustomOption('giftcard_is_redeemable')) {
//            $result[] = array(
//                'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Is Redeemable'),
//                'value'=>($value ? $yes : $no),
//            );
//        }

        $createdCodes = 0;
        $totalCodes = $this->getItem()->getQtyOrdered();
        if ($codes = $this->getItem()->getProductOptionByCode('giftcard_created_codes')) {
            $createdCodes = count($codes);
        }

        if (is_array($codes)) {
            foreach ($codes as &$code) {
                if ($code === null) {
                    $code = Mage::helper('teamwork_cegiftcards')->__('Unable to create.');
                }
            }
        } else {
            $codes = array();
        }

        for ($i = $createdCodes; $i < $totalCodes; $i++) {
            $codes[] = Mage::helper('teamwork_cegiftcards')->__('N/A');
        }

        $result[] = array(
            'label'=>Mage::helper('teamwork_cegiftcards')->__('Gift Card Accounts'),
            'value'=>implode('<br />', $codes),
            'custom_view'=>true,
        );



        return $result;
    }

    public function getOrderOptions()
    {
        return array_merge($this->_getGiftcardOptions(), parent::getOrderOptions());
    }
}
