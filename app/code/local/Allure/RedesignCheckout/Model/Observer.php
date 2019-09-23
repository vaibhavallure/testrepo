<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Observer extends Varien_Object
{
    /**
     * Set address item gift to order
     * @param Varien_Object $observer
     */
    public function salesEventConvertQuoteItemToOrderItem($observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $orderItem->setIsGiftItem($quoteItem->getIsGiftItem());
        $orderItem->setGiftItemQty($quoteItem->getGiftItemQty());
    }
    
    /**
     * Set signature delivery method to quote address
     * @param Varien_Object $observer
     */
    public function checkoutEventSaveSignature($observer)
    {
        $signatureDelivery = $observer->getEvent()->getRequest()->getParam('no_signature_delivery');
        $quote = $observer->getEvent()->getQuote();
        if(is_array($signatureDelivery)){
            $addresses = $quote->getAllShippingAddresses();
            foreach ($addresses as $address){
                try{
                    if (isset($signatureDelivery[$address->getId()])) {
                        $address->setNoSignatureDelivery(1);
                    } else {
                        $address->setNoSignatureDelivery(0);
                    }
                }catch (Exception $e){}
            }
        }
    }
    
    public function salesEventConvertQuoteAddressToOrder($observer){
        if($observer->getEvent()->getAddress()->getNoSignatureDelivery()) {
            $observer->getEvent()->getOrder()
            ->setNoSignatureDelivery($observer->getEvent()->getAddress()->getNoSignatureDelivery());
        }
        return $this;
    }
}

