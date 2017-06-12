<?php
class Allure_CheckoutStep_Model_Sales_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
    /**
     * Invoice states backorder payment is pay as ship
     */
    const STATE_DELAYED       = 0;
    
    /**
     * Check invice capture action availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->getState() != self::STATE_CANCELED
            && $this->getState() != self::STATE_PAID
            && $this->getOrder()->getIsReadyToShip() != self::STATE_DELAYED
            && $this->getOrder()->getPayment()->canCapture();
    }

   }
