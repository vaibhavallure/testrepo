<?php

class Teamwork_CEGiftcards_Helper_Invoice extends Mage_Core_Helper_Abstract
{
    public function checkVirtualProductInOrder($order)
    {
        foreach($order->getItemsCollection() as $item)
        {
            $product = $item->getProduct();
            if(
                $product->getTypeId() === Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD &&
                $product->hasGiftcardType() &&
                $product->getGiftcardType() == Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_VIRTUAL
            )
            {
                return true;
            }
        }
        return false;
    }
    
    public function createInvoice($order)
    {
        try
        {
            $order = Mage::getModel('sales/order')->load( $order->getId() );
            if( $order->getPayment() )
            {
                $order->getPayment()->capture(null);
                $order->save();
            }
        }
        catch(Mage_Core_Exception $e)
        {
            Mage::log( $e->getMessage(), null, 'teamwork_transfer.log' );
            Mage::log( $e->getTraceAsString(), null, 'teamwork_transfer.log' );
        }
    }
    
    public function allowAuthorizeOnlyPayment($paymentName, $channelId)
    {
        $paymentMethodInfo = Mage::getModel('teamwork_service/settings')->getPaymentMethodByName($paymentName, $channelId);
        if( !empty($paymentMethodInfo) )
        {
            return $paymentMethodInfo['allow_authorize_only'];
        }
        return false;
    }
    
    public function getChannelIdByStoreId($storeId)
    {
        $store = Mage::getModel('core/store')->load( $storeId );
        $channels = Mage::helper('teamwork_service')->getChannelsList();
        foreach($channels as $channelId => $channelName)
        {
            if( $channelName == $store->getCode() )
            {
                return $channelId;
            }
        }
        return false;
    }
}