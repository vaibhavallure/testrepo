<?php

class Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard extends Mage_Catalog_Model_Product_Type_Abstract
{

    const TYPE_VIRTUAL  = 0;
    const TYPE_PHYSICAL = 1;
    const TYPE_COMBINED = 2;
    const TYPE_UNKNOWN  = 3;

    const TYPE_GIFTCARD     = 'teamwork_cegiftcard';

    /**
     * Whether product quantity is fractional number or not
     *
     * @var bool
     */
    protected $_canUseQtyDecimals  = false;

    /**
     * Product is configurable
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Check is gift card product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isGiftCard($product = null)
    {
        return true;
    }

    /**
     * Check if gift card type is combined
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isTypeCombined($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == self::TYPE_COMBINED) {
            return true;
        }
        return false;
    }

    /**
     * Check if gift card type is physical
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isTypePhysical($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == self::TYPE_PHYSICAL) {
            return true;
        }
        return false;
    }

    /**
     * Check if gift card type is virtual
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isTypeVirtual($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == self::TYPE_VIRTUAL) {
            return true;
        }
        return false;
    }

    /**
     * Check if gift card is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == self::TYPE_VIRTUAL) {
            return true;
        }
        return false;
    }

    /**
     * Check if product is available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null)
    {
        $amount = $this->getProduct($product)->getPrice();

        if (!$amount) {
            return false;
        }


        return parent::isSalable($product);
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Use standard preparation process and also add specific giftcard options.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        try {
            /*$amount = */$this->_validate($buyRequest, $product, $processMode);
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper('teamwork_cegiftcards')->__('An error has occurred while preparing Gift Card.');
        }

        //$product->addCustomOption('giftcard_amount', $amount, $product);
        //$product->addCustomOption('giftcard_sender_name', $buyRequest->getGiftcardSenderName(), $product);
        //$product->addCustomOption('giftcard_recipient_name', $buyRequest->getGiftcardRecipientName(), $product);

//        $amount = $buyRequest->getGiftcardAmount();
//        if ($amount === "custom") {
//            $amount = $buyRequest->getGiftcardAmountCustom();
//        }
//        $product->addCustomOption('giftcard_amount', $amount, $product);
        $amount = false;
        $allowedAmountList = $this->getLoadAmounts($product);
        if ($buyRequest->hasGiftcardAmount()) {
            $amount = $buyRequest->getGiftcardAmount();
        } else if (count($allowedAmountList) == 1) {
            $amount = reset($allowedAmountList);
        }
        if (!count($allowedAmountList)) {
            $amount = 'custom';
        }


        $product->addCustomOption('giftcard_amount', $amount, $product);
        $product->addCustomOption('giftcard_amount_custom', $buyRequest->getGiftcardAmountCustom(), $product);

        if (!$this->isTypePhysical($product)) {
            $product->addCustomOption('giftcard_sender_email', $buyRequest->getGiftcardSenderEmail(), $product);
            $product->addCustomOption('giftcard_recipient_email', $buyRequest->getGiftcardRecipientEmail(), $product);
            $product->addCustomOption('giftcard_sender_name', $buyRequest->getGiftcardSenderName(), $product);
            $product->addCustomOption('giftcard_recipient_name', $buyRequest->getGiftcardRecipientName(), $product);
            $product->addCustomOption('giftcard_message', $buyRequest->getGiftcardMessage(), $product);
        }

        return $result;
    }

    /**
     * Validate Gift Card product, determine and return its amount
     *
     * @param Varien_Object $buyRequest
     * @param  $product
     * @param  $processMode
     * @return double|float|mixed
     */
    private function _validate(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if (!$this->isTypePhysical($product)) {
            if (!$buyRequest->getGiftcardRecipientEmail() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('teamwork_cegiftcards')->__('Please specify recipient email.')
                );
            }
            if (!$buyRequest->getGiftcardSenderEmail() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('teamwork_cegiftcards')->__('Please specify sender email.')
                );
            }
            if (!$buyRequest->getGiftcardRecipientName() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('teamwork_cegiftcards')->__('Please specify recipient name.')
                );
            }
            if (!$buyRequest->getGiftcardSenderName() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('teamwork_cegiftcards')->__('Please specify sender name.')
                );
            }
        }

        if ($isStrictProcessMode) {

            $amount = false;
            $allowedAmountList = $this->getLoadAmounts($product);
            if ($buyRequest->hasGiftcardAmount()) {
                $amount = $buyRequest->getGiftcardAmount();
            } else if (count($allowedAmountList) == 1) {
                $amount = reset($allowedAmountList);
            }

            if (count($allowedAmountList) && $amount != 'custom') {
                $error = true;
                if ($amount !== false) {
                    foreach($allowedAmountList as $allowedAmount) {
                        if ($allowedAmount == $amount) {
                            $error = false;
                            break;
                        }
                    }
                }
                if ($error) {
                    Mage::throwException(
                        Mage::helper('teamwork_cegiftcards')->__('Please specify all the required information.')
                    );
                }
            }

            if (!count($allowedAmountList)) {
                $amount = 'custom';
            }

            if ($product->getGiftcardOpenAmount() && $amount == 'custom') {
                if ($buyRequest->hasGiftcardAmountCustom()) {
                    $amount = $buyRequest->getGiftcardAmountCustom();
                    if ($product->hasGiftcardAmountMin()) {
                        $minAmount = $product->getGiftcardAmountMin();
                        if ($amount < $minAmount) {
                            $messageAmount = Mage::helper('core')->currency($minAmount, true, false);
                            Mage::throwException(
                            Mage::helper('teamwork_cegiftcards')->__('Gift Card min amount is %s', $messageAmount)
                            );
                        }
                    }
                    if ($product->hasGiftcardAmountMax()) {
                        $maxAmount = $product->getGiftcardAmountMax();
                        if ($amount > $maxAmount) {
                            $messageAmount = Mage::helper('core')->currency($maxAmount, true, false);
                            Mage::throwException(
                            Mage::helper('teamwork_cegiftcards')->__('Gift Card max amount is %s', $messageAmount)
                            );
                        }
                    }
                } else {
                    Mage::throwException(
                    Mage::helper('teamwork_cegiftcards')->__('Please specify all the required information.')
                    );
                }
            }
        }

    }

    /**
     * Check if product can be bought
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product = null)
    {
        parent::checkProductBuyState($product);
        $product = $this->getProduct($product);
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $buyRequest = new Varien_Object(unserialize($option->getValue()));
            $this->_validate($buyRequest, $product, self::PROCESS_MODE_FULL);
        }
        return $this;
    }

    public function canConfigure($product = null)
    {
        $product = $this->getProduct($product);
        if ($product->getGiftcardOpenAmount()) {
            return true;
        }
        if (count($this->getLoadAmounts($product)) > 1) {
            return true;
        }
        return !$this->isTypePhysical($product);
    }

    public function save($product = null)
    {
        $product = $this->getProduct($product);
        if ($product->hasData('giftcard_amount')) {
            /*previously added amounts*/
            $oldCollection = Mage::getModel('teamwork_cegiftcards/catalog_product_type_giftcard_amount')->getCollection()->addProductFilter($product);

            /*add new amounts*/
            $amounts = $product->getData('giftcard_amount');
            if (!is_array($amounts)) {
                $amounts = array($amounts);
            }
            $amountsToSave = array();
            foreach ($amounts as $amount) {
                if (is_array($amount)) {
                    if (!$amount['delete']) {
                        $amountsToSave[] = $amount['price'];
                    }
                } else {
                    $amountsToSave[] = $amount;
                }
            }

            $doResave = false;
            if ($oldCollection->count() != count($amountsToSave)) {
                $doResave = true;
            }
            if (!$doResave) {
                foreach($oldCollection as $oldAmount) {
                    if (!in_array($oldAmount->getData('amount'), $amountsToSave)) {
                        $doResave = true;
                        break;
                    }
                }
            }
            if ($doResave) {
                foreach ($oldCollection as $oldAmount) {
                    $oldAmount->isDeleted(true);
                }
                $oldCollection->save();
                foreach($amountsToSave as $key => $amount) {
                    $amountObj = Mage::getModel('teamwork_cegiftcards/catalog_product_type_giftcard_amount');
                    $amountObj->setData('amount', $amount);
                    $amountObj->setData('product_id', $product->getId());
                    $amountObj->setData('position', ($key + 1) * 10);
                    $amountObj->save();
                }
            }
        }
        return parent::save($product);
    }

    public function getLoadAmounts($product = null)
    {
        $product = $this->getProduct($product);
        if ($product->getData('__giftcard_amount_loaded')) {
            $amounts = $product->getData('giftcard_amount');
            if (!is_array($amounts)) {
                $amounts = array($amounts);
            }
        } else {
            $amounts = array();
            $collection = Mage::getModel('teamwork_cegiftcards/catalog_product_type_giftcard_amount')->getCollection()->addProductFilter($product);
            foreach($collection as $amount) {
                $amounts[] = $amount->getData('amount');
            }
            $product->setData('giftcard_amount', $amounts);
            $product->setData('__giftcard_amount_loaded', true);
        }
        return $amounts;
    }

    /**
     * Prepare selected options for giftcard
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = array(
            'giftcard_sender_name'    => $buyRequest->getGiftcardSenderName(),
            'giftcard_sender_email'    => $buyRequest->getGiftcardSenderEmail(),
            'giftcard_recipient_name' => $buyRequest->getGiftcardRecipientName(),
            'giftcard_recipient_email' => $buyRequest->getGiftcardRecipientEmail(),
            'giftcard_message'        => $buyRequest->getGiftcardMessage(),
            'giftcard_amount'        => $buyRequest->getGiftcardAmount(),
            'giftcard_amount_custom'        => $buyRequest->getGiftcardAmountCustom(),
        );

        return $options;
    }
}
