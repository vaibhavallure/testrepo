<?php

class Teamwork_CEGiftcards_Block_Catalog_Product_View_Type_Giftcard
    extends Mage_Catalog_Block_Product_View_Abstract
{
    public function isMessageAvailable($product)
    {
        return (int) $product->getData('giftcard_allow_message');
    }

    public function getDefaultValue($key)
    {
        return (string) $this->getProduct()->getPreconfiguredValues()->getData($key);
    }

    public function isAmountLikePrice()
    {
        $_product = $this->getProduct();
        if ($_product->getGiftcardOpenAmount()) {
            return false;
        }
        if (count($this->getAmountList($_product)) > 1) {
            return false;
        }
        return true;
    }

    public function getAmountList(Mage_Catalog_Model_Product $_product)
    {
        return $_product->getTypeInstance(true)->getLoadAmounts($_product);
    }

    public function isAmountAvailable($product)
    {
        return (count($this->getAmountList($product)) > 0);
    }

    public function isOpenAmountAvailable($product)
    {
        return $product->getGiftcardOpenAmount();
    }

    public function getCurrentCurrency()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    public function isEmailAvailable($product)
    {
        if ($product->getTypeInstance()->isTypePhysical()) {
            return false;
        }
        return true;
    }
}
