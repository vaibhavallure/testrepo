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
     * set plu to order item
     */
    public function setPluOrderLevel($observer){

        try{
            $order = $observer->getOrder();

            $items = $order->getAllItems();
            foreach($items as $i) {
                if($i->getPlu()==null || $i->getPlu()==0){
                    $product=Mage::getModel("catalog/product")->loadByAttribute('sku',$i->getSku());
                    $product=Mage::getModel("catalog/product")->load($product->getId());

                    $i->setPlu($product->getTeamworkPlu());
                }
            }
        }catch (Exception $e){
            Mage::log($e->getMessage(),7,'exception.log',true);
        }
        return $order;
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
        $orderItem->setPlParentItem($quoteItem->getPlParentItem());

        //check the quote item is belong to address item
        if($quoteItem instanceof Mage_Sales_Model_Quote_Address_Item){
            $item = $quoteItem->getQuote()->getItemById($quoteItem->getQuoteItemId());
            $orderItem->setStoreId($item->getStoreId());
            $backorderQty = $item->getBackorders();
            $orderItem->setQtyBackordered($backorderQty);
            $orderItem->setBackorderTime($item->getBackorderTime());
            $orderItem->setPlu($item->getPlu());
            $orderItem->setPlParentItem($item->getPlParentItem());

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



    public function adminhtmlBlockSalesruleActionsPrepareform($observer)
    {

        $fieldset = $observer->getForm()->getElement('action_fieldset');
        $fieldset->addField('custom_error_message', 'text', array(
            'name' => 'custom_error_message',
            'label' => 'Custom Error Message',
            'title' => 'Custom Error Message',
            'note' => 'Custom Error Message',
        ));
    }

}

