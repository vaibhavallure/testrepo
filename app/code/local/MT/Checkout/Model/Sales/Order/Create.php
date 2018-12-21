<?php

class MT_Checkout_Model_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    /**
     * Parse data retrieved from request
     *
     * @param   array $data
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function importPostData($data)
    {
        if (is_array($data)) {
            $this->addData($data);
        } else {
            return $this;
        }
        
        if (isset($data['account'])) {
            $this->setAccountData($data['account']);
        }
        
        if (isset($data['comment'])) {
            $this->getQuote()->addData($data['comment']);
            if (empty($data['comment']['customer_note_notify'])) {
                $this->getQuote()->setCustomerNoteNotify(false);
            } else {
                $this->getQuote()->setCustomerNoteNotify(true);
            }
        }
        
        if(isset($data["no_signature_delivery"])){
            $signature = $data["no_signature_delivery"];
            $this->getQuote()->setNoSignatureDelivery($signature);
        }
        
        if (isset($data['billing_address'])) {
            $this->setBillingAddress($data['billing_address']);
        }
        
        if (isset($data['shipping_address'])) {
            $this->setShippingAddress($data['shipping_address']);
        }
        
        if (isset($data['shipping_method'])) {
            $this->setShippingMethod($data['shipping_method']);
        }
        
        if (isset($data['payment_method'])) {
            $this->setPaymentMethod($data['payment_method']);
        }
        
        if (isset($data['coupon']['code'])) {
            $this->applyCoupon($data['coupon']['code']);
        }
        return $this;
    }
}
