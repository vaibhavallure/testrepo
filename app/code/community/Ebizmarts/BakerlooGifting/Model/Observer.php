<?php

class Ebizmarts_BakerlooGifting_Model_Observer
{

    private $_helper;

    public function __construct($helper)
    {
        if (!$helper) {
            $this->_helper = Mage::helper('bakerloo_gifting');
        } else {
            $this->_helper = $helper;
        }
    }

    /**
     * Manually setting Magestore Giftvoucher allows
     * customer to choose gift voucher code at checkout.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function orderPlaceAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->getId()) {
            $items = $order->getAllItems();

            foreach ($items as $_item) {
                $product = $_item->getProduct();
                $br = $_item->getBuyRequest();
                if ($product->getTypeId() == 'giftvoucher' and $br->getGiftCode()) {
                    Mage::getModel('bakerloo_gifting/magestoreGiftvoucher')->addGiftVoucherForPosOrderItem($_item, $order);
                }
            }
        }

        return $this;
    }

    /**
     * Switch AheadWorks Giftcard2 purchased code if one was specified
     * at checkout.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function invoicePay(Varien_Event_Observer $observer)
    {
        if (!$this->isPosRequest()) {
            return $this;
        }

        if (Mage::helper('bakerloo_gifting')->getIntegrationFromConfig() != 'AW_Giftcard2') {
            return $this;
        }

        $invoice = $observer->getInvoice();
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            if ($orderItem->getProductType() != AW_Giftcard2_Model_Product_Type_Giftcard::TYPE_CODE) {
                continue;
            }

            $options            = $orderItem->getProductOptions();
            $buyRequest         = $orderItem->getBuyRequest();
            $requestedGiftCodes = $buyRequest->getSelectedGiftCodes();

            if (!$requestedGiftCodes or empty($requestedGiftCodes)) {
                continue;
            }

            $awGc2Options     = $orderItem->getProductOptionByCode(AW_Giftcard2_Model_Product_Type_Giftcard::CUSTOM_OPTIONS_CODE);
            $createdGiftCodes = $awGc2Options[AW_Giftcard2_Model_Product_Type_Giftcard::ORDER_ITEM_CODE_CREATED_CODES];

            foreach ($createdGiftCodes as $i => $code) {
                if (!isset($requestedGiftCodes[$i])) {
                    continue;
                }

                $giftCard = Mage::getModel('aw_giftcard2/giftcard')->loadByCode($code);
                $giftCard->setCode($requestedGiftCodes[$i])
                    ->setIsImported(true)
                    ->save();

                $createdGiftCodes[$i] = $requestedGiftCodes[$i];
            }

            //update item options after saving gift card changes
            $awGc2Options[AW_Giftcard2_Model_Product_Type_Giftcard::ORDER_ITEM_CODE_CREATED_CODES] = $createdGiftCodes;
            $options[AW_Giftcard2_Model_Product_Type_Giftcard::CUSTOM_OPTIONS_CODE]                = $awGc2Options;
            $orderItem->setProductOptions($options)->save();
        }

        return $this;
    }

    /**
     * If a gift card integration is selected, disable
     * output from other gift card extensions.
     *
     * @param Varien_Event_Observer $observer
     */
    public function configChange(Varien_Event_Observer $observer)
    {
        $h = Mage::helper('bakerloo_gifting');

        $selected = $h->getIntegrationFromConfig();
        $supportedTypes = $h->getSupportedTypes();

        foreach ($supportedTypes as $key => $type) {
            if ($type == $selected) {
                Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/' . $type, 0);
            } elseif (Mage::helper('bakerloo_restful')->isModuleInstalled($type)) {
                Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/' . $type, 1);
            }
        }
    }

    /**
     * Workaround for event adminhtml_sales_order_creditmemo_register_before not
     * dispatched from front end.
     * 
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function giftvoucherRegisterCreditmemo(Varien_Event_Observer $observer)
    {
        if (Mage::helper('bakerloo_gifting')->getIntegrationFromConfig() != 'Magestore_Giftvoucher') {
            return $this;
        }

        $giftvoucherObserver = Mage::getModel('giftvoucher/observer');

        if ($giftvoucherObserver) {
            $giftvoucherObserver->creditmemoRegisterBefore($observer);
        }

        return $this;
    }

    /**
     * Add gift card data to quote item
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function quoteItemReturn(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $posItem = $observer->getPosItem();

        $giftcard = $this->_helper->getGiftcard($quoteItem->getProductType());
        if (!is_null($giftcard)) {
            $giftcardOptions = $giftcard->getBuyRequestOptions($quoteItem->getBuyRequest());
            $giftcardOptions['amount'] = (float)$quoteItem->getPrice();
            $posItem->setGiftcardOptions($giftcardOptions);
        }

        return $this;
    }

    public function isPosRequest()
    {
        return Mage::helper('bakerloo_restful')->isPosRequest(Mage::app()->getRequest());
    }
}
