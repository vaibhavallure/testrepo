<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Observer extends Varien_Object
{
    /**
     * if cart contains remove gift wrap item.
     */
    public function removeGiftWrapItem(){
        try{
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if($quote->getId()){
                $helper = Mage::helper("allure_redesigncheckout");
                $giftWrapSku = $helper->getGiftWrapSku();
                foreach ($quote->getAllVisibleItems() as $item){
                    if(strtolower($item->getSku()) == strtolower($giftWrapSku)){
                        $quote->removeItem($item->getId());
                        $quote->setTotalsCollectedFlag(false);
                        $quote->collectTotals();
                        $quote->save();
                        break;
                    }
                }
            }
        }catch (Exception $e){
            
        }
    }

    /**
     * set plu to quote item
     */
    public function setPlu($observer){
        try{
            $item = $observer->getEvent()->getDataObject();
                    if($item->getPlu()==null || $item->getPlu()==0){
                        $product=Mage::getModel("catalog/product")->loadByAttribute('sku',$item->getSku());
                        $item->setPlu($product->getTeamworkPlu());

                    }
        }catch (Exception $e){
             Mage::log($e->getMessage(),7,'exception.log',true);
        }
        return $item;
    }
    
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
        $orderItem->setPlu($quoteItem->getPlu());

        //check the quote item is belong to address item
        if($quoteItem instanceof Mage_Sales_Model_Quote_Address_Item){
            $item = $quoteItem->getQuote()->getItemById($quoteItem->getQuoteItemId());
            $orderItem->setStoreId($item->getStoreId());
            $backorderQty = $item->getBackorders();
            $orderItem->setQtyBackordered($backorderQty);
            $orderItem->setBackorderTime($item->getBackorderTime());
            $orderItem->setPlu($item->getPlu());

        }
    }
    
    /**
     * Set signature delivery method to quote address
     * @param Varien_Object $observer
     */
    public function checkoutEventSaveSignature($observer)
    {
        $logStatus = Mage::helper("allure_multicheckout")->getOnePagelogStatus();
        $signatureDelivery = $observer->getEvent()->getRequest()->getParam('no_signature_delivery');
        if($logStatus){
            Mage::log("delivery signature",Zend_Log::DEBUG,'checkout_multishipping.log',true);
            Mage::log($signatureDelivery,Zend_Log::DEBUG,'checkout_multishipping.log',true);
        }
        $quote = $observer->getEvent()->getQuote();
        $addresses = $quote->getAllShippingAddresses();
        foreach ($addresses as $address){
            try{
                $address->setNoSignatureDelivery(0);
                if (isset($signatureDelivery[$address->getId()])) {
                    if($signatureDelivery[$address->getId()]){
                        $address->setNoSignatureDelivery(1);
                    }
                } 
            }catch (Exception $e){}
        }
    }
    
    public function salesEventConvertQuoteAddressToOrder($observer){
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();
        if($address->getNoSignatureDelivery()) {
            $order->setNoSignatureDelivery($address->getNoSignatureDelivery());
        }
        if($address->getIsContainBackorder()){
            $order->setIsContainBackorder($address->getIsContainBackorder());
        }
        return $this;
    }
}

