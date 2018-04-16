<?php

class Teamwork_CEGiftcards_Model_Quote_Address_Total extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    const TOTAL_DATA_KEY = 'teamwork_cegiftcards_data';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        $doCollect = false;
        if ($quote->isVirtual()) {
            if ($this->_isBillingAddress($address)) {
                $doCollect = true;
            }
        } else if ($this->_isShippingAddress($address)){
            $doCollect = true;
        }
        if ($doCollect) {
            parent::collect($address);
            $quote = $address->getQuote();
            $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                            ->getCollection()
                          ->addQuoteFilter($quote);
            if ($appliedGCs->count()) {
                $gt = $address->getGrandTotal();
                $bgt = $address->getBaseGrandTotal();
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach($appliedGCs as $gc) {
                    $dAppliedAmount = 0;
                    if ($gt > 0) {
                        $gBalance = $gc->getData('balance');
                        if ($gt < $gBalance) {
                            $dAppliedAmount = $gt;
                            $gt = 0;
                        } else {
                            $dAppliedAmount = $gBalance;
                            $gt -= $gBalance;
                        }
                    }
                    $appliedAmount += $dAppliedAmount;
                    $gc->setData('amount', $dAppliedAmount);

                    $dAppliedAmount = 0;
                    if ($bgt > 0) {
                        $gBalance = $gc->getData('balance');
                        if ($bgt < $gBalance) {
                            $dAppliedAmount = $bgt;
                            $bgt = 0;
                        } else {
                            $dAppliedAmount = $gBalance;
                            $bgt -= $gBalance;
                        }
                    }
                    $gc->setData('base_amount', $dAppliedAmount);
                    $baseAppliedAmount += $dAppliedAmount;

                    $gc->save();
                }

                if ($appliedAmount > 0) {
                    $this->_setAmount($appliedAmount * (-1));
                    $address->setGrandTotal($address->getGrandTotal() - $appliedAmount);
                }
                if ($baseAppliedAmount > 0) {
                    $this->_setBaseAmount($baseAppliedAmount * (-1));
                    $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseAppliedAmount);
                }
            }
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        $doCollect = false;
        if ($quote->isVirtual()) {
            if ($this->_isBillingAddress($address)) {
                $doCollect = true;
            }
        } else if ($this->_isShippingAddress($address)){
            $doCollect = true;
        }
        if ($doCollect) {

            $quote = $address->getQuote();
            $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')
                            ->getCollection()
                          ->addQuoteFilter($quote);
            if ($appliedGCs->count()) {
                $appliedAmount = 0;
                $gcData = array();
                foreach($appliedGCs as $gc) {
                    $appliedAmount += $gc->getData('amount');
                }
                $address->addTotal(array(
                    'code'  => $this->getCode(),
                    'title' => Mage::helper('teamwork_cegiftcards')->__('Gift Card(s) Amount'),
                    'value' => $appliedAmount,
                    self::TOTAL_DATA_KEY => $appliedGCs,
                    /*'area'  => 'footer',*/
                ));

            }
        }
        return $this;
    }

    protected function _isShippingAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $shippingType = Mage::helper('teamwork_cegiftcards')->getClassConstVal('Mage_Customer_Model_Address_Abstract', 'TYPE_SHIPPING', 'shipping');
        return $address->getAddressType() == $shippingType;
    }

    protected function _isBillingAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $billingType = Mage::helper('teamwork_cegiftcards')->getClassConstVal('Mage_Customer_Model_Address_Abstract', 'TYPE_BILLING', 'billing');
        return $address->getAddressType() == $billingType;
    }
    
}
